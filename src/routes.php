<?php
use App\Middleware\AuthJWT;

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->post('/register', 'App\Controllers\UserController:register');
$app->post('/login', 'App\Controllers\UserController:login');
// $app->post('/reset-password', 'App\Controllers\UserController:reset_password');
// $app->get('/verify', 'App\Controllers\UserController:verify');
// $app->post('/unregister', 'App\Controllers\UserController:delete_user');
// Change user password
$app->post('/change-password', 'App\Controllers\UserController:change_password');

$app->group('/v1', function() use ($app) {
    // Get habits list
    $app->get('/habits', 'App\Controllers\HabitController:list');

    // Create new habit
    $app->post('/habit', 'App\Controllers\HabitController:create');

    // Read specific habit info
    $app->get('/habit/{id}', 'App\Controllers\HabitController:read');

    // Complete habit
    $app->post('/habit/{id}', 'App\Controllers\HabitController:complete');
    $app->put('/habit/{id}', 'App\Controllers\HabitController:reverse');

    // Update habit info
    $app->patch('/habit/{id}', 'App\Controllers\HabitController:update');

    // Delete habit
    $app->delete('/habit/{id}', 'App\Controllers\HabitController:destroy');

})->add(new AuthJWT($app->getContainer()->get('settings')['jwt']));


