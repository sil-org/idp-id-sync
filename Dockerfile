FROM silintl/php8:8.1
LABEL maintainer="matt_henderson@sil.org"

ENV REFRESHED_AT 2023-11-09

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

ADD https://github.com/silinternational/config-shim/releases/latest/download/config-shim.gz config-shim.gz
RUN gzip -d config-shim.gz && chmod 755 config-shim && mv config-shim /usr/local/bin

CMD ["/data/run.sh"]
