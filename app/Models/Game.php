<?php

    namespace App\Models;

    use Carbon\Carbon;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Str;

    class Game
    {
        const STATES = [
            [
                'code' => 'IN_PROGRESS',
                'name' => 'En progreso'
            ],
            [
                'code' => 'FINISHED',
                'name' => 'Finalizado'
            ]
        ];

        /**
         * @var string Identificador del juego
         */
        public $id;

        /**
         * @var int Número de rondas del juego
         */
        public $roundNumber;

        /**
         * @var string Código del modo de juego
         */
        public $modeCode;

        /**
         * @var string Código UUID del creador del juego
         */
        public $userUUID;

        /**
         * @var Carbon Fecha de creación
         */
        public $createdAt;

        /**
         * @var Registro de cada ronda
         */
        public $rounds;

        /**
         * @var Código del estado
         */
        public $stateCode;


        public function __construct(string $modeCode, string $userUUID)
        {
            $this->id = Str::uuid()->toString();
            $this->roundNumber = 1;
            $this->userUUID = $userUUID;
            $this->modeCode = $modeCode;
            $this->stateCode = Game::STATES[0]['code'];
            $this->createdAt = Carbon::now();
        }

        /**
         * Crea un nuevo juego y devuelve su información
         * @param string $modeCode
         * @param string $userUUID
         * @return \Illuminate\Http\JsonResponse
         */
        public static function create(string $modeCode, string $userUUID)
        {
            $games = Cache::get("games:$userUUID", collect());
            $game = new Game($modeCode, $userUUID);
            $games->push($game);
            Cache::put("games:$userUUID", $games);
            return $game;
        }


        /**
         * Obtiene un juego en específico a partir de su id
         * @param string $userUUID
         * @param string $id
         * @return mixed
         */
        public static function find(string $userUUID, string $id)
        {
            $games = Cache::get("games:$userUUID", collect());
            return $games->first(function ($item) use ($id) {
                return $item->id === $id;
            });
        }

        /**
         * Borra todos los juegos de un usuario o uno en particular
         * @param string $userUUID
         * @param string|null $id
         * @return bool
         */
        public static function delete(string $userUUID, string $id = null)
        {
            $games = Cache::get("games:$userUUID", collect());
            if ($id) {
                $games = $games->filter(function ($game) use ($id) {
                    return $game->id !== $id;
                });
                return Cache::put("games:$userUUID", $games);
            } else {
                return Cache::forget("games:$userUUID");
            }
        }


        public function addRound(string $userActionCode, string $machineActionCode, string $winnerUUID)
        {
            $round = new Round(
                [
                    'gameId' => $this->id,
                    'roundNumber' => $this->roundNumber,
                    'userActionCode' => $userActionCode,
                    'machineActionCode' => $machineActionCode,
                    'winnerUUID' => $winnerUUID
                ]
            );
            $this->rounds[] = $round;
            $this->roundNumber++;
        }

    }
