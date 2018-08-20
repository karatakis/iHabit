<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Application logic injector
$container['logic'] = function($c) {
    // TODO optimize to load specified logic
    return [
        'HabitLogic' => new \App\Logic\HabitLogic($c),
    ];
};

// Database Abstraction Layer -- DBAL
$container['database'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $config = new \Doctrine\DBAL\Configuration();
    $connection = \Doctrine\DBAL\DriverManager::getConnection($settings, $config);
    return $connection;
};

// TODO add Exception Handler