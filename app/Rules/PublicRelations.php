<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PublicRelations.php
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\Rule;
use Neo\Models\Traits\HasPublicRelations;

class PublicRelations implements Rule {
    protected string|null $error = null;
    protected string|null $badRelation = null;

    /**
     * @param class-string<HasPublicRelations> $model
     */
    public function __construct(protected string $model) {
    }

    public function passes($attribute, $value): bool {
        $requestedRelations = is_string($value) ? [$value] : $value;

        if (!is_array($requestedRelations) && $requestedRelations !== null) {
            $this->error = "invalid-format";
            return false;
        }

        /** @var HasPublicRelations $model */
        $model                  = new $this->model();
        $allowedPublicRelations = array_keys($model->getPublicRelationsList());
        
        foreach ($requestedRelations as $requestedRelation) {
            if (!in_array($requestedRelation, $allowedPublicRelations)) {
                $this->error       = "unknown-relation";
                $this->badRelation = $requestedRelation;
                return false;
            }
        }

        return true;
    }

    public function message(): string {
        if ($this->error === "invalid-format") {
            return 'The provided list of public relations is invalid. Only an array of strings is accepted';
        }

        return "'$this->badRelation' is not a public relation of the model";
    }
}
