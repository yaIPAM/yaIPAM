#!/bin/sh

# Doctrine 2 Entities Generator v0.1
# ==================================

# Requirements
# ============
# It's necessary to create a composer project before exec this script.
# More info about composer at https://getcomposer.org/doc/00-intro.md


echo Doctrine 2 Entities Generator v0.1
echo ==================================

# Remove ./config/yml directory before
rm -rf ./config/yml

# Create yaml directory
mkdir ./config/yml

# Read the ./cli-config.php (by default) and generate mapping yaml files to ./config/yaml directory
php vendor/bin/doctrine orm:convert-mapping --namespace="" --force --from-database yml ./config/yml

# Generated models to ./src directory
php vendor/bin/doctrine orm:generate:models --generate-annotations=false --update-models=true --generate-methods=false ./src/models

# Validate schema
php vendor/bin/doctrine orm:validate-schema
