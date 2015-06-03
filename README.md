# BlacklistMonitor
```
/*
Copyright (c) by respective owners. All rights reserved.  Released under a BSD (revised)
license as described in the file LICENSE.
 */
```
Application for monitoring Domains and IPs on RBLs

Work in progress

## Prerequisite software
- [MySQL](http://www.MySQL.org) or [MariaDB](https://mariadb.org/) are needed for the database.
- Most likely you'll need your own DNS server as well.  You can use [Bind](https://www.isc.org/downloads/bind/) or even better (its faster) [unbound](https://www.unbound.net/)
- Apache or Nginx


## Installation (Ubuntu)
```
$ apt-get -y install apache2
$ apt-get -y install mariadb-server mariadb-client mariadb-common
$ apt-get -y install php5 php5-mysqlnd php5-cli
$ update-rc.d apache2 defaults
$ update-rc.d mysql defaults
```

Over ssh, go into the directory you want to install BlacklistMonitor into and clone the git repo.  Usually this would be a web server directory like /var/www/html/ - the rest of the commands below assume you're using this dir and the config files do as well.

```
$ cd /var/www/html/
$ git clone git://github.com/mikerlynn/blacklistmonitor.git
```

## Initialize Data
```
$ mysql -p < /var/www/html/blacklistmonitor/setup/blacklistmonitor.sql
```

## Copy Default Config
```
$ cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.cfg /etc/
```

## Schedule Cron
```
$ cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.cron /etc/cron.d/
```

## Service
```
$ cp /var/www/html/blacklistmonitor/setup/blacklistmonitor.conf /etc/init/
```

### start/stop/restart
```
$ start blacklistmonitor
$ stop blacklistmonitor
$ restart blacklistmonitor
```

Watch your log for issues/performance
```
$ tail -f /var/log/blacklistmonitor.log
```



