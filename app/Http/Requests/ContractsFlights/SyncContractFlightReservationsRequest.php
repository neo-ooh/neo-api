<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncContractFlightReservationsRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Illuminate\Foundation\Http\FormRequest;

class SyncContractFlightReservationsRequest extends FormRequest {
    public function rules(): array {
        return [
            "reservations"   => ["nullable", "array"],
            "reservations.*" => ["integer", "exists:contracts_reservations,id"]
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
