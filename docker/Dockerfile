FROM php:8.1-apache-bullseye

# set main params
ENV APP_HOME /var/www/html
ARG HOST_UID=1000
ARG HOST_GID=1000
ENV USERNAME=www-data

ENV DEBIAN_FRONTEND noninteractive
ENV DEBCONF_NOWARNINGS="yes"

# install all the dependencies and enable PHP modules
RUN apt-get update && apt-get install -y --no-install-recommends \
    apt-utils cron curl g++ gettext libicu-dev openssl wget \
    libc-client-dev libkrb5-dev libxml2 libxml2-dev \
    libgd-dev libmcrypt-dev bzip2 zlib1g-dev unzip \
    libbz2-dev libtidy-dev libcurl4-openssl-dev \
    libz-dev libmemcached-dev libxslt-dev libpq-dev \
    libzip4 libzip-dev libwebp-dev \
    libjpeg62-turbo libpng16-16 libx11-6 \
    libxcb1 libxext6 libxrender1 \
    procps nano git libreadline-dev sudo redis-tools

RUN docker-php-ext-configure gd --with-jpeg --with-webp && \
    docker-php-ext-configure hash --with-mhash && \
    docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
    docker-php-ext-configure intl

RUN docker-php-ext-install bcmath bz2 calendar dba exif gettext iconv intl && \
    docker-php-ext-install mysqli pgsql pdo pdo_mysql pdo_pgsql && \
    docker-php-ext-install opcache soap tidy gd xsl zip sockets

RUN pecl update-channels && pecl install -s -o redis && \
    pecl install -s -o xdebug

RUN docker-php-ext-enable redis xdebug

# install imagick
RUN apt-get update && apt-get install -y \
    libmagickwand-dev libmagickcore-dev && \
    yes '' | pecl install -f imagick
RUN docker-php-ext-enable imagick

# install node
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs

# create document root, fix permissions for www-data user and change owner to www-data
RUN mkdir -p $APP_HOME/public && \
    mkdir -p /home/$USERNAME && chown $USERNAME:$USERNAME /home/$USERNAME && \
    usermod -o -u $HOST_UID $USERNAME -d /home/$USERNAME && \
    groupmod -o -g $HOST_GID $USERNAME && \
    chown -R ${USERNAME}:${USERNAME} $APP_HOME

# disable default site
RUN a2dissite 000-default.conf

# put default apache config, enable sites
COPY ./docker/default-host.conf /etc/apache2/sites-available/default-host.conf
COPY ./docker/default-host-ssl.conf /etc/apache2/sites-available/default-host-ssl.conf
RUN a2ensite default-host.conf && a2ensite default-host-ssl

# enable apache modules
RUN a2enmod rewrite
RUN a2enmod ssl

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# generate certificates
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem -subj "/C=AT/ST=Vienna/L=Vienna/O=Security/OU=Development/CN=example.com"

# add cron task
COPY --chown=root:crontab ./docker/cron /var/spool/cron/crontabs/root
RUN chmod 0600 /var/spool/cron/crontabs/root

RUN service apache2 restart

WORKDIR $APP_HOME

USER root
