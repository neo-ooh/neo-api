<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - EnvironicsCustomerFileProcessor.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports\Processors;

use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Jobs\GeographicReports\GeographicDataReader;
use Neo\Modules\Demographics\Structures\GeographicDataEntry;

class EnvironicsCustomerFileProcessor implements GeographicDataReader {
    public function __construct(protected string $filePath) {
    }

    /**
     * @throws InvalidFileFormatException
     */
    public function getEntries(): iterable {
        // Get a handle to the file
        $fileHandle = fopen($this->filePath, 'rb');

        // Load the headers
        $headers = str_getcsv(fgets($fileHandle), ",", "\"");

        // Get the index of columns of interest
        $weightColumnIndex = array_search("Unique_Visitors", $headers, true);
        $geographyCodeColumnIndex = array_search("CEL_PRCDDA", $headers, true);
        $geographyDescriptionColumnIndex = array_search("CEL_GeoLevel", $headers, true);

        if($weightColumnIndex === false || $geographyCodeColumnIndex === false || $geographyDescriptionColumnIndex === false) {
            throw new InvalidFileFormatException("Invalid Environics Customer File.");
        }

        // Read the file line by line
        while (($line = fgets($fileHandle)) !== false) {
            $values = str_getcsv($line, ",", "\"");

            // Ignore rows marked as `Not Coded`
            if($values[$geographyDescriptionColumnIndex] === "Not Coded") {
                continue;
            }

            $weight = $values[$weightColumnIndex];
            $geographyCode = $values[$geographyCodeColumnIndex];
            
            yield new GeographicDataEntry(
                geography_id: null,
                geography_type_code: 'PRCDDA',
                geography_code: $geographyCode,
                weight: (float)$weight,
                metadata: array_combine($headers, $values)
            );
        }
    }
}
