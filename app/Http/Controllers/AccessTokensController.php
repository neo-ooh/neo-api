<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\AccessTokens\DestroyAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\ListAccessTokensRequest;
use Neo\Http\Requests\AccessTokens\ShowAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\StoreAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\UpdateAccessTokenRequest;
use Neo\Models\AccessToken;

class AccessTokensController extends Controller
{
    public function index(ListAccessTokensRequest $request): Response {
        return new Response(AccessToken::all());
    }

    public function show(ShowAccessTokenRequest $request, AccessToken $accessToken): Response {
        return new Response($accessToken);
    }

    public function store(StoreAccessTokenRequest $request): Response {
        $inputs = $request->validated();
        $accessToken = new AccessToken();
        $accessToken->name = $inputs["name"];
        $accessToken->save();

        $accessToken->capabilities()->attach($inputs["capabilities"]);

        return new Response($accessToken->refresh(), 201);
    }

    public function update(UpdateAccessTokenRequest $request, AccessToken $accessToken): Response {
        $inputs = $request->validated();
        $accessToken->name = $inputs["name"];
        $accessToken->save();

        $accessToken->capabilities()->sync($inputs["capabilities"]);

        return new Response($accessToken->refresh());
    }

    public function destroy(DestroyAccessTokenRequest $request, AccessToken $accessToken): Response {
        $accessToken->delete();

        return new Response();
    }
}
