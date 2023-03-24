<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdaptProductLine.php
 */

namespace Neo\Console\Commands\Test;

use Spatie\LaravelData\Data;

class AdaptProductLine extends Data {
    public function __construct(
        public string      $id,
        public string      $group,
        public string      $classification,
        public string      $name,
        public string      $address,
        public string      $city,
        public string      $province,
        public string|null $fsa,
        public float       $lat,
        public float       $lng,
        public int         $screens_count,
        public bool        $has_gas,
        public int         $daily_impressions,
        public bool        $commb_audited,
        public bool        $is_digital,
        public int         $spot_length_sec,
        public int         $spots_count,
        public int         $loop_length_sec,
        public string      $media_type,
        public string      $orientation,
        public int         $daily_hours,
    ) {
    }

    public static function fromLine(array $line) {
        return new static(
            id               : $line[0],
            group            : $line[1],
            classification   : $line[2],
            name             : $line[3],
            address          : $line[4],
            city             : $line[5],
            province         : $line[6],
            fsa              : $line[7],
            lat              : (float)$line[8],
            lng              : (float)$line[9],
            screens_count    : (int)$line[10],
            has_gas          : $line[11] === 'YES',
            daily_impressions: (int)$line[12],
            commb_audited    : $line[13] === 'YES',
            is_digital       : $line[14] === 'YES',
            spot_length_sec  : (int)$line[15],
            spots_count      : (int)$line[16],
            loop_length_sec  : (int)$line[17],
            media_type       : $line[18],
            orientation      : $line[19],
            daily_hours      : (int)$line[20],
        );
    }

    public function getParentId(): int {
        return match ($this->classification) {
            "AMP-M"    => 1350,
            "AMP-T"    => 1345,
            "Circle K" => 1347,
            "DTC"      => 1348,
            "INEO"     => 1349,
            "POP-T"    => 1346,
        };
    }

    public function getPricelist(): int {
        return match ($this->classification) {
            "AMP-M"    => 22,
            "AMP-T"    => 23,
            "Circle K" => 24,
            "DTC"      => 25,
            "INEO"     => 26,
            "POP-T"    => 27,
        };
    }

    public function getTags(): array {
        return match ($this->classification) {
            "AMP-M"    => [27, 28],
            "AMP-T"    => [27, 29],
            "Circle K" => [27, 30],
            "DTC"      => [27, 31],
            "INEO"     => [27, 32],
            "POP-T"    => [27, 33],
        };
    }

    public function getDwellTime() {
        return match ($this->classification) {
            "AMP-M"    => 0.75,
            "AMP-T"    => 0.75,
            "Circle K" => 3,
            "DTC"      => 38.25,
            "INEO"     => 3,
            "POP-T"    => 3.5,
        };
    }

    public function getDailyTraffic() {
        return $this->daily_impressions / ($this->getDwellTime() / ($this->loop_length_sec / 60));
    }
}
