<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WithAttachments.php
 */

namespace Neo\Modules\Properties\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface WithAttachments {
    public function attachments(): BelongsToMany;
}
