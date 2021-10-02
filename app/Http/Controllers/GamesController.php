<?php

    namespace App\Http\Controllers;

    class GamesController extends Controller
    {

        const GAME_STATES = [
            ['code' => 'IN_PROGRESS', 'name' => 'En progreso'],
            ['code' => 'FINISHED', 'name' => 'Finalizado']
        ];

        const ACTIONS = [
            ['code' => 'ROCK', 'name' => 'piedra', 'image' => 'asdf', 'strongAgainst' => ['SCISSORS']],
            ['code' => 'PAPER', 'name' => 'papel', 'image' => 'asdfd', 'strongAgainst' => ['ROCK']],
            ['code' => 'SCISSORS', 'name' => 'TIJERAS', 'image' => 'asdfd', 'strongAgainst' => ['PAPER']]
        ];

        const MODES = [
            ['code' => 'ROCK', 'name' => 'piedra', 'allowedActionCodes' => ['ROCK', 'PAPER', 'SCISSORS']]
        ];

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
        }

        public function settings()
        {
            return response()->json([
                'game_states' => self::GAME_STATES,
                'actions' => self::ACTIONS,
                'modes' => self::MODES
            ]);
        }
    }
