<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PublicRelations.php
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\Rule;
use Neo\Models\Traits\WithPublicRelations;

class PublicRelations implements Rule {
    /**
     * @param class-string<WithPublicRelations> $model
     */
    public function __construct(protected string $model) {
    }

    public function passes($attribute, $value): bool {
        $requestedRelations = is_string($value) ? [$value] : $value;

        if (!is_array($requestedRelations) && $requestedRelations !== null) {
            return false;
        }

        /** @var WithPublicRelations $model */
        $model                  = new $this->model();
        $allowedPublicRelations = array_keys($model->getPublicRelationsList());

        foreach ($requestedRelations as $requestedRelation) {
            if (!in_array($requestedRelation, $allowedPublicRelations)) {
                return false;
            }
        }

        return true;
    }

    public function message(): string {
        return 'The provided list of public relations is invalid. Only an array of strings is accepted';
    }
}
