#!/bin/sh

echo "Starting Jenkins local deployment..."

if [ -n "$GIT_BRANCH" ]; then
    echo "We are on Git branch: "${GIT_BRANCH}
fi

# -------- ENVIRONMENT --------
# Make sure the DB connection parameters exist
if [ -z "$MYSQL_DATABASE" ]; then
    echo "DB connection envvars not found"
    exit 1
fi

# -------- COMPOSER --------
echo "Run composer install..."


composer install

if [ "$?" != 0 ]; then
    echo "'composer install' failed"
    exit 1;
fi

# -------- DB --------
echo "Run Doctrine Migrations..."
php app/console doctrine:migrations:migrate --no-interaction

if [ "$?" != 0 ]; then
    echo "Doctrine Migrations execution failed"
    exit 1;
fi



# -------- ASSETS --------
echo "Run assetic:dump..."
php app/console cache:clear --env=prod --no-warmup --no-debug
php app/console assetic:dump --env=prod --no-debug

if [ "$?" != 0 ]; then
    echo "assetic:dump failed"
    exit 1;
fi
