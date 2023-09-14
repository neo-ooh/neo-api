/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - table_foreign_keys.sql
 */

-- https://stackoverflow.com/a/201678
-- For a Table
SELECT `TABLE_NAME`,
       `COLUMN_NAME`,
       `CONSTRAINT_NAME`,
       `REFERENCED_TABLE_NAME`,
       `REFERENCED_COLUMN_NAME`
  FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
 WHERE `REFERENCED_TABLE_SCHEMA` = 'neo_ooh_dev'
   AND `REFERENCED_TABLE_NAME` = 'demographic_values';

-- For a column
SELECT `TABLE_NAME`,
       `COLUMN_NAME`,
       `CONSTRAINT_NAME`,
       `REFERENCED_TABLE_NAME`,
       `REFERENCED_COLUMN_NAME`
  FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
 WHERE `REFERENCED_TABLE_SCHEMA` = '<database>'
   AND `REFERENCED_TABLE_NAME` = '<table>'
   AND `REFERENCED_COLUMN_NAME` = '<column>';
