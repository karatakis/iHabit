<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;

class UserController extends AbstractController {

    public function register(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $paramsValidator = Validator::key('username', Validator::stringType()->length(4, 32)->noWhitespace())
                                    ->key('email', Validator::email())
                                    ->key('password', Validator::stringType()->length(8));

        $paramsValidator->assert($params);

        $UserLogic = $this->container->get('logic')['UserLogic'];

        $UserLogic->register($params['username'], $params['email'], $params['password']);

        return $response->withStatus(200);
    }

    public function login(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $paramsValidator = Validator::key('email', Validator::email())
                                    ->key('password', Validator::stringType()->length(8));

        $paramsValidator->assert($params);

        $UserLogic = $this->container->get('logic')['UserLogic'];

        $token = $UserLogic->login_with_email($params['email'], $params['password']);

        if ($token) {
            return $response->withJson([
                'token' => $token . ''
            ]);
        } else {
            return $response->withStatus(403)->withJson(['message' => 'Invalid email/password combination.']);
        }
    }
}