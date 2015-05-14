#!/bin/bash
# Checks if there's a composer.json, and if so, installs/runs composer.

set -eu

cd /opt/app

if [ -f /opt/app/composer.json ] ; then
    if [ ! -f composer.phar ] ; then
        curl -sS https://getcomposer.org/installer | php
    fi
    php composer.phar install
fi

# Additionally, fetch the GeoLite City geolocation database.
if [ ! -f /opt/app/misc/GeoIPCity.dat ] ; then
    echo "Fetching MaxMind GeoLite City geolocation database..."
    URL=http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.xz
    cd misc
    curl $URL | xz --decompress --stdout > GeoIPCity.dat.partial
    mv GeoIPCity.dat.partial GeoIPCity.dat
fi
