<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\BroadSign\Models;

use Neo\BroadSign\Endpoint;

/**
 * Class Creatives
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    approval_status
 * @property int    approved_by_user_id
 * @property string approved_on_utc
 * @property int    archive_priority
 * @property int    archive_status
 * @property int    archived_by
 * @property string archived_on_utc
 * @property string attributes
 * @property int    bmb_host_id
 * @property string checksum2
 * @property int    checksum2_type
 * @property int    container_id
 * @property string creation_tm
 * @property int    creation_user_id
 * @property int    domain_id
 * @property string external_id
 * @property string feeds
 * @property int    id
 * @property string mime
 * @property string name
 * @property string originalfilename
 * @property int    parent_id
 * @property int    size
 *
 * @method static Collection<Creative> all()
 * @method static Creative get(int $creativeID)
 * @method static int update(array $attributes)
 */
class Creative extends BroadSignModel {

    protected static string $unwrapKey = "content";

    protected static array $updatable = [
        "active",
        "approval_status",
        "archive_priority",
        "attributes",
        "bmb_host_id",
        "container_id",
        "domain_id",
        "external_id",
        "feeds",
        "id",
        "name",
        "parent_id",
    ];

    protected static function actions(): array {
        return [
            "all"                 => Endpoint::get("/content/v11")->multiple(),
            "create"              => Endpoint::post("/content/v11/add")->multipart()->id(),
            "import_from_url"     => Endpoint::post("/content/v11/import_from_url")->id(),
            "get"                 => Endpoint::get("/content/v11/{id}"),
            "update"              => Endpoint::put("/content/v11"),
            "addResourceCriteria" => Endpoint::post("/resource_criteria/v7/add")->ignore(),
        ];
    }

    public function import() {
        return static::import_from_url([
            "attributes" => $this->attributes,
            "name" => $this->name,
            "parent_id" => $this->parent_id,
            "url" => $this->url,
        ]);
    }

    /**
     * @param int $criteriaID
     * @param int $type
     *
     */
    public function addCriteria(int $criteriaID, int $type): void {
        static::addResourceCriteria([
            "active"      => true,
            "criteria_id" => $criteriaID,
            "parent_id"   => $this->id,
            "type"        => $type,
        ]);
    }

    public function approve(): void {
        $this->active           = 1;
        $this->approval_status  = 1;
        $this->archive_priority = 1;
        $this->external_id      = "";
        $this->save();
    }
}
