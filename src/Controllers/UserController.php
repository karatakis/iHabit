<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class UserController extends AbstractController {

    public function register(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();
        // TODO validate user input

        $UserLogic = $this->container->get('logic')['UserLogic'];

        $UserLogic->register($params['username'], $params['email'], $params['password']);

        return $response->withStatus(200);
    }
}