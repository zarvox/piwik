#!/bin/bash

# Create a bunch of folders under the clean /var that php, nginx, and mysql expect to exist
mkdir -p /var/lib/mysql
mkdir -p /var/lib/nginx
mkdir -p /var/log
mkdir -p /var/log/mysql
mkdir -p /var/log/nginx
# Wipe /var/run, since pidfiles and socket files from previous launches should go away
# TODO someday: I'd prefer a tmpfs for these.
rm -rf /var/run
mkdir -p /var/run
mkdir -p /var/run/mysqld

# Ensure mysql tables created
HOME=/etc/mysql /usr/bin/mysql_install_db --force

MYSQL_SOCKET_FILE=/var/run/mysqld/mysqld.sock
# Spawn mysqld, wait for the DB to be up, so we can do the DB migration
HOME=/etc/mysql /usr/sbin/mysqld &
while [ ! -e $MYSQL_SOCKET_FILE ] ; do
    echo "waiting for mysql to be available at $MYSQL_SOCKET_FILE"
    sleep .2
done

# Ensure the 'piwik' database exists.
echo "CREATE DATABASE IF NOT EXISTS piwik DEFAULT CHARACTER SET utf8" | mysql --user root --socket $MYSQL_SOCKET_FILE
echo "database 'piwik' created"

# App-specific: ensure /var/piwik/tmp and /var/piwik/config exist.  Copy the
# config snippets to where piwik will look for them.
mkdir -p /var/piwik/tmp
mkdir -p /var/piwik/config
cp -av /opt/app/config-orig/* /var/piwik/config/

PIWIK_CONFIG_FILE=/var/piwik/config/config.ini.php
if [ ! -f $PIWIK_CONFIG_FILE ] ; then
    echo "first run; adding default config"
    #cp /opt/app/config-orig/config.ini.php.example $PIWIK_CONFIG_FILE

    # Import schema.  This requires:
    # 1) a fully-populated config, including DB settings
    # 2) MySQL is up.
    #time php /opt/app/sandstorm-setup.php

    # TODO: INSERT INTO site(idsite, name, host) VALUES (1, "Analytics", "http://example.com")
    #echo "Ran import script"
fi

#time php /opt/app/console core:update --yes -n -vv
#echo "Ran update script"

time php /opt/app/console customvariables:info

# Spawn PHP
/usr/sbin/php5-fpm --nodaemonize --fpm-config /etc/php5/fpm/php-fpm.conf &
# Wait until PHP has bound its socket, indicating readiness
while [ ! -e /var/run/php5-fpm.sock ] ; do
    echo "waiting for php5-fpm to be available at /var/run/php5-fpm.sock"
    sleep .2
done
echo "php5-fpm up."

# Start nginx.
/usr/sbin/nginx -g "daemon off;"
