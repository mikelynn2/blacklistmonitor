# BlacklistMonitor
```
Copyright (c) by respective owners. All rights reserved.  Released under license as described in the file LICENSE.txt
```
Application for monitoring Domains and IPs on RBLs.

[![Build Status](https://scrutinizer-ci.com/g/mikerlynn/blacklistmonitor/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mikerlynn/blacklistmonitor/build-status/master)

## Features
- Supports All Major Blacklists.  You can customize this list
- Monitor blocks of IPs in CIDR Format and your domains
- Web based reporting
- Email, SMS, and Twitter Alerts

## Prerequisite software
- [MySQL](http://www.MySQL.org) or [MariaDB](https://mariadb.org/) are needed for the database.
- Most likely you'll need your own DNS server as well.  You can use [Bind](https://www.isc.org/downloads/bind/) or even [unbound](https://www.unbound.net/).  Bind is easier, unbound may be faster.
- Apache or Nginx
- SMTP Mail server like postfix

## Installation (Ubuntu/mariadb/bind/apache)
```
apt-get -y install apache2
apt-get -y install mariadb-server mariadb-client mariadb-common
apt-get -y install php5 php5-mysqlnd php5-cli php5-curl
apt-get -y install bind9
update-rc.d bind9 defaults
update-rc.d apache2 defaults
update-rc.d mysql defaults
```

Go into the directory you want to install BlacklistMonitor into and clone the git repo.  Usually this would be a web server directory like /var/www/html/.  The rest of the commands below assume you're using this dir and the default config files do as well.

```
cd /var/www/html/
git clone git://github.com/mikerlynn/blacklistmonitor.git
```

## Initialize Data
```
mysql -p < /var/www/html/blacklistmonitor/setup/blacklistmonitor.sql
```

## Setup Apache
```
cp /var/www/html/blacklistmonitor/setup/blacklistmonitor-apache.conf /etc/apache2/sites-enabled/
```

## Copy Default Config
```
cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.cfg /etc/
```

After you've copied the config file you need to edit it to customize it for your setup here: /etc/blacklistmonitor.cfg


## Schedule Cron
```
cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.cron /etc/cron.d/blacklistmonitor
```

## Service
```
cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.conf /etc/init/
```

### start/stop/restart
```
start blacklistmonitor
stop blacklistmonitor
restart blacklistmonitor
```

### Website
The default username and password to the portal is admin/pa55w0rd

### Timezone Setup
```
dpkg-reconfigure tzdata
```
Then edit your the value for date.timezone in /etc/php5/apache2/php.ini


Watch your log for issues/performance
```
tail -f /var/log/blacklistmonitor.log
```



