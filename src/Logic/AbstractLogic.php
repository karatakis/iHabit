<?php
namespace App\Logic;

use Slim\Container;

class AbstractLogic {

    protected $container;
    protected $connection;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->connection = $container->get('database');
    }
}