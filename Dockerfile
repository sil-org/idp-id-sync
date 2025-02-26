FROM silintl/php8:8.1
LABEL maintainer="matt_henderson@sil.org"

ARG GITHUB_REF_NAME
ENV GITHUB_REF_NAME=$GITHUB_REF_NAME

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

CMD ["/data/run.sh"]
