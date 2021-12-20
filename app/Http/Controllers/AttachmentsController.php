<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AttachmentsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Neo\Http\Requests\Attachments\StoreAttachmentRequest;
use Neo\Http\Requests\Attachments\UpdateAttachmentRequest;
use Neo\Models\Attachment;
use Neo\Models\Interfaces\WithAttachments;
use Neo\Models\Product;
use Neo\Models\ProductCategory;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class AttachmentsController {
    public function storeProduct(StoreAttachmentRequest $request, Product $product) {
        return $this->store($request, $product);
    }

    public function storeProductCategory(StoreAttachmentRequest $request, ProductCategory $product) {
        return $this->store($request, $product);
    }

    public function store(StoreAttachmentRequest $request, WithAttachments $productLike) {
        /** @var UploadedFile $file */
        $file = $request->file("file");

        // Validate upload
        if (!$file->isValid()) {
            throw new UploadException($file->getErrorMessage(), $file->getError());
        }

        // Store file
        $attachment = $productLike->attachments()->create([
            "name"     => $file->getClientOriginalName(),
            "filename" => $file->getClientOriginalName(),
            "locale"   => $request->input("locale"),
        ]);
        $attachment->store($file);

        return new Response($attachment, 201);
    }

    public function updateProduct(UpdateAttachmentRequest $request, Product $product, Attachment $attachment) {
        return $this->update($request, $product, $attachment);
    }

    public function updateProductCategory(UpdateAttachmentRequest $request, ProductCategory $product, Attachment $attachment) {
        return $this->update($request, $product, $attachment);
    }

    public function update(UpdateAttachmentRequest $request, WithAttachments $productLike, Attachment $attachment) {
        $attachment->name   = $request->input("name");
        $attachment->locale = $request->input("locale");
        $attachment->save();

        return new Response($attachment);
    }

    public function destroyProduct(UpdateAttachmentRequest $request, Product $product, Attachment $attachment) {
        return $this->update($request, $product, $attachment);
    }

    public function destroyProductCategory(UpdateAttachmentRequest $request, ProductCategory $product, Attachment $attachment) {
        return $this->update($request, $product, $attachment);
    }

    public function destroy(UpdateAttachmentRequest $request, WithAttachments $productLike, Attachment $attachment) {
        $attachment->delete();

        return new Response(["status" => "OK"]);
    }


}
