FROM mcr.microsoft.com/devcontainers/php:1-8.4-bookworm

ENV XDEBUG_MODE=off

RUN rm -f /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libnghttp2-dev \
        pkg-config \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
