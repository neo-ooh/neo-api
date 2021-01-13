<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Capability.php
 */

namespace Neo\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static self actors_edit()
 * @method static self actors_create()
 * @method static self actors_delete()
 * @method static self roles_edit()
 * @method static self brandings_edit()
 * @method static self libraries_edit()
 * @method static self libraries_create()
 * @method static self libraries_destroy()
 * @method static self campaigns_edit()
 * @method static self contents_edit()
 * @method static self contents_schedule()
 * @method static self contents_review()
 * @method static self locations_edit()
 * @method static self bursts_request()
 * @method static self reports_create()
 * @method static self reports_edit()
 * @method static self tests()
 */
final class Capability extends Enum {
    // Users
    public const actors_edit   = "actors.edit";
    public const actors_create = "actors.create";
    public const actors_delete = "actors.delete";

    // Roles
    public const roles_edit = "roles.edit";

    // Brandings
    public const brandings_edit = "brandings.edit";

    // Libraries
    public const libraries_edit    = "libraries.edit";
    public const libraries_create  = "libraries.create";
    public const libraries_destroy = "libraries.destroy";

    // campaigns
    public const campaigns_edit = "campaigns.edit";

    // Contents
    public const contents_edit     = "contents.edit";
    public const contents_schedule = "contents.schedule";
    public const contents_review   = "contents.review";

    // Formats
    public const formats_edit = "formats.edit";

    // Formats
    public const locations_edit = "locations.edit";

    // Terms Of Services
    public const tos_update = "tos.update";

    // Tests
    public const tests = "test.capability";

    // Customers
    public const customers_edit = "customers.edit";

    // Reports & bursts
    public const reports_create = "reports.create";
    public const reports_edit = "reports.edit";
    public const bursts_request = "bursts.request";
}
