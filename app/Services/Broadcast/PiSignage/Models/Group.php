<?php

namespace Neo\Services\Broadcast\PiSignage\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\PiSignage\API\PiSignageClient;

/**
 * Class Group
 *
 * @package Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string   $_id
 * @property boolean  $deploy                   Deploy state of group to players
 * @property string   $name                     Name of the group
 * @property string   $playlistToSchedule       Playlist name to be used as starting name for adding to scheduling group
 * @property boolean  $combineDefaultPlaylist   Play default playlist first along with scheduled playlist(s)
 * @property boolean  $playAllEligiblePlaylists Play all elgible scheduled playlists (otherwise only first one will be played)
 * @property boolean  $shuffleContent           Randomize playlist rows before deploy
 * @property boolean  $alternateContent         While combining scheduled playlists, combine row by row of each playlist
 * @property string[] $labels
 * @property string   $installation             Username of the user who installed the group
 * @property integer  $lastDeployed             timestamp for the last deploy for the group
 * @property string   $resolution               [ auto, 720p, 1080p, PAL, NTSC ] Display resolution, auto is preferred to take
 *           care of any display
 * @property string   $orientation              [ landscape, portrait, portrait270 ]
 * @property boolean  $animationEnable          enable animation for assets other than videos and webpage links
 * @property string   $animationType
 * @property string   $signageBackgroundColor   Hexadecimal notation #RRGGBB or #RGB or CSS colors
 * @property string   $logo                     png image name from the uploaded assets, null to disable
 * @property integer  $logox                    x-offset for the logo
 * @property integer  $logoy                    y-offset for the logo
 * @property boolean  $urlReloadDisable         false reloads urls each time and true tries to use the cache if available
 * @property boolean  $keepWeblinksInMemory     Keep webpages in memory
 * @property boolean  $loadPlaylistOnCompletion
 * @property integer  $omxVolume                percentage volume of audio
 * @property integer  $timeToStopVideo          stop playing videos after every programmed seconds to check whether any advert is
 *           eligible to play (0 to disable)
 * @property boolean  $resizeAssets             for images - false to show original size, true to resize to fit or letterboxed
 * @property boolean  $imageLetterboxed         for images - show full screen or letterboxed to keep aspect ratio
 * @property boolean  $videoKeepAspect          for videos - true, to show letterboxed, false to show full screen
 * @property string   $deployTime               One time deploy in next 24 hours at specific time (in date object, example shows
 *           3AM with timezone at UTC+0530)
 * @property boolean  $deployEveryday           Deploy everyday at specific time (everydayDeployTime)
 * @property string   $everydayDeployTime       time of the day to deploy (in date object, example shows 3AM with timezone at
 *           UTC+0530)
 * @property boolean  $enableMpv                Use alternate video player mpv instead of omxplayer
 * @property string   $mpvAudioDelay            audio-delay value to be passed to mpv as a flag
 * @property boolean  $disableWebUi             Disable player webUI
 * @property boolean  $disableWarnings          Disable Pi firmware power and temperature warnings (Not recommended)
 * @property boolean  $disableAp                Disable Pi Access Point
 * @property array    $playlists
 * @property array    $deployedPlaylists
 *
 * @method static Collection all(PiSignageClient $client);
 *
 */
class Group extends PiSignageModel {
    protected static array $updatable = [
        "deploy",
        "exportAssets",
        "_id",
        "name",
        "playlistToSchedule",
        "combineDefaultPlaylist",
        "playAllEligiblePlaylists",
        "shuffleContent",
        "alternateContent",
        "assetsValidity",
        "assets",
        "deployedAssets",
        "playlists",
        "deployedPlaylists",
        "ticker",
        "deployedTicker",
        "labels",
        "installation",
        "lastDeployed",
        "resolution",
        "orientation",
        "animationEnable",
        "animationType",
        "signageBackgroundColor",
        "logo",
        "logox",
        "logoy",
        "showClock",
        "urlReloadDisable",
        "keepWeblinksInMemory",
        "loadPlaylistOnCompletion",
        "sleep",
        "omxVolume",
        "timeToStopVideo",
        "resizeAssets",
        "imageLetterboxed",
        "videoKeepAspect",
        "emergencyMessage",
        "reboot",
        "deployTime",
        "deployEveryday",
        "everydayDeployTime",
        "enableMpv",
        "mpvAudioDelay",
        "disableWebUi",
        "disableWarnings",
        "disableAp",
    ];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/groups")->parser(new MultipleResourcesParser(static::class)),
            "create" => Endpoint::post("/groups")->parser(new SingleResourcesParser(static::class)),
            "get"    => Endpoint::get("/groups/{_id}")->parser(new SingleResourcesParser(static::class)),
            "update" => Endpoint::post("/groups/{_id}")->parser(new SingleResourcesParser(static::class)),
            "delete" => Endpoint::post("/groups/{_id}"),
        ];
    }

    public function hasPlaylist(string $playlistName): bool {
        return collect($this->playlists)->contains("name", "=", $playlistName);
    }
}
