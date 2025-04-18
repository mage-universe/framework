FROM php:8.3-cli-alpine

ARG UID
ARG GID

RUN apk --update --no-cache add curl git patch

RUN apk --update --no-cache add --virtual deps $PHPIZE_DEPS linux-headers imagemagick-dev \
 && apk --update --no-cache add icu-dev mariadb-client mariadb-connector-c-dev imagemagick

#Fix till release on pecl
RUN curl -L -o /tmp/imagick.tar.gz https://github.com/Imagick/imagick/archive/7088edc353f53c4bc644573a79cdcd67a726ae16.tar.gz \
    && tar --strip-components=1 -xf /tmp/imagick.tar.gz \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/ext-imagick.ini \
    && rm -rf /tmp/*

RUN pecl install -f xdebug apcu \
 && docker-php-ext-install -j$(nproc) opcache intl pdo pdo_mysql \
 && docker-php-ext-enable xdebug apcu opcache intl pdo_mysql \
 && apk del --purge deps

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "opcache.enable=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.enable_cli=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.jit=1255" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
 && echo "apc.enabled=0" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
 && echo "apc.enabled_cli=0" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

RUN addgroup -S -g ${GID} app \
 && adduser -S -D -u ${UID} -s /bin/ash -h /home/app -G app app \
 && chown -R app:app /home/app \
 && chmod -R g+w /home/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN mkdir -p /home/app/.composer \
 && chmod -R g+w /home/app/.composer

USER app
WORKDIR /app
