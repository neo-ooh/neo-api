-- Copyright 2020 (c) Neo-OOH - All Rights Reserved
-- Unauthorized copying of this file, via any medium is strictly prohibited
-- Proprietary and confidential
-- Written by Valentin Dufois <vdufois@neo-ooh.com> & Andrew Wise <awise@neo-ooh.com>
--
-- Lsyncd configuration file for Neo-OOH Servers replication
-- Original file and location: neo-back-01:/etc/lsyncd/lsyncd.conf.lua 

settings {
logfile = "/var/log/lsyncd/lsyncd.log",
statusFile = "/var/log/lsyncd/lsyncd.status"
}

-- Dynamics API -----
-- prod
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/runcloud/webapps/dynamics-api/", 
	host="10.137.160.140", 
	targetdir="/home/runcloud/webapps/dynamics-api/",
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}

-- dev
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/runcloud/webapps/dynamics-api_dev/", 
	host="10.137.160.140", 
	targetdir="/home/runcloud/webapps/dynamics-api_dev/",
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}

-- Weather Dynamic -----
-- prod
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/runcloud/webapps/weather-dynamic/", 
	host="10.137.160.140", 
	targetdir="/home/runcloud/webapps/weather-dynamic/",
	-- Exclude nodejs modules and git folder from replication
	exclude = { "node_modules", ".git" },
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}

-- News Dynamic -----
-- prod
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/runcloud/webapps/news-dynamic/", 
	host="10.137.160.140", 
	targetdir="/home/runcloud/webapps/news-dynamic/",
	-- Exclude nodejs modules and git folder from replication
	exclude = { "node_modules", ".git" },
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}


-- OOH API -----
-- prod
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/ooh-apis/webapps/ooh-api/", 
	host="10.137.160.140", 
	targetdir="/home/ooh-apis/webapps/ooh-api/",
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}

-- dev
sync{
	default.rsyncssh, 
	delete = true, 
	source="/home/ooh-apis/webapps/dev_ooh-api/", 
	host="10.137.160.140", 
	targetdir="/home/ooh-apis/webapps/dev_ooh-api/",
	-- Preserve files permissions and ownership
	rsync = {
		perms = true,
		owner = true,
		group = true
	}
}
