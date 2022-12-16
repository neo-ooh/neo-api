<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreativesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Neo\Exceptions\InvalidVideoCodec;
use Neo\Exceptions\UnreadableCreativeException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Exceptions\CannotOverwriteCreativeException;
use Neo\Modules\Broadcast\Exceptions\ContentIsNotEditableException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleFrameAndLayout;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeDimensions;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeDuration;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeFrameRate;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeSize;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Broadcast\Http\Requests\Creatives\DestroyCreativeRequest;
use Neo\Modules\Broadcast\Http\Requests\Creatives\StoreCreativeRequest;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Frame;
use Neo\Modules\Broadcast\Models\StructuredColumns\CreativeProperties;
use Neo\Modules\Broadcast\Utils\CreativeValidator;

class CreativesController extends Controller {
    /**
     * @param StoreCreativeRequest $request
     * @param Content              $content
     * @return Response
     * @throws CannotOverwriteCreativeException
     * @throws IncompatibleFrameAndLayout
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeDuration
     * @throws InvalidCreativeFrameRate
     * @throws InvalidCreativeSize
     * @throws InvalidVideoCodec
     * @throws UnreadableCreativeException
     * @throws UnsupportedFileFormatException
     */
    public function store(StoreCreativeRequest $request, COntent $content): Response {
        // Start by checking the given frame matched the content layout
        /** @var Frame $frame */
        $frame = Frame::query()->find($request->get("frame_id"));
        if ($frame->layout->id !== $content->layout->id) {
            throw new IncompatibleFrameAndLayout();
        }

        // Check there is not already a creative for this content's frame
        if ($content->creatives()->where("frame_id", "=", $frame->id)->exists()) {
            throw new CannotOverwriteCreativeException();
        }

        // Prefill the creative
        $creative             = new Creative();
        $creative->type       = CreativeType::from($request->input("type"));
        $creative->owner_id   = Auth::id();
        $creative->content_id = $content->id;
        $creative->frame_id   = $frame->id;

        // Treatment continue in appropriate branch for creative type
        return match ($creative->type) {
            CreativeType::Static => $this->handleStaticCreative($request->file("file"), $creative, $frame, $content),
            CreativeType::Url    => $this->handleDynamicCreative($request->input("name"), $request->input("url"), $request->input("refresh_interval"), $creative, $content),
        };
    }

    /**
     * @param UploadedFile $file
     * @param Creative     $creative
     * @param Frame        $frame
     * @param Content      $content
     * @return Response
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeDuration
     * @throws InvalidCreativeFrameRate
     * @throws InvalidCreativeSize
     * @throws InvalidVideoCodec
     * @throws UnreadableCreativeException
     * @throws UnsupportedFileFormatException
     */
    protected function handleStaticCreative(UploadedFile $file, Creative $creative, Frame $frame, Content $content): Response {
        // Control the uploaded creative
        // This methods returns only if the creative is valid
        $validator = new CreativeValidator($file, $frame, $content);
        $validator->validate();

        // Finalize the creative
        $creative->original_name = $file->getClientOriginalName();
        $creative->duration      = $content->duration;

        // File the creative properties
        $creativeProperties            = new CreativeProperties();
        $creativeProperties->extension = $file->extension();
        $creativeProperties->mime      = $file->getMimeType();
        $creativeProperties->checksum  = hash_file('sha256', $file->path());

        $creative->properties = $creativeProperties;
        $creative->save();
        $creative->refresh();

        // And store the creative
        $creative->storeFile($file);

        // Update content duration
        if ($content->duration < 0.0001) {
            $content->duration = $validator->getCreativeLength();
            $content->save();
        }

        // Properly rename the content if applicable
        if (!$content->name) {
            // This creative is alone in the content, use its name as the name content
            $content->name = str_replace("_", " ", pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $content->save();
        }

        $content->refresh();

        return new Response($creative, 201);
    }

    protected function handleDynamicCreative($name, $url, $refreshInterval, Creative $creative, Content $content): Response {
        // Nothing to check here really, just create the creative
        $creative->original_name = $name;
        $creative->duration      = $content->duration;

        // File its settings
        $creativeProperties                           = new CreativeProperties();
        $creativeProperties->url                      = $url;
        $creativeProperties->refresh_interval_minutes = $refreshInterval;

        $creative->properties = $creativeProperties;

        $creative->save();
        $creative->refresh();

        // Properly rename the ad if applicable
        if ($content->creatives_count === 1) {
            // This creative is the first, use its name
            $content->name = $name;
            $content->save();
        }

        return new Response($creative, 201);
    }

    /**
     * @param DestroyCreativeRequest $request
     * @param Content                $content
     * @param Creative               $creative
     *
     * @return Response
     * @throws ContentIsNotEditableException
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyCreativeRequest $request, Content $content, Creative $creative): Response {
        // A creative can not be deleted is the content is locked
        if (!$creative->content->is_editable) {
            throw new ContentIsNotEditableException();
        }

        // If the creative's content has never been scheduled before, we skip the force-delete step
        if ($creative->content->schedules_count === 0) {
            $creative->forceDelete();
        } else {
            $creative->delete();
        }

        if ($content->creatives()->count() === 0) {
            $content->duration = 0;
            $content->save();
        }

        return new Response($creative);
    }
}
