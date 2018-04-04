<?php
namespace App\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;

class HabitController extends AbstractController {

    protected $HabitLogic;


    public function __construct(Container $container) {
        parent::__construct($container);
        $this->HabitLogic = $this->container->get('logic')['HabitLogic'];
    }

    private function validate_params($params) {
        $paramsValidator = Validator::key('name', Validator::stringType()->length(1, 254))->key('counter_value', Validator::intVal()->min(1));

        $paramsValidator->assert($params);
    }

    private function validate_args($args) {
        $argsValidator = Validator::key('id', Validator::intVal()->min(1));

        $argsValidator->assert($args);
    }

    public function list(Request $request, Response $response, array $args) {
        $user_uuid = $request->getAttribute('user_uuid');

        $habits = $this->HabitLogic->list($user_uuid);

        return $response->withJson($habits);
    }

    public function create(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $this->validate_params($params);

        $user_uuid = $request->getAttribute('user_uuid');

        $habit = $this->HabitLogic->create($params, $user_uuid);

        return $response->withJson($habit);
    }

    public function read(Request $request, Response $response, array $args) {
        $user_uuid = $request->getAttribute('user_uuid');

        $this->validate_args($args);

        $habit = $this->HabitLogic->read($args['id'], $user_uuid);

        return $response->withJson($habit);
    }

    public function complete(Request $request, Response $response, array $args) {
        $user_uuid = $request->getAttribute('user_uuid');

        $this->validate_args($args);

        $habit = $this->HabitLogic->complete($args['id'], $user_uuid);

        return $response->withJson($habit);
    }

    public function reverse(Request $request, Response $response, array $args) {
        $user_uuid = $request->getAttribute('user_uuid');

        $this->validate_args($args);

        $habit = $this->HabitLogic->reverse($args['id'], $user_uuid);

        return $response->withJson($habit);
    }

    public function update(Request $request, Response $response, array $args) {
        $params = $request->getParsedBody();

        $this->validate_params($params);

        $user_uuid = $request->getAttribute('user_uuid');

        $this->validate_args($args);

        $habit = $this->HabitLogic->update($args['id'], $user_uuid, $params);

        return $response->withJson($habit);
    }

    public function destroy(Request $request, Response $response, array $args) {
        $user_uuid = $request->getAttribute('user_uuid');

        $this->validate_args($args);

        $habit = $this->HabitLogic->destroy($args['id'], $user_uuid);

        return $response->withJson($habit);
    }

}