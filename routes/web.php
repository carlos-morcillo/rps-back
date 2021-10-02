<?php

    /** @var \Laravel\Lumen\Routing\Router $router */

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

    $router->get('/', function () use ($router) {
        return $router->app->version();
    });

    $router->group([], function () use ($router) {
        $router->get('/settings', ['uses' => 'GamesController@settings']);
        $router->get('/historical/{uuid}', ['uses' => 'GamesController@historical']);
        $router->group(['prefix' => 'games'], function () use ($router) {
            $router->post('/{userUUID}', ['uses' => 'GamesController@index']);
            $router->put('/{codeMode}/{userUUID}', ['uses' => 'GamesController@create']);
            $router->get('/{userUUID}/{id}', ['uses' => 'GamesController@show']);
            $router->put('/{id}/rounds', ['uses' => 'GamesController@saveRound']);
            $router->delete('/{userUUID}', ['uses' => 'GamesController@delete']);
            $router->delete('/{userUUID}/{id}', ['uses' => 'GamesController@delete']);
        });
    });

