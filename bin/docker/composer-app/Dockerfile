FROM php:8.4-cli

# Set the /app as a current working directory
# All subsequent commands:
# - RUN
# - COPY (its destination path, if not absolute)
# - CMD
# shall execute inside of this docker directory
WORKDIR /app

# Copy the project root (all its files) into the /app directory of docker container
# Beware, that ../../ must be within docker's context
COPY ../../ /app

# Install
# - git and unzip for pulling composer packages
# - php dependencies (cli php is minimal, redis wouldn't work without it)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    $PHPIZE_DEPS

# Compile redis extension and enable
RUN pecl install redis && docker-php-ext-enable redis

RUN rm -rf /var/lib/apt/lists/*

RUN git config --global --add safe.directory /app

# Install composer into container
# Multistage COPY -> composer gets pulled temporarily and copied into docker container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the entrypoint script
COPY bin/docker/composer-app/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make the entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["entrypoint.sh"]
