FROM mcr.microsoft.com/devcontainers/php:1-8.4-bookworm

ENV XDEBUG_MODE=off

RUN rm -f /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        autoconf \
        make \
        g++ \
        libnghttp2-dev \
        libssl-dev \
        pkg-config \
    && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/dkkoma/php-grpc-lite.git /tmp/php-grpc-lite \
    && cd /tmp/php-grpc-lite \
    && git checkout 21567d001b2e00708b4ba2c99c9cd235d11f6d3e \
    && cd ext/grpc \
    && phpize \
    && ./configure --enable-grpc \
    && make -j"$(nproc)" \
    && make install \
    && rm -rf /tmp/php-grpc-lite

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
