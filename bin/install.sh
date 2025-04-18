#!/bin/bash

# Load environment variables from .env file
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

# Source the get_compose_files function from docker.sh
source bin/docker.sh

# Determine if a non-default database authentication plugin needs to be used
determine_auth_option() {
  if [ "$LOCAL_DB_TYPE" != "mysql" ]; then
    return
  fi

  if [ "$LOCAL_PHP" != "7.2-fpm" ] && [ "$LOCAL_PHP" != "7.3-fpm" ]; then
    return
  fi

  # MySQL 8.4 removed --default-authentication-plugin in favor of --authentication-policy
  if [ "$LOCAL_DB_VERSION" = "8.4" ]; then
    export LOCAL_DB_AUTH_OPTION="--authentication-policy=mysql_native_password"
  else
    export LOCAL_DB_AUTH_OPTION="--default-authentication-plugin=mysql_native_password"
  fi
}

determine_auth_option

# Function to run WP-CLI commands in the Docker environment
wp_cli() {
  COMPOSE_FILES=$(get_compose_files)
  docker compose $COMPOSE_FILES run --quiet-pull --rm cli $1 --path=/var/www/${LOCAL_DIR}
}

# Create wp-config.php
wp_cli "config create --dbname=wordpress_develop --dbuser=root --dbpass=password --dbhost=mysql --force"

# Add the debug settings to wp-config.php
wp_cli "config set WP_DEBUG ${LOCAL_WP_DEBUG} --raw --type=constant"
wp_cli "config set WP_DEBUG_LOG ${LOCAL_WP_DEBUG_LOG} --raw --type=constant"
wp_cli "config set WP_DEBUG_DISPLAY ${LOCAL_WP_DEBUG_DISPLAY} --raw --type=constant"
wp_cli "config set SCRIPT_DEBUG ${LOCAL_SCRIPT_DEBUG} --raw --type=constant"
wp_cli "config set WP_ENVIRONMENT_TYPE ${LOCAL_WP_ENVIRONMENT_TYPE} --type=constant"
wp_cli "config set WP_DEVELOPMENT_MODE ${LOCAL_WP_DEVELOPMENT_MODE} --type=constant"

# Move wp-config.php to the base directory
mv "${LOCAL_DIR}/wp-config.php" wp-config.php

# Create wp-tests-config.php from wp-tests-config-sample.php
sed -e "s/youremptytestdbnamehere/wordpress_develop_tests/" \
    -e "s/yourusernamehere/root/" \
    -e "s/yourpasswordhere/password/" \
    -e "s/localhost/mysql/" \
    -e "s/'WP_TESTS_DOMAIN', 'example.org'/'WP_TESTS_DOMAIN', '${LOCAL_WP_TESTS_DOMAIN}'/" \
    wp-tests-config-sample.php > wp-tests-config.php

# Add FS_METHOD to wp-tests-config.php
echo "define( 'FS_METHOD', 'direct' );" >> wp-tests-config.php

# Wait for the site to be available
echo "Waiting for the site to be available..."
timeout=60
counter=0
while ! curl -s "http://localhost:${LOCAL_PORT}" > /dev/null; do
  sleep 1
  counter=$((counter + 1))
  if [ $counter -ge $timeout ]; then
    echo "Timed out waiting for the site to be available."
    exit 1
  fi
done

# Install WordPress
wp_cli "db reset --yes"
if [ "$LOCAL_MULTISITE" = "true" ]; then
  wp_cli "core multisite-install --title=\"WordPress Develop\" --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email --url=http://localhost:${LOCAL_PORT}"
else
  wp_cli "core install --title=\"WordPress Develop\" --admin_user=admin --admin_password=password --admin_email=test@test.com --skip-email --url=http://localhost:${LOCAL_PORT}"
fi

echo "WordPress installation complete!"
