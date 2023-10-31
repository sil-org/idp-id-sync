FROM silintl/php8:8.1
LABEL maintainer="matt_henderson@sil.org"

ENV REFRESHED_AT 2020-06-10

RUN mkdir -p /data

WORKDIR /data

# Install/cleanup composer dependencies
COPY application/composer.json /data/
COPY application/composer.lock /data/
RUN composer install --prefer-dist --no-interaction --no-dev --optimize-autoloader

# It is expected that /data is = application/ in project folder
COPY application/ /data/

# Fix folder permissions
RUN chown -R www-data:www-data \
    console/runtime/

ENTRYPOINT [ "/data/yii" ]
