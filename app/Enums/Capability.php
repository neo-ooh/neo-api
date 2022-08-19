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

enum Capability: string {
    // Actors
    case actors_edit = "actors.edit";
    case actors_create = "actors.create";
    case actors_delete = "actors.delete";

    /**
     * Allows user to update and manage the Authentication and signup token of accessible users.
     */
    case actors_auth = "actors.auth";

    /**
     * Allows user to browse Connect under the account of an accessible user.
     */
    case actors_impersonate = "actors.impersonate";

    // Roles
    case roles_edit = "roles.edit";

    // Brandings
    case brandings_edit = "brandings.edit";

    // Libraries
    case libraries_edit = "libraries.edit";
    case libraries_create = "libraries.create";
    case libraries_destroy = "libraries.destroy";

    // campaigns
    case campaigns_edit = "campaigns.edit";

    // Contents
    case contents_edit = "contents.edit";
    case contents_dynamic = "contents.dynamic";
    case contents_schedule = "contents.schedule";
    case contents_review = "contents.review";

    /**
     * Allow assigning tags to contents
     */
    case contents_tags = "contents.tags";

    /**
     * Allow assigning tags to contents
     */
    case schedules_tags = "schedules.tags";

    // Formats
    case formats_edit = "formats.edit";

    // Headlines
    case headlines_edit = "headlines.edit";

    // Formats
    case locations_edit = "locations.edit";

    // Terms Of Services
    case tos_update = "tos.update";

    // Tests
    case tests = "test.capability";

    // Contracts
    case contracts_edit = "contracts.edit";
    case contracts_manage = "contracts.manage";

    case bursts_request = "bursts.request";
    case bursts_quality = "bursts.quality";

    // Networks
    /**
     * Allows to create and edit broadcasters connections
     */
    case networks_edit = "networks.edit";

    /**
     * Allows to create and edit broadcasters connections
     */
    case networks_connections = "networks.connections";

    /**
     * Allows to force refresh networks
     */
    case networks_refresh = "networks.refresh";

    // Dymamics
    case dynamics_weather = "dynamics.weather";
    case dynamics_news = "dynamics.news";

    // Documents Generation
    case documents_generation = "documents.generation";

    // Access Tokens
    case access_token_edit = "access-token.edit";

    // Broadsign administration
    // TODO: Rename this capability to `broadcasters.broadsign`
    case chores_broadsign = "chores.broadsign";

    /**
     * Allow user to see information about properties he can access
     */
    case properties_view = "properties.view";

    /**
     * Allow user to create, edit and remove properties in its accessible hierarchy
     */
    case properties_edit = "properties.edit";

    /**
     * Allow user to fill in traffic information for accessible properties.
     */
    case properties_traffic = "properties.traffic";

    /**
     * Allow user to change markets and cities in them
     */
    case properties_markets = "properties.markets";

    /**
     * Allows the user to create and edit custom fields for the properties
     */
    case properties_fields = "properties.fields";

    /**
     * Allows the user to view the products of properties
     */
    case properties_products = "properties.products";

    /**
     * Allows the user to edit the products of properties
     */
    case products_impressions = "products.impressions";

    /**
     * Allow user to setup traffic sources
     */
    case traffic_sources = "traffic.sources";

    /**
     * Allow user to refresh the current day's traffic snapshot
     */
    case traffic_refresh = "traffic.refresh";

    /**
     * Allow user to setup and edit odoo properties and products
     */
    case tools_planning = "tools.planning";

    /**
     * Allow user to setup and edit odoo properties and products
     */
    case odoo_properties = "odoo.properties";

    /**
     * Allow user to send properties list made using the planning to Odoo
     */
    case odoo_contracts = "odoo.contracts";

    /**
     * Allow to request an export of impressions, like for specific display unit
     */
    case impressions_export = "impressions.export";

    /**
     * Allows the user to request an excel file of the property
     */
    case properties_export = "properties.export";

    /**
     * Allows the user to access the list of tenants of properties
     */
    case properties_tenants = "properties.tenants";

    /**
     * Allows the user to access other users plans in the planner
     */
    case planning_fullaccess = "planning.fullaccess";

    /**
     * Allows the user to edit tags
     */
    case tags_edit = "tags.edit";

    /**
     * Allows the user to edit pricelists
     */
    case pricelists_edit = "pricelists.edit";

    /**
     * Allows the user to edit loop configurations
     */
    case loops_edit = "loops.edit";

    /**
     * Allows the user to access properties contacts
     */
    case properties_contacts = "properties.contacts";

    /**
     * Allows creating and editing broadcast tags
     */
    case broadcast_tags = "broadcast.tags";
}
