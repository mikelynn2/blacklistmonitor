# BlacklistMonitor
```
Copyright (c) by respective owners. All rights reserved.  Released under license as described in the file LICENSE.txt
```
Application for monitoring Domains and IPs on RBLs.

## Prerequisite software
- [MySQL](http://www.MySQL.org) or [MariaDB](https://mariadb.org/) are needed for the database.
- Most likely you'll need your own DNS server as well.  You can use [Bind](https://www.isc.org/downloads/bind/) or even better (its faster) [unbound](https://www.unbound.net/)
- Apache or Nginx


## Installation (Ubuntu)
```
$ apt-get -y install apache2
$ apt-get -y install mariadb-server mariadb-client mariadb-common
$ apt-get -y install php5 php5-mysqlnd php5-cli php5-curl
$ update-rc.d apache2 defaults
$ update-rc.d mysql defaults
```

Go into the directory you want to install BlacklistMonitor into and clone the git repo.  Usually this would be a web server directory like /var/www/html/.  The rest of the commands below assume you're using this dir and the default config files do as well.

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

After you've copied the config file you need to edit it to customize it for your setup here: /etc/blacklistmonitor.cfg


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

### Website
The default username and password to the portal is admin/pa55w0rd


Watch your log for issues/performance
```
$ tail -f /var/log/blacklistmonitor.log
```



