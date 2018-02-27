<?php
namespace App\Logic;

use Slim\Container;

/**
 * Class used to provide the basic skeleton
 * for business Logic classes
 */
class AbstractLogic {

    protected $container;
    protected $connection;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->connection = $container->get('database');
    }
}