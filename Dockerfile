FROM php:7.1-fpm

ARG DIRECTORY_PROJECT=/var/www

# Set working directory
WORKDIR $DIRECTORY_PROJECT
# Install Packages
RUN apt-get update && apt-get install -y \
  git zip unzip gnupg nginx\
  libxml2-dev zip unzip zlib1g-dev \
  libpng-dev libmcrypt-dev \
  --no-install-recommends

  # Docker extension install
RUN docker-php-ext-install \
  opcache \
  pdo_mysql \
  pdo \
  mbstring \
  tokenizer \
  xml \
  ctype \
  json \
  zip \
  soap \
  mcrypt \
  gd \
  bcmath \
  sockets

# Configuraciones PHP
RUN echo "\
log_errors = On\n\
error_log = /dev/stderr\n\
error_reporting = E_ALL\n\
post_max_size = 100M\n\
upload_max_filesize = 100M\n\
memory_limit = 512M\n\
max_input_vars = 2000\n\
date.timezone = "America/La_Paz"\n\
max_execution_time = 12000s" > /usr/local/etc/php/php.ini


# Shortcut to replace enabled vhost in nginx
RUN { \
        echo 'server {'; \
        echo '    listen 80;'; \
        echo '    server_name _;'; \
        echo '    root /var/www/simple/public;'; \
        echo '    include /etc/nginx/default.d/*.conf;'; \
        echo '    index index.php;'; \
        echo '    error_log /var/log/nginx/error.log;'; \
        echo '    client_max_body_size 300m;'; \
        echo '    location / {'; \
        echo '        try_files $uri $uri/ /index.php?$query_string;'; \
        echo '    }'; \
        echo '    location ~ [^/]\.php(/|$) {'; \
        echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;'; \
        echo '        fastcgi_index index.php;'; \
        echo '        fastcgi_split_path_info ^(.+?\.php)(/.*)$;'; \
        echo '        fastcgi_read_timeout 3000;'; \
        echo '        # Mitigate https://httpoxy.org/ vulnerabilities'; \
        echo '        fastcgi_param HTTP_PROXY "";'; \
        echo '        fastcgi_pass 127.0.0.1:9000;'; \
        echo '        include fastcgi_params;'; \
        echo '        include fastcgi.conf;'; \
        echo '        fastcgi_param APPLICATION_ENV local;'; \
        echo '    }'; \
        echo '}'; \
    } > /etc/nginx/sites-available/default

# Configuraciones PHP (precargadas via docker-compose.yml desde el volume de servicio webserver => nginx)
# RUN echo "\
# log_errors = On\n\
# error_log = /dev/stderr\n\
# error_reporting = E_ALL\n\
# post_max_size = 100M\n\
# upload_max_filesize = 100M\n\
# memory_limit = 512M\n\
# max_input_vars = 2000\n\
# date.timezone = "America/La_Paz"\n\
# max_execution_time = 12000s" > /usr/local/etc/php/php.ini

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && apt-get remove --purge -y curl \
  && apt-get autoremove -y \
  && apt-get clean

COPY . $DIRECTORY_PROJECT
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer config --no-plugins allow-plugins.kylekatarnls/update-helper true && \
    composer config --no-plugins allow-plugins.symfony/thanks true
RUN composer install


RUN chown -R www-data:www-data storage/

# Node js
ENV NODE_VERSION=13.14.0
RUN apt install -y curl
RUN curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.34.0/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"
RUN node --version
RUN npm --version

# yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt update
RUN apt remove cmdtest
RUN apt install yarn -y

# Init services or another executions on build time
RUN { \
        echo '#!/bin/bash'; \
        echo 'service nginx start'; \
        echo 'php-fpm'; \
    } > /usr/local/bin/entrypoint

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod +x /usr/local/bin/entrypoint

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago


# access to the folder structure
# RUN chgrp -R www-data storage bootstrap/cache && \
# RUN chown -R www-data storage bootstrap/cache && \
# RUN chmod -R ug+rwx storage bootstrap/cache && \
# RUN touch storage/logs/laravel.log && \
# RUN chmod 775 storage/logs/laravel.log && \
# RUN chown www-data storage/logs/laravel.log


# RUN  ln -sf /dev/stderr /var/log/php-errors.log
#RUN  ln -sf /dev/stderr /var/www/storage/logs/laravel.log

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR $DIRECTORY_PROJECT

# Expose port 9000 and start php-fpm server
EXPOSE 9000
EXPOSE 80
CMD ["php-fpm"]
