<?php
namespace App\Controllers;

use Slim\Container;

/**
 * Class used to provide the basic skeleton
 * for Application Controllers
 */
class AbstractController {

    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }
}