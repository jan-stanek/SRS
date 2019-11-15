<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator();

$configurator->setDebugMode(getenv('DEVELOPMENT_MACHINE') === 'true');
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addParameters([
    'wwwDir' => realpath(__DIR__ . '/../www'),
]);

$configurator->addConfig(__DIR__ . '/config/config.neon');
if (PHP_SAPI != 'cli') {
    $configurator->addConfig(__DIR__ . '/config/config.console.neon');
}
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
