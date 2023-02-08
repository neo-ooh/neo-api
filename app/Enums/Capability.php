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

    /**
     * Allows access to campaign performances
     */
    case campaigns_performances = "campaigns.performances";

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

    /**
     * Allow assigning tags to contents
     */
    case campaigns_tags = "campaigns.tags";

    // Formats
    case formats_edit = "formats.edit";

    // Headlines
    case headlines_edit = "headlines.edit";

    // Formats
    case locations_edit = "locations.edit";

    // Terms Of Services
    case tos_update = "tos.update";

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
     * Allows the user to view the products of properties
     */
    case properties_products = "properties.products";

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
    case planner_access = "tools.planning";

    /**
     * Allows the user to access other users plans in the planner
     */
    case planner_fullaccess = "planning.fullaccess";

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
     * Allows the user to edit tags
     */
    case tags_edit = "tags.edit";

    /**
     * Allows the user to edit loop configurations
     */
    case loops_edit = "loops.edit";

    /**
     * Allows editing broadcast settings
     */
    case broadcast_settings = "broadcast.settings";

    /**
     * Allows creating and editing broadcast tags
     */
    case broadcast_tags = "broadcast.tags";

    /**
     * Allows access to various tools/info for developers
     */
    case dev_tools = "dev.tools";

    /**
     * Allows user to edit advertisers
     */
    case advertiser_edit = "advertisers.edit";


    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    /**
     * Gives access to basic information on properties
     * * Address
     * * Opening hours
     * * Website
     * * Description
     * * Non-demographic fields
     */
    case properties_view = "properties.view";

    /**
     * Allows creating and archiving properties
     */
    case properties_create = "properties.create";

    /**
     * Allows exporting properties data
     */
    case properties_export = "properties.export";

    /**
     * Allows editing properties address
     */
    case properties_address_edit = "properties.address.edit";

    /**
     * Allows editing properties opening hours
     */
    case properties_opening_hours_edit = "properties.opening-hours.edit";

    /**
     * Allows editing properties basic information
     * * Website
     * * Description
     * * Non-demographic fields
     */
    case properties_infos_edit = "properties.infos.edit";

    /**
     * Gives access to properties pictures
     */
    case properties_pictures_view = "properties.pictures.view";

    /**
     * Allows adding and editing properties pictures
     */
    case properties_pictures_edit = "properties.pictures.edit";

    /**
     * Gives access to properties contacts
     */
    case properties_contacts_view = "properties.contacts.view";

    /**
     * Allows adding and editing properties contacts
     */
    case properties_contacts_edit = "properties.contacts.edit";

    /**
     * Gives access to properties tags
     */
    case properties_tags_view = "properties.tags.view";

    /**
     * Allows assigning tags to properties
     */
    case properties_tags_edit = "properties.tags.edit";

    /**
     * Allows creating new properties tags
     */
    case properties_tags_create = "properties.tags.create";

    /**
     * Gives access to properties demographic values
     */
    case properties_demographics_view = "properties.demographics.view";

    /**
     * Allows uploading demographic values
     */
    case properties_demographics_edit = "properties.demographics.edit";

    /**
     * Gives access to properties traffic
     */
    case properties_traffic_view = "properties.traffic.view";

    /**
     * Allows updating properties traffic values
     */
    case properties_traffic_fill = "properties.traffic.fill";

    /**
     * Allows editing properties traffic settings
     */
    case properties_traffic_manage = "properties.traffic.manage";

    /**
     * Gives access to properties tenants directory
     */
    case properties_tenants_view = "properties.tenants.view";

    /**
     * Allows updating properties tenants
     */
    case properties_tenants_edit = "properties.tenants.edit";

    /**
     * Gives access to properties pricelist
     */
    case properties_pricelist_view = "properties.pricelist.view";

    /**
     * Allows updating properties pricelist
     */
    case properties_pricelist_assign = "properties.pricelist.assign";


    /*
    |----------------------------------------------------------------------
    | Products
    |----------------------------------------------------------------------
    */

    /**
     * Gives access to properties products
     */
    case products_view = "products.view";

    /**
     * Allows updating products attachments
     */
    case products_attachments_edit = "products.attachments.edit";

    /**
     * Gives access to products impressions
     */
    case products_impressions_view = "products.impressions.view";

    /**
     * Allows updating products impressions models
     */
    case products_impressions_edit = "products.impressions.edit";

    /**
     * Gives access to products locations
     */
    case products_locations_view = "products.locations.view";

    /**
     * Allows editing products locations
     */
    case products_locations_edit = "products.locations.edit";


    /*
    |----------------------------------------------------------------------
    | Product Categories
    |----------------------------------------------------------------------
    */

    /**
     * Gives access to product categories
     *
     * Product categories uses the same capabilities for edition as products.
     * e.g. To allow editing impressions models of product categories, use the product-equivalent capability.
     */
    case product_categories_edit = "product-categories.edit";


    /*
    |----------------------------------------------------------------------
    | Pricelists
    |----------------------------------------------------------------------
    */

    /**
     * Allows creating and updating pricelists
     */
    case pricelists_edit = "pricelists.edit";

    /*
    |----------------------------------------------------------------------
    | Brands
    |----------------------------------------------------------------------
    */

    /**
     * Gives access to the brands list
     */
    case brands_view = "brands.view";

    /**
     * Allows adding and editing brands
     */
    case brands_edit = "brands.edit";

    /**
     * Give access to brands POIs list/map
     */
    case brands_poi_view = "brands.poi.view";

    /**
     * Allows editing brands POIs
     */
    case brands_poi_edit = "brands.poi.edit";

    /*
    |----------------------------------------------------------------------
    | Fields
    |----------------------------------------------------------------------
    */

    /**
     * Allows creating, editing and assigning fields
     */
    case fields_edit = "fields.edit";

    /**
     * Allows creating and updating fields using demographic values
     */
    case fields_demographics_edit = "fields.demographics.edit";
}
