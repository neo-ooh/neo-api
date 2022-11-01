<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterConnection.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Neo\Modules\Broadcast\Models\StructuredColumns\BroadcasterSettings;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class BroadcasterConnections
 *
 * Represent a connection to an external Digital Signage service
 *
 * @package Neo\Models
 *
 * @property int                     $id
 * @property string                  $uuid
 * @property BroadcasterType         $broadcaster
 * @property string                  $name
 * @property bool                    $active
 * @property bool                    $contracts True if the connection should be used for contracts
 * @property Date                    $created_at
 * @property Date                    $updated_at
 * @property Date                    $deleted_at
 *
 * @property BroadcasterSettings     $settings
 * @property Collection<DisplayType> $display_types
 *
 */
class BroadcasterConnection extends Model {
    use SoftDeletes;

    protected $table = "broadcasters_connections";

    protected $casts = [
        "broadcaster" => BroadcasterType::class,
        "active"      => "bool",
        "contracts"   => "bool",
        "settings"    => BroadcasterSettings::class,
    ];

    /**
     * @return HasMany<Network>
     */
    public function networks(): HasMany {
        return $this->hasMany(Network::class, "connection_id")->orderBy("name");
    }

    /**
     * @return HasMany<DisplayType>
     */
    public function displayTypes(): HasMany {
        return $this->hasMany(DisplayType::class, "connection_id")->orderBy("name");
    }

    public function storeCertificate(File $file): void {
        // !! IMPORTANT !! Visibility has to be set to private, this key has no password
        // The key is stored on the shared storage to be accessible by all the API nodes
        Storage::disk("public")
               ->putFileAs("secure/certs/", $file, "$this->uuid.pem", ["visibility" => "private"]);
    }
}
