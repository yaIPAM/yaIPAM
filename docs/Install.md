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
** apcu (If you want caching)
* Apache 2.2 or higher with mod rewrite enabled and allowed
* git

## 1. Clone from Github

At the moment there is no stable release so you must be somehow familiar in cloning Github projects.

`git clone https://github.com/KlaasT/yaIPAM.git`

## 2. Install composer dependencies

We don't want to reinvent the wheel, so we will depend on other composer libraries which help us doing our job. These dependencies need to be installed using composer:

`php composer.phar update`

## 3. Copy config.dist.php to config.php

You should edit the config.php with the data for your setup before proceeding with the database.

## 4. Create the cache/ directory

The cache directory is needed to cache template files. It needs to be writable by PHP.

## 5. Install the SQL schema

To install the schema you can use the following command:

`./manager orm:schema-tools:create`

## 6. Updating from older version

Updating should be quite easy if you have just installed the new application code and configured the config.php correctly.
Just execute the migrations tool:

`./manager migrations:execute`