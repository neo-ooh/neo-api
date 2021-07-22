<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Capability.php
 */

namespace Neo\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static self actors_edit()
 * @method static self actors_create()
 * @method static self actors_delete()
 * @method static self actors_impersonate()
 * @method static self actors_auth()
 *
 * @method static self roles_edit()
 *
 * @method static self brandings_edit()
 *
 * @method static self libraries_edit()
 * @method static self libraries_create()
 * @method static self libraries_destroy()
 *
 * @method static self campaigns_edit()
 *
 * @method static self contents_edit()
 * @method static self contents_schedule()
 * @method static self contents_review()
 *
 * @method static self locations_edit()
 *
 * @method static self bursts_request()
 * @method static self bursts_quality()
 *
 * @method static self contracts_edit()
 * @method static self contracts_manage()
 *
 * @method static self inventory_read()
 *
 * @method static self networks_edit()
 * @method static self networks_connections()
 * @method static self networks_prints()
 *
 * @method static self chores_broadsign()
 * @method static self tests()
 *
 * @method static self tools_prints()
 */
final class Capability extends Enum {
    // Actors
    public const actors_edit   = "actors.edit";
    public const actors_create = "actors.create";
    public const actors_delete = "actors.delete";

    /**
     * Allows user to update and manage the Authentication and signup token of accessible users.
     */
    public const actors_auth = "actors.auth";

    /**
     * Allows user to browse Connect under the account of an accessible user.
     */
    public const actors_impersonate = "actors.impersonate";

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
    public const contents_dynamic     = "contents.dynamic";
    public const contents_schedule = "contents.schedule";
    public const contents_review   = "contents.review";

    // Formats
    public const formats_edit = "formats.edit";

    // Headlines
    public const headlines_edit = "headlines.edit";

    // Formats
    public const locations_edit = "locations.edit";

    // Terms Of Services
    public const tos_update = "tos.update";

    // Tests
    public const tests = "test.capability";

    // Contracts
    public const contracts_edit = "contracts.edit";
    public const contracts_manage = "contracts.manage";

    public const bursts_request = "bursts.request";
    public const bursts_quality = "bursts.quality";

    // Networks
    public const networks_edit = "networks.edit";
    public const networks_connections = "networks.connections";
    public const networks_prints = "networks.prints";

    // Inventory
    public const inventory_read = "inventory.read";

    // Dymamics
    public const dynamics_weather = "dynamics.weather";
    public const dynamics_news = "dynamics.news";

    // Documents Generation
    public const documents_generation = "documents.generation";

    // Access Tokens
    public const access_token_edit = "access-token.edit";

    // Broadsign administration
    // TODO: Rename this capability to `broadcasters.broadsign`
    public const chores_broadsign = "chores.broadsign";

    // Broadsign administration
    /**
     * Allow user to see information about properties he can access
     */
    public const properties_view = "properties.view";

    /**
     * Allow user to create, edit and remove properties in its accessible hierarchy
     */
    public const properties_edit = "properties.edit";

    /**
     * Allow user to fill in traffic information for accessible properties. Redundant with `properties_edit`
     */
    public const properties_traffic = "properties.traffic";

    /**
     * Allow user to use the Prints tools to export estimated prints by properties
     */
    public const tools_prints = "tools.prints";

    /**
     * Allow user to setup traffic sources
     */
    public const traffic_sources = "traffic.sources";
}
