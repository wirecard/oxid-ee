########################################################################
# Dockerfile for oxid-ee, on OXID 6
########################################################################

########################################################################
# Base PHP image with OXID
########################################################################
ARG PHP_VERSION
FROM php:${PHP_VERSION}-apache-stretch AS base
ARG PAYPAL_PASSWORD=0

ENV COMPOSER_NO_INTERACTION=1
ENV WEBROOT_DIR=/var/www/html/source
ENV PAYPAL_PASSWORD $PAYPAL_PASSWORD

RUN set -ex; \
    # set DocumentRoot (see: https://hub.docker.com/_/php)
    sed -ri -e 's!/var/www/html!${WEBROOT_DIR}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${WEBROOT_DIR}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    # install packages
    apt-get update -qq; \
    apt-get install -qq -y --no-install-recommends \
        curl \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        mysql-client \
        netcat \
        sudo \
        unzip \
        vim \
        zlib1g-dev \
    ; \
    rm -rf /var/lib/apt/lists/*; \
    # install php extensions
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
    docker-php-ext-install -j$(nproc) bcmath gd intl pdo_mysql soap zip > /dev/null; \
    pecl install xdebug > /dev/null; \
    docker-php-ext-enable xdebug; \
    # configure php for development
    cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini; \
    # install composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --version=1.8.4 --filename=composer; \
    chown www-data:www-data /var/www; \
    sudo -u www-data composer global require hirak/prestissimo; \
    # configure apache
    a2enmod rewrite headers

# create OXID project
ARG OXID_VERSION
ARG MODULE_PATH

ENV PARTIAL_MODULE_PATHS=${MODULE_PATH}
RUN set -ex; \
    sudo -u www-data composer create-project oxid-esales/oxideshop-project . ${OXID_VERSION}; \
    sudo -u www-data touch ${WEBROOT_DIR}/log/oxideshop.log
    # chown -R www-data:www-data ${WEBROOT_DIR}

# copy OXID configuration
COPY --chown=www-data:www-data ./ops/oxid-config/config.inc.php ${WEBROOT_DIR}/config.inc.php


########################################################################
# CI image
########################################################################
FROM base as ci
ARG MODULE_NAME
ARG MODULE_PATH

# add module to shop's dependencies
RUN set -ex; \
    composer config minimum-stability dev; \
    composer config repositories.${MODULE_NAME} path ${WEBROOT_DIR}/modules/${MODULE_PATH}

# copy module into container
COPY --chown=www-data:www-data ./ ${WEBROOT_DIR}/modules/${MODULE_PATH}
RUN set -ex; \
    # install module into shop
    sudo -u www-data composer require "${MODULE_NAME}:*"; \
    # install module's dependencies for tests that run independently of the shop
    sudo -u www-data composer install -d ${WEBROOT_DIR}/modules/${MODULE_PATH}

COPY ./ops/docker/*.sh /usr/local/bin/
ENTRYPOINT ["entrypoint.ci.sh"]


########################################################################
# Local testing image
########################################################################
FROM ci as local-testing
ENTRYPOINT ["entrypoint.dev.sh"]
