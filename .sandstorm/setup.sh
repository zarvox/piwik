#!/bin/bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y git pkg-config clang build-essential libgeoip-dev mysql-server nginx php5-cli php5-curl php5-dev php5-fpm php5-gd php5-geoip php5-mysql
unlink /etc/nginx/sites-enabled/default
cat > /etc/nginx/sites-available/sandstorm-php <<EOF
server {
    listen 8000 default_server;
    listen [::]:8000 default_server ipv6only=on;

    server_name localhost;
    root /opt/app;

    # Allow arbitrarily large bodies - Sandstorm can handle them, and requests
    # are authenticated already, so there's no reason for apps to add additional
    # limits by default
    client_max_body_size 0;

    location / {
        index index.php;
        try_files \$uri \$uri/ =404;
    }
    location ~ \\.php\$ {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.+\\.php)(/.+)\$;
        fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF
ln -s /etc/nginx/sites-available/sandstorm-php /etc/nginx/sites-enabled/sandstorm-php
service nginx stop
service php5-fpm stop
service mysql stop
# patch /etc/php5/fpm/pool.d/www.conf to not change uid/gid to www-data
sed --in-place='' \
        --expression='s/^listen.owner = www-data/#listen.owner = www-data/' \
        --expression='s/^listen.group = www-data/#listen.group = www-data/' \
        --expression='s/^user = www-data/#user = www-data/' \
        --expression='s/^group = www-data/#group = www-data/' \
        /etc/php5/fpm/pool.d/www.conf
# patch /etc/php5/fpm/php-fpm.conf to not have a pidfile
sed --in-place='' \
        --expression='s/^pid =/#pid =/' \
        /etc/php5/fpm/php-fpm.conf
# patch /etc/php5/fpm/php.ini to disable $HTTP_RAW_POST_DATA; see
# http://php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data
sed --in-place='' \
        --expression='s/^;always_populate_raw_post_data.*$/always_populate_raw_post_data = -1/' \
        /etc/php5/fpm/php.ini
# patch mysql conf to not change uid
# and to enable local-infile
sed --in-place='' \
        --expression='s/^user\t\t= mysql/#user\t\t= mysql/' \
        --expression='s/^\[mysqld\]$/[mysqld]\nlocal-infile/' \
        --expression='s/^\[mysql\]$/[mysql]\nlocal-infile/' \
        /etc/mysql/my.cnf
# patch nginx conf to not bother trying to setuid, since we're not root
sed --in-place='' \
        --expression 's/^user www-data/#user www-data/' \
        --expression 's#^pid /run/nginx.pid#pid /var/run/nginx.pid#' \
        /etc/nginx/nginx.conf
# Add a conf snippet providing what sandstorm-http-bridge says the protocol is as var fe_https
cat > /etc/nginx/conf.d/50sandstorm.conf << EOF
    # Trust the sandstorm-http-bridge's X-Forwarded-Proto.
    map \$http_x_forwarded_proto \$fe_https {
        default "";
        https on;
    }
EOF
# Adjust fastcgi_params to use the patched fe_https
sed --in-place='' \
        --expression 's/^fastcgi_param *HTTPS.*$/fastcgi_param  HTTPS              \$fe_https if_not_empty;/' \
        /etc/nginx/fastcgi_params
# Configure PHP to look for the MaxMind GeoIP database in the app folder
echo "geoip.custom_directory=/opt/app/misc" >> /etc/php5/mods-available/geoip.ini
# Install capnproto from source, since it's only packaged in sid
cd /root/
git clone https://github.com/sandstorm-io/capnproto.git
cd capnproto/c++
./setup-autotools.sh
autoreconf -i
./configure
make -j$(nproc)
make install
# Build getPublicId
cd /opt/app/.sandstorm/getpublicid/
make
