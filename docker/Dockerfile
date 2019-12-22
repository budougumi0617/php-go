FROM php:7.4.1-cli

# https://github.com/bufferings/phpstorm-docker-cakephp/blob/master/docker/Dockerfile
# Xdebug
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug

RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends git \
    && apt-get clean && rm -rf /var/cache/apt/archives/* /var/lib/apt/lists/*

# Composer
ENV COMPOSER_HOME /tmp
COPY --from=composer:1.8.6 /usr/bin/composer /usr/bin/composer

# To switch user on runtime
RUN apt-get update && apt-get -y install \
    gosu zip unzip\
 && rm -rf /var/lib/apt/lists/*
 WORKDIR /app
CMD php -S 0.0.0.0:80
