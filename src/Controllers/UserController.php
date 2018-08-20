<?php
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Respect\Validation\Validator;
use App\Logic\UserLogic;

class UserController extends AbstractController {
    protected $userLogic;

    public function __construct(Container $container) {
        parent::__construct($container);

        $this->userLogic = new UserLogic($this->container);
    }

    public function register(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $paramsValidator = Validator::key('username', Validator::stringType()->length(4, 32)->noWhitespace())
                                    ->key('email', Validator::email())
                                    ->key('password', Validator::stringType()->length(8, 64));

        $paramsValidator->assert($params);

        $this->userLogic->register($params['username'], $params['email'], $params['password']);

        return $response->withStatus(200);
    }

    public function login(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $paramsValidator = Validator::key('email', Validator::email())
                                    ->key('password', Validator::stringType()->length(8, 64));

        $paramsValidator->assert($params);

        $token = $this->userLogic->login_with_email($params['email'], $params['password']);

        return $response->withJson([
            'token' => $token . ''
        ]);
    }

    public function change_password(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $paramsValidator = Validator::key('email', Validator::email())
                                     ->key('old_password', Validator::stringType()->length(8, 64))
                                     ->key('new_password', Validator::stringType()->length(8, 64));

        $paramsValidator->assert($params);

        $this->userLogic->change_password($params['email'], $params['old_password'], $params['new_password']);

        $token = $this->userLogic->login_with_email($params['email'], $params['new_password']);

        return $response->withJson([
            'token' => $token . ''
        ]);
    }
}