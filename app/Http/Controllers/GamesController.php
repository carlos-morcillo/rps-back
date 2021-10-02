<?php

    namespace App\Http\Controllers;


    use App\Models\Game;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Cache;

    class GamesController extends Controller
    {

        public function settings()
        {
            return response()->json([
                'game_states' => Game::STATES,
                'actions' => Game::ACTIONS,
                'modes' => Game::MODES
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


        public function create(string $userUUID, string $modeCode = 'TRADITIONAL', int $numberOfRounds = 3)
        {
            return response()->json(Game::create($modeCode, $numberOfRounds, $userUUID));
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

        public function addRound(Request $request, string $userUUID, string $id = null)
        {
            try {
                $data = $request->all();
                if (!$game = Game::find($userUUID, $id)) {
                    return response()->json('game_not_found', Response::HTTP_NOT_FOUND);
                }

                $game->addRound($data);
                return response()->json($game);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
