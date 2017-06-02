# Installation

## Prerequisities
To install yaIPAM, you will need the following software installed and running on your system:
* PHP >= 7.0
* Several PHP Modules
** gettext
** pdo-mysql
** ldap
** intl
** bcmath
* Apache 2.2 or higher with mod rewrite enabled and allowed
* git

## 1. Clone from Github

At the moment there is no stable release so you must be somehow familiar in cloning Github projects.

`git clone https://github.com/KlaasT/yaIPAM.git`

## 2. Install composer dependencies

We don't want to reinvent the wheel, so we will depend on other composer libraries which help us doing our job. These dependencies need to be installed using composer:

`php composer.phar update`

## 3. Install the SQL schema

Currently there is no installer, which can help you with this step. You can use doctrine to install the schema for you vendor/bin/doctrine orm:schema-tools:create

## 4. Copy config.dist.php to config.php

## 5. Edit the values under config.php so they will fit your needs.

## 6. Create a cache/ directory and make it writable for the PHP and HTTPD process
