<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;

// replace with file to your own project bootstrap
require_once 'bootstrap.php';

#$helperSet = ConsoleRunner::createHelperSet($EntityManager);
#$helperSet->set(['dialog' => new \Symfony\Component\Console\Helper\QuestionHelper()]);
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'entityManager' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($EntityManager),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($EntityManager),
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($EntityManager->getConnection()),
    'dialog' => new \Symfony\Component\Console\Helper\QuestionHelper(),
));
/** Migrations setup */

$configuration = new Configuration($EntityManager->getConnection());
$configuration->setMigrationsNamespace('Migration');
$configuration->setMigrationsTableName('migration_versions');
$configuration->setMigrationsDirectory('src/migrations/');

$diff = new DiffCommand();
$exec = new ExecuteCommand();
$gen = new GenerateCommand();
$migrate = new MigrateCommand();
$status = new StatusCommand();
$ver = new VersionCommand();

$diff->setMigrationConfiguration($configuration);


$cli = ConsoleRunner::createApplication($helperSet, [
    $diff, $exec, $gen, $migrate, $status, $ver
]);
return $cli->run();
