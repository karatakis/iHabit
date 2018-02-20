<?php
namespace App\Controllers;

use Slim\Container;

class AbstractController {

    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }
}