# NEO-API

The Neo API handles all the logic related the Neo Connect platform, including communication with third-party services, recurrent and scheduled tasks, as well as some off-loading
tasks for Neo's Odoo instance.

## Requirements

This API uses Laravel. It uses a **MariaDB 10.x** as a database, supplemented by a Redis DB for caching.

All file storage is done on a remote S3 compatible storage server that also act as a CDN for fast content delivery.

The API requires PHP8.2 with the `CURL`, `GD`, `Imagick`, `intl`, `simplexml` and `zlib` extensions enabled.
Additionally, `FFMpeg` and `FFProbe` with support for mp4 is also required.

## Architecture

The API is divided in a core section, and three modules.

The **Core** section is responsible for

- Actors: Users and groups, the hierarchy between them, and supporting information
- Roles & Capabilities: What can users do or not;
- Authentication: The auth mechanism is part of the core of Connect;
- Geographic data: Addresses, markets and geographical census data
- Campaign Planner endpoints: This is legacy, consider moving all planner logic to the `Properties` module

The **Broadcast** module deals with

- Networks: Connection and synchronization with third-party broadcasters
- Formats: Broadcast formats, layouts, loops and tags configuration
- Creatives: Content (Ads) management, including creative upload and libraries
- Campaigns: Content scheduling, targeting and monitoring.

The **Properties** module deals with

- Products: Properties and products themselves, plus all their supporting resources
- Traffic: Traffic storage and calculations
- Demographics: Demographic data storage
- External Inventories: Connection & synchronization with third-party inventory systems
- Contracts: Contract monitoring and management

The **Dynamics** module deals with

- Weather: Bundle management, connection to WeatherSource to get actual weather forecast
- News: Proxy to the Canadian Press to load news articles

Each module holds its models, controllers, resources, etc. As well as its own jobs.
All recurring tasks are registered with the Laravel scheduler, and can be listed with `php artisan schedule:list`
