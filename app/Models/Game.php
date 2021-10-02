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

        /**
         * @var string Identificador del juego
         */
        public $id;

        /**
         * @var int Número de rondas del juego
         */
        public $roundNumber;

        /**
         * @var int Número de rondas total del juego
         */
        public $numberOrRounds;

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
         * @var int Registro de cada ronda
         */
        public $rounds;

        /**
         * @var string Código del estado
         */
        public $stateCode;

        /**
         * @var string Código UUID del ganador del juego si es humano
         */
        public $winnerUUID;

        /**
         * @var string Código del resultado de la partida respecto al jugador
         */
        public $resultCode;


        public function __construct(string $modeCode, int $numberOrRounds, string $userUUID)
        {
            $this->id = Str::uuid()->toString();
            $this->roundNumber = 1;
            $this->userUUID = $userUUID;
            $this->modeCode = $modeCode;
            $this->numberOrRounds = $numberOrRounds;
            $this->stateCode = Game::STATES[0]['code'];
            $this->createdAt = Carbon::now();
        }

        /**
         * Crea un nuevo juego y devuelve su información
         * @param string $modeCode
         * @param int $numberOrRounds
         * @param string $userUUID
         * @return Game
         */
        public static function create(string $modeCode, int $numberOrRounds, string $userUUID)
        {
            $games = Cache::get("games:$userUUID", collect());
            $game = new Game($modeCode, $numberOrRounds, $userUUID);
            $games->push($game);
            Cache::put("games:$userUUID", $games);
            return $game;
        }

        /**
         * Obtiene un juego en específico a partir de su id
         * @param string $userUUID
         * @param string $id
         * @return Game
         */
        public static function find(string $userUUID, string $id): Game
        {
            $games = Cache::get("games:$userUUID", collect());
            return $games->first(function ($item) use ($id) {
                return $item->id === $id;
            });
        }

        /**
         * Persiste los cambios sucedidos en el juego
         * @param Game $game
         * @return bool
         */
        public static function update(Game $game): bool
        {
            $games = Cache::get("games:{$game->userUUID}", collect());
            $key = array_search($game->id, $games->pluck('id')->toArray());
            if ($key !== false) {
                $games[$key] = $game;
            }
            return Cache::put("games:{$game->userUUID}", $games);
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


        /**
         * Añade una ronda al juego pasado en la colección del parámetro
         * @param array $attrs
         * @return $this
         * @throws \Exception
         */
        public function addRound(array $attrs): Game
        {
            if ($this->numberOrRounds <= ($this->rounds ?? collect())->count() || $this->stateCode === 'FINISHED') {
                throw new \Exception('game_is_completed');
            }

            // Creamos la nueva ronda y la añadimos
            $round = new Round(
                [
                    'gameId' => $this->id,
                    'roundNumber' => $this->roundNumber,
                    'userActionCode' => $attrs['userActionCode'],
                    'machineActionCode' => $attrs['machineActionCode'],
                    'winnerUUID' => $attrs['winnerUUID']
                ]
            );
            if (!$this->rounds) {
                $this->rounds = collect();
            }
            $this->rounds->push($round);

            // Si se han jugado todas las rondas, se da el juego por finalizado
            if ($this->rounds->count() >= $this->numberOrRounds) {
                $this->stateCode = 'FINISHED';

                // Si el jugador ha ganado más veces, se declara vencedor de la partida
                $wins = $this->rounds->where('winnerUUID', $this->userUUID)->count();
                if ($wins > ($this->numberOrRounds / 2)) {
                    $this->winnerUUID = $this->userUUID;
                    $this->resultCode = 'WIN';
                } else {
                    $this->resultCode = 'LOOSE';
                }
            } else {
                $this->roundNumber++;
            }
            if (!self::update($this)) {
                throw new \Exception('error_ocurred');
            };
            return $this;
        }

    }
