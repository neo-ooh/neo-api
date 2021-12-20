<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WithImpressionsModels.php
 */

namespace Neo\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface WithAttachments {
    public function attachments(): BelongsToMany;
}
