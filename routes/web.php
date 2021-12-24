<?php

/** @var Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {

    return $router->app->version();
});

$router->group(
    ['prefix' => 'api', 'middleware' => 'auth'],

    function () use ($router) {

        $router->get(
            'authors',
            ['uses' => 'AuthorController@showAllAuthors']
        );

        $router->get(
            'authors/{id}',
            ['uses' => 'AuthorController@showOneAuthor']
        );

        $router->delete(
            'authors/{id}',
            ['uses' => 'AuthorController@delete']
        );

        $router->put(
            'authors/{id}',
            ['uses' => 'AuthorController@update']
        );
    }
);

$router->post(
    'api/authors',
    [
        'middleware' => 'auth:create:authors',
        'uses' => 'AuthorController@create',
    ]
);
