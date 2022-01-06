<?php
/**
 * Based on vendor/cottagelabs/coar-notifications/docker/cli-config.php
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


require_once "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// the connection configuration
$conn = [];
require __DIR__ . '/db-config.php';


// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/vendor/cottagelabs/coar-notifications/src/orm"],
    $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);


// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

return ConsoleRunner::createHelperSet($entityManager);