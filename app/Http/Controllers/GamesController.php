<?php

    namespace App\Http\Controllers;


    use App\Models\Game;
    use Illuminate\Support\Facades\Cache;

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
            [
                'code' => 'TRADITIONAL',
                'name' => 'Tradicional',
                'allowedActionCodes' => ['ROCK', 'PAPER', 'SCISSORS']
            ]
        ];

        public function settings()
        {
            return response()->json([
                'game_states' => self::GAME_STATES,
                'actions' => self::ACTIONS,
                'modes' => self::MODES
            ]);
        }

        /**
         * Listado de partidas del jugador
         * @param string $userUUID
         * @return \Illuminate\Http\JsonResponse
         */
        public function index(string $userUUID)
        {
            $games = Cache::get("games:$userUUID", collect());
            return response()->json($games);
        }

        public function show(string $userUUID, string $id)
        {
            if (!$game = Game::find($userUUID, $id)) {
                throw new \Exception('game_not_found');
            }
            return response()->json($game);
        }

        /**
         * Obtiene el histórico de jugadas del usuario
         * @param Request $request
         * @param string $userUUID
         * @return \Illuminate\Http\JsonResponse
         */
        public function historical(string $userUUID)
        {
            return response()->json(Cache::get("games:$userUUID", collect([])));
        }


        public function create(string $modeCode = 'TRADITIONAL', string $userUUID)
        {
            return response()->json(Game::create($modeCode, $userUUID));
        }

        /**
         * Borra todos los juegos del usuario o uno en particular
         * @param string $userUUID
         * @param string $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function delete(string $userUUID, string $id = null)
        {
            $result = Game::delete($userUUID, $id);
            return response()->json($result);
        }

        public function saveRound(string $id)
        {
//            if( !           $game = Game::find($id)) {
//                return response()->json('GAME_NOT_FOUND', Response::HTTP_NOT_FOUND);
//            }
//            $game->
//            return Game::create($userUUID);
        }
    }
