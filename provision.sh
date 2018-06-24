#!/usr/bin/env bash
apt-get update

#install any binaries for compiling from source
apt-get install -y build-essential

#install apache utilities
apt-get install -y apache2-utils

#install git-scm
apt-get install -y git

#install utilities
apt-get install -y unzip

#install nginx and php-fpm
apt-get install -y nginx php-fpm

#install php
apt-get install -y php php-mysql php-mcrypt php-curl php-cli php-gd php7.0-mbstring php7.0-dom php7.0-bcmath php-imagick php-zip

apt-get install -y memcached

apt-get install -y php-memcached

# Install redis-server
apt-get install -y redis-server
service redis-server start

# Install dos2unix
apt-get install -y dos2unix

sudo timedatectl set-timezone America/New_York

#configuration for silently creating the root password
echo mysql-server mysql-server/root_password select "root" | debconf-set-selections
echo mysql-server mysql-server/root_password_again select "root" | debconf-set-selections

#install mysql
apt-get install -y mysql-server

#restart mysql
service mysql restart

#install node
curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -
apt-get install -y nodejs

#install composer
curl -sS https://getcomposer.org/installer | php --
mv composer.phar /usr/bin/composer

cd /var/www

composer install

#cp .env.example .env

php artisan key:generate

mysql -uroot -proot -e "create database mfl-manager"

# migrate for the platform site
php artisan migrate

#php artisan db:seed --class=LocalDatabaseSeeder

mkdir /etc/nginx/ssl

sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt -subj "/C=US/ST=ME/L=Portland/O=Divcom/CN=mfl-manager.local/emailAddress=admin@divcom.com/"


cat << EOF > /etc/nginx/sites-available/000-default
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /var/www/public;
        index index.php;
        sendfile off;
        server_name _;
        if (\$http_x_forwarded_proto = "http") {
                rewrite        ^ https://\$server_name\$request_uri? permanent;
        }
        location /robots.txt {
                add_header Content-Type text/plain;
                return 200 "User-agent: *\nDisallow: /\n";
        }
        location / {
                try_files \$uri \$uri/ /index.php?\$query_string;
        }
        location ~ \.php\$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        }
}
EOF

ln -s /etc/nginx/sites-available/000-default /etc/nginx/sites-enabled/000-default

cat << EOF > /etc/nginx/sites-available/mfl-manager.conf
server {
        listen 80;
        listen 443;
        server_name mfl-manager.local;
        ssl    on;
        ssl_certificate    /etc/nginx/ssl/nginx.crt;
        ssl_certificate_key    /etc/nginx/ssl/nginx.key;
        error_log /var/log/nginx/error.log;
        access_log /var/log/nginx/access.log;
        root /var/www/public;
        index index.php;
        sendfile off;
        if (\$http_x_forwarded_proto = "http") {
                rewrite        ^ https://\$server_name\$request_uri? permanent;
        }
        location /robots.txt {
                add_header Content-Type text/plain;
                return 200 "User-agent: *\nDisallow: /\n";
        }
        location / {
                try_files \$uri \$uri/ /index.php?\$query_string;
        }
        location ~ \.php\$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        }
}
EOF

ln -s /etc/nginx/sites-available/mfl-manager.conf /etc/nginx/sites-enabled/mfl-manager.conf


if [ -f /etc/nginx/sites-enabled/default ] ; then
    rm /etc/nginx/sites-enabled/default
fi

#update the max filesize upload to 100M
sed -ie 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' /etc/php/7.0/fpm/php.ini
sed -ie 's/post_max_size = 8M/post_max_size = 100M/g' /etc/php/7.0/fpm/php.ini
sed -i '/http {/a client_max_body_size 100m;' /etc/nginx/nginx.conf

#set the max execution time for database backups
sed -ie 's/max_execution_time .*/max_execution_time = 600/g' /etc/php/7.0/fpm/php.ini
sed -i '/http {/a fastcgi_read_timeout 600s;' /etc/nginx/nginx.conf

service nginx restart
service php7.0-fpm restart

grep "cd /var/www" /home/vagrant/.bashrc || printf "cd /var/www\n" >> /home/vagrant/.bashrc

# #install supervisor for laravel queues
# apt-get install -y supervisor

# #setup supervisor config
# cat << EOF > /etc/supervisor/conf.d/laravel-worker.conf
# [program:laravel-worker]
# process_name=%(program_name)s_%(process_num)02d
# command=php /var/www/artisan queue:work sqs --sleep=3 --tries=3
# autostart=true
# autorestart=true
# user=www-data
# numprocs=8
# redirect_stderr=true
# stdout_logfile=/var/www/storage/logs/worker.log
# EOF

# update-rc.d supervisor defaults

# systemctl enable supervisor

#start supervisor
service supervisor stop
service supervisor start