FROM silintl/php8:8.3
LABEL maintainer="gtis_itse_support+idp@sil.org"

ARG GITHUB_REF_NAME
ENV GITHUB_REF_NAME=$GITHUB_REF_NAME

ENV REFRESHED_AT 2025-01-27

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

CMD ["/data/run.sh"]
