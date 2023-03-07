<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Neo\Enums\ActorType;
use Neo\Enums\Capability;
use Neo\Http\Requests\Actors\DestroyActorsRequest;
use Neo\Http\Requests\Actors\ImpersonateActorRequest;
use Neo\Http\Requests\Actors\ListActorsByIdRequest;
use Neo\Http\Requests\Actors\ListActorsRequest;
use Neo\Http\Requests\Actors\RequestActorTokenRequest;
use Neo\Http\Requests\Actors\ShowActorSecurityStatusRequest;
use Neo\Http\Requests\Actors\StoreActorRequest;
use Neo\Http\Requests\Actors\UpdateActorRequest;
use Neo\Jobs\CreateActorLibrary;
use Neo\Jobs\CreateSignupToken;
use Neo\Mails\ActorWelcomeEmail;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Library;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Class ActorsController
 *
 * @package Neo\Http\Controllers
 */
class ActorsController extends Controller {
    public function index(ListActorsRequest $request): Response {
        $params = $request->validated();

        /** @var Collection $actors */
        $actors = Auth::user()?->getAccessibleActors() ?? new Collection();

        if ($request->input("withself", false)) {
            $actors = $actors->push(Auth::user());
        }

        $actors = $actors->unique("id");
        $actors->append("type");

        if ($request->has("types")) {
            $requestedTypes = collect($request->input("types", []))->map(fn($t) => ActorType::from($t));
            $actors         = $actors->filter(fn(Actor $actor) => $requestedTypes->contains($actor->getTypeAttribute()));
        }

        if ($request->has("capabilities")) {
            $actors->load(["capabilities"]);

            $actors = $actors->filter(fn(Actor $actor) => array_any($request->input("capabilities"), static fn(string $capSlug) => $actor->hasCapability(Capability::from($capSlug))
            )
            );

            $actors->makeHidden(["capabilities"]);
        }


        // Legacy filters; Deprecated

        // We return all actors by default. If a groups query parameter is specified, let it decide
        if ($request->has("groups")) {
            $actors = $actors->where("is_group", "=", (bool)$request->query('groups'));
        }

        // Exclude specific actors
        if ($request->has("exclude")) {
            $actors = $actors->whereNotIn("id", $params["exclude"]);
        }

        if ($request->has("property")) {
            $actors->load(Gate::allows(Capability::odoo_properties->value) ? "property.odoo" : "property");
        }

        return new Response($actors->loadPublicRelations()->sortBy("name")->values());
    }

    public function byId(ListActorsByIdRequest $request) {
        return new Response(Actor::query()
                                 ->whereIn("id", $request->input("ids"))
                                 ->orderBy("name")
                                 ->get()
                                 ->values()->all());
    }

    public function show(Request $request, Actor $actor): Response {
        $with = $request->get("with", []);

        if ($actor->is_locked) {
            $actor->load("lockedBy");
        }

        if (in_array("direct_children", $with, true)) {
            $actor->append("direct_children");
        }

        if (in_array("roles", $with, true)) {
            $actor->load("roles");
        }

        if (in_array("roles_capabilities", $with, true)) {
            $actor->load("roles_capabilities");
        }

        if (in_array("standalone_capabilities", $with, true)) {
            $actor->load("standalone_capabilities");
        }

        if (in_array("capabilities", $with, true)) {
            $actor->load(["capabilities"]);
        }

        if (!is_null($actor->branding_id) && in_array("branding", $with, true)) {
            $actor->load("branding");
        }

        if (in_array("applied_branding", $with, true)) {
            $actor->append("applied_branding");
        }

        if (in_array("own_locations", $with, true)) {
            $actor->load("own_locations");
        }

        if (in_array("locations", $with, true)) {
            $actor->append("locations");
        }

        if (in_array("formats", $with, true)) {
            $actor->setRelation("formats", $actor->getLocations()->pluck("format")->unique("id")->values());
        }

        if (in_array("sharers", $with, true)) {
            $actor->load("sharers");
        }

        if (in_array("logo", $with, true)) {
            $actor->load("logo");
        }

        if (in_array("phone", $with, true) || Auth::user()?->is($actor)) {
            $actor->load("phone");
        }

        $actor->append(["parent", "registration_sent", "is_registered"]);

        return new Response($actor);
    }

    public function store(StoreActorRequest $request): Response {
        // Authorization is handled by the request
        $values = $request->validated();

        // Create the actor's "shell"
        $actor           = new Actor();
        $actor->name     = $values['name'];
        $actor->locale   = $values['locale'];
        $actor->is_group = $values['is_group'];
        $actor->save();

        // Place it in the tree
        /** @var Actor $parent */
        $parent = Actor::query()->find($values["parent_id"]);
        $actor->moveTo($parent);

        // If its a physical user, add more informations
        if (!$actor->is_group) {
            $actor->email       = $values['email'];
            $actor->branding_id = $values['branding_id'];
            $actor->roles()->attach($values['roles']);
            $actor->standalone_capabilities()->attach($values['capabilities']);
            $actor->save();

            // Execute the user's creation side effects
            if ($values["enabled"] === true) {
                CreateSignupToken::dispatch($actor->id);
            } else {
                $actor->is_locked = true;
                $actor->locked_by = Auth::id();
                $actor->save();
            }
        }

        // Should we create a library for the user ?
        if ($actor->is_group && $request->input("make_library", false)) {
            CreateActorLibrary::dispatch($actor->id);
        }

        return new Response($actor, 201);
    }

    public function update(UpdateActorRequest $request, Actor $actor): Response {
        // Since all request properties are optional, make sure at least one was given
        if (count($request->all()) === 0) {
            return new Response([
                                    "code" => "empty-request",
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               "message" => "You must pass at lease 1 parameter when calling this route",
                                ], 422);
        }

        // The request handles input validation
        $actor->name           = $request->get("name", $actor->name);
        $actor->email          = $request->get("email", $actor->email);
        $actor->locale         = $request->get("locale", $actor->locale);
        $actor->branding_id    = $request->get("branding_id", $actor->branding_id);
        $actor->limited_access = $request->get("limited_access", $actor->limited_access);
        $actor->two_fa_method  = $request->get("two_fa_method", $actor->two_fa_method);

        $lock = $request->get("is_locked", $actor->is_locked);

        if ($request->has("password")) {
            $actor->password = $request->get("password");
        }

        if ($lock !== $actor->is_locked) {
            $actor->is_locked = $lock;
            $actor->locked_by = $lock ? Auth::id() : null;
        }

        $actor->save();

        // If a parent_id is present and different from the current actor's parent, try to move the actor
        if (($parentID = $request->get("parent_id", null)) && $parentID !== $actor->parent_id) {
            /** @var Actor $parent */
            $parent = Actor::query()->findOrFail($parentID);

            // Make sure we are not creating a hierarchical loop.
            // A user cannot have one of its child or itself has its parent
            if ($parent->id === $actor->id || $actor->isParentOf($parent)) {
                return new Response([
                                        'code' => 'actor.hierarchy-loop',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   'message' => 'Parent assignment would result in incoherent actors hierarchy',
                                        'data' => $actor,
                                    ], 403);
            }

            $actor->moveTo($parent);
        }

        $actor->unsetRelations();
        $actor->refresh();

        return new Response($this->show($request, $actor)->original);
    }

    public function destroy(DestroyActorsRequest $request, Actor $actor): Response {
        // Move or delete this actor's resources according to specified behaviour
        switch ($request["behaviour"]) {
            case "to-parent":
                $actor->children->each(fn($actor) => $actor->moveTo($actor->parent));
                $actor->libraries->each(function (Library $library) use ($actor) {
                    $library->owner_id = $actor->parent_id;
                    $library->save();
                });
                $actor->campaigns->each(function (Campaign $campaign) use ($actor) {
                    $campaign->parent_id = $actor->parent_id;
                    $campaign->save();
                });
                break;
            case "to-self":
                $actor->children->each(fn($actor) => $actor->moveTo(Auth::user()));
                $actor->libraries->each(function (Library $library) use ($actor) {
                    $library->owner_id = Auth::id();
                    $library->save();
                });
                $actor->campaigns->each(function (Campaign $campaign) use ($actor) {
                    $campaign->parent_id = Auth::id();
                    $campaign->save();
                });
                break;
            case "to-trash":
                $actor->libraries->each(fn(Library $library) => $library->delete());
                $actor->campaigns->each(fn(Campaign $campaign) => $campaign->delete());
                break;
        }

        $actor->phone?->delete();

        $actor->delete();

        return new Response($actor);
    }

    /**
     * Generates a new welcome token and email for the given actor
     *
     * @param Actor $actor
     * @return Response
     */
    public function resendWelcomeEmail(Actor $actor): Response {
        // If the user has no token, run the default job
        try {
            if (!$actor->signupToken) {
                CreateSignupToken::dispatchSync($actor->id);
                $actor->refresh();
            } else {
                // Otherwise, simply resend the email
                Mail::to($actor)->send(new ActorWelcomeEmail($actor->signupToken));
            }
        } catch (TransportException) {
            // Email could not be delivered because recipient does not exist.
            // Not my problem
        }

        return new Response($actor->signupToken);
    }

    /**
     * Gives a token for the current user
     *
     * @param RequestActorTokenRequest $request
     * @return Response
     */
    public function getToken(RequestActorTokenRequest $request): Response {
        return new Response(["token" => Auth::user()?->getJWT(updateIfNecessary: false)]);
    }

    public function impersonate(ImpersonateActorRequest $request, Actor $actor) {
        // Validate the actor is not a group
        if ($actor->is_group) {
            throw new InvalidArgumentException("Cannot impersonate a group");
        }

        // Get and return the impersonating token
        return new Response(["token" => $actor->getJWT(isImpersonating: true)]);
    }

    public function security(ShowActorSecurityStatusRequest $request, Actor $actor): Response {
        $twoFAToken = $actor->twoFactorToken;
        $twoFAToken?->makeVisible("token");

        $signupToken = $actor->signupToken;
        $signupToken?->makeVisible("token");

        return new Response([
                                "signup_token"     => $signupToken,
                                "two_factor_token" => $twoFAToken,
                            ]);
    }
}
