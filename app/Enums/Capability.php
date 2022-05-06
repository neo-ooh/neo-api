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

final class Capability extends Enum {
    // Actors
    public const actors_edit = "actors.edit";
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
    public const libraries_edit = "libraries.edit";
    public const libraries_create = "libraries.create";
    public const libraries_destroy = "libraries.destroy";

    // campaigns
    public const campaigns_edit = "campaigns.edit";

    // Contents
    public const contents_edit = "contents.edit";
    public const contents_dynamic = "contents.dynamic";
    public const contents_schedule = "contents.schedule";
    public const contents_review = "contents.review";

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
    /**
     * Gives all rights for creating and edition networks and locations.
     */
    public const networks_admin = "networks.admin";


    public const networks_edit = "networks.edit";
    public const networks_connections = "networks.connections";

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

    /**
     * Allow user to see information about properties he can access
     */
    public const properties_view = "properties.view";

    /**
     * Allow user to create, edit and remove properties in its accessible hierarchy
     */
    public const properties_edit = "properties.edit";

    /**
     * Allow user to fill in traffic information for accessible properties.
     */
    public const properties_traffic = "properties.traffic";

    /**
     * Allow user to change markets and cities in them
     */
    public const properties_markets = "properties.markets";

    /**
     * Allows the user to create and edit custom fields for the properties
     */
    public const properties_fields = "properties.fields";

    /**
     * Allows the user to view the products of properties
     */
    public const properties_products = "properties.products";

    /**
     * Allows the user to edit the products of properties
     */
    public const products_impressions = "products.impressions";

    /**
     * Allow user to setup traffic sources
     */
    public const traffic_sources = "traffic.sources";

    /**
     * Allow user to setup and edit odoo properties and products
     */
    public const tools_planning = "tools.planning";

    /**
     * Allow user to setup and edit odoo properties and products
     */
    public const odoo_properties = "odoo.properties";

    /**
     * Allow user to send properties list made using the planning to Odoo
     */
    public const odoo_contracts = "odoo.contracts";

    /**
     * Allow to request an export of impressions, like for specific display unit
     */
    public const impressions_export = "impressions.export";

    /**
     * Allows the user to request an excel file of the property
     */
    public const properties_export = "properties.export";

    /**
     * Allows the user to access the list of tenants of properties
     */
    public const properties_tenants = "properties.tenants";

    /**
     * Allows the user to access other users plans in the planner
     */
    public const planning_fullaccess = "planning.fullaccess";

    /**
     * Allows the user to edit tags
     */
    public const tags_edit = "tags.edit";

    /**
     * Allows the user to edit pricelists
     */
    public const pricelists_edit = "pricelists.edit";

    /**
     * Allows the user to edit loop configurations
     */
    public const loops_edit = "loops.edit";
}
