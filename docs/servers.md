# Preamble

This article is here to describe the current state of the deployment environment of the Neo-Connect system. It includes description on how the different systems are set up.

The goal here is not to provide a tutorial to deploy the platform, but to explain how everything is currently working.

# Source code repositories

The Connect platform is comprised of two private code repositories hosted on Github under the Neo-OOH organisation:

* [neo-ooh/neo-api ](https://github.com/neo-ooh/neo-api)For the back-end code (API)
* [neo-ooh/neo-connect](https://github.com/neo-ooh/neo-connect) For the front-end code

Some files such as `.env` files and deployment scripts are not part of the repositories for security reasons.

> For the api, the .env file can be found at `/home/ooh-apis/webapps/ooh-api_prod.env` in the `neo-back-01` server.<br />
> For the ui, the .env file can be found at `/home/ooh-connect/webapps/neo-connect_prod.env` in the `neo-front` server.

> Deployment scripts are registered inside of Runcloud and can be found there.

# Server Setup

Currently, Connect uses four different servers to balance loads and separate concerns.
These servers are DigitalOcean Droplets VPS and managed through RunCloud. Each server has a specific purpose and has different setups.

### neo-front

This server holds the front-end interface. It's this server that is hit when user request the Connect UI.

This server requires Node.JS for building the front-end interface.

### neo-db

This server sole purpose is to store the database. The decision to have the database on a single server is to make sure it stays available even if another server goes down.
Databases, users and accesses are all handled through RunCloud. A phpMyAdmin instance is available at <https://db.neo-ooh.info/>.

This server also hosts the Redis server on port 6379 (Redis default) used for caching by the API. At the time of writing, only the traffic from the `neo-back-01` et `neo-back-02`
servers is allowed to reach this port.

### neo-back-01

This server holds an instance of the API. Multiple things needs to be setup for this server:

1. [Composer](https://getcomposer.org/) (PHP Package manager) needs to be installed and available. I don't know if RunCloud does it or not;
2. [FFmpeg](https://ffmpeg.org/) (Media analysis and transcoding) has to be installed and working with MP4 files;
3. [Lsyncd](https://axkibe.github.io/lsyncd/) (Live Syncing Daemon) service has to be installed and running. Configuration file for Lsyncd is attached to this
   article: [lsyncd.conf.lua](lsyncd.conf.lua);

### neo-back-02

This server holds a copy of the API instance on `neo-back-01`. Requirements for the server are the same as `neo-back-01`.

No specific deployment setup, except for setting up the appropriate web-apps on RunCloud, is required. All files are automatically copied to this server by Lsyncd.

Additionaly, a DigitalOcean Load Balancer is used to balance requests loads on the `neo-back-1` and `neo-back-2` servers, and a DigitalOcean Space is used a storage location for
Connect related files, and is access through the `cdn.neo-ooh.info` domain. Access configuration is done in the .env file of the main instance on `neo-back-01`

# Database

Connect requires only one database to run, and just one database user with read and write access.

Update to the database tables are done through migration files in the OOH-API. The API should be able, upon first setup, to correctly setup the database and fill it with the
necessary records. Information to connect to the database from the API are stored in the `.env` files of the API.

UI for the DB (phpmyadmin) is available at `https://db.neo-ooh.info/`

# API

The API is the most crucial part of the Connect system and is the one interacting with the most systems.

The API is a [Laravel](https://laravel.com/) application built in a Composer project. The API is setup under the `ooh-api` web app in `neo-back-01` in RunCloud.

### Settings

The settings of the App are as follow and are the same for the `ooh-api` web app on `neo-back-02`.

| Setting           | Value                                                                                            |
|-------------------|--------------------------------------------------------------------------------------------------|
| PHP Version       | 8.1                                                                                              |
| disable_functions | `exec`, plus all functions related to `fopen`, `fclose`, etc. needs to be removed from this list |

### Cloning & Deployment

The `ooh-api` webapp is linked to the Github repo using RunClound Git functionnality. The deployement key for the unix user provided by RunCloud is installed in Github to allow the
communication, and the webhook provided by RunCloud for the webapp in the Git section is setup in the `ooh-api` repository in Github. This allows for automatically triggering a
deployement upon each push to the `main` branch of the Github repo.

Even though cloning is automatic, some actions still needs to be done upon each update to properly apply updates. These are specified through a deployment script setup in RunCloud
for the web app.

# Front End

The front end has a similar setup as the API, albeit without any replication involved.

### Settings

| Setting     | Value                                      |
|-------------|--------------------------------------------|
| Public path | /home/ooh-connect/webapps/neo-connect/live |

### Cloning & Deployment

The web app link with the Github repo follows the same schema as the for the API.

Even though cloning is automatic, some actions still needs to be done upon each update to build the webapp. These are specified through a deployment script setup in RunCloud for
the web app.
