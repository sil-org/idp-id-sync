FROM silintl/php8:8.1
LABEL maintainer="matt_henderson@sil.org"

ENV REFRESHED_AT 2020-06-10

# Install cron
RUN apt-get update \
    && apt-get install -y \
       cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /data

# Copy in cron configuration
COPY dockerbuild/idsync-cron /etc/cron.d/idsync-cron
RUN chmod 0644 /etc/cron.d/idsync-cron

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

EXPOSE 80
CMD ["/data/run.sh"]
