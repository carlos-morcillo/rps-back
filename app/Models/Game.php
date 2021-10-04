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
            [
                'code' => 'ROCK',
                'name' => 'piedra',
                'image' => 'far fa-hand-rock',
                'strongAgainst' => ['SCISSORS', 'LIZARD']
            ],
            [
                'code' => 'PAPER',
                'name' => 'papel',
                'image' => 'far fa-hand-paper',
                'strongAgainst' => ['ROCK', 'SPOCK']
            ],
            [
                'code' => 'SCISSORS',
                'name' => 'tijeras',
                'image' => 'far fa-hand-scissors',
                'strongAgainst' => ['PAPER', 'LIZARD']
            ],
            [
                'code' => 'LIZARD',
                'name' => 'lagarto',
                'image' => 'far fa-hand-lizard',
                'strongAgainst' => ['PAPER', 'SPOCK']
            ],
            [
                'code' => 'SPOCK',
                'name' => 'spock',
                'image' => 'far fa-hand-spock',
                'strongAgainst' => ['ROCK', 'SCISSORS']
            ],
        ];

        const MODES = [
            [
                'code' => 'TRADITIONAL',
                'name' => 'Tradicional',
                'allowedActionCodes' => ['ROCK', 'PAPER', 'SCISSORS']
            ],
            [
                'code' => 'BIG_BANG',
                'name' => '+ Lagarto y Spock',
                'allowedActionCodes' => ['ROCK', 'PAPER', 'SCISSORS', 'LIZARD', 'SPOCK']
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
        public $numberOfRounds;

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


        public function __construct(string $modeCode, int $numberOfRounds, string $userUUID)
        {
            $this->id = Str::uuid()->toString();
            $this->roundNumber = 1;
            $this->userUUID = $userUUID;
            $this->modeCode = $modeCode;
            $this->numberOfRounds = $numberOfRounds;
            $this->stateCode = Game::STATES[0]['code'];
            $this->createdAt = Carbon::now();
        }

        /**
         * Crea un nuevo juego y devuelve su información
         * @param string $modeCode
         * @param int $numberOfRounds
         * @param string $userUUID
         * @return Game
         */
        public static function create(string $modeCode, int $numberOfRounds, string $userUUID)
        {
            $games = Cache::get("games:$userUUID", collect());
            $game = new Game($modeCode, $numberOfRounds, $userUUID);
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
        public static function find(string $userUUID, string $id)
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
            if ($this->numberOfRounds <= ($this->rounds ?? collect())->count() || $this->stateCode === 'FINISHED') {
                throw new \Exception('game_is_completed');
            }

            // Creamos la nueva ronda y la añadimos
            $round = new Round(
                [
                    'gameId' => $this->id,
                    'roundNumber' => $this->roundNumber,
                    'userActionCode' => $attrs['userActionCode'],
                    'machineActionCode' => $attrs['machineActionCode'],
                    'resultCode' => $attrs['resultCode']
                ]
            );
            if (!$this->rounds) {
                $this->rounds = collect();
            }
            $this->rounds->push($round);

            // Si se han jugado todas las rondas, se da el juego por finalizado
            if ($this->rounds->count() >= $this->numberOfRounds) {
                $this->stateCode = 'FINISHED';

                // Si el jugador ha ganado más veces, se declara vencedor de la partida
                $victories = $this->rounds->where('resultCode', 'VICTORY')->count();
                if ($victories > ($this->numberOfRounds / 2)) {
                    $this->winnerUUID = $this->userUUID;
                    $this->resultCode = 'VICTORY';
                } else {
                    $this->resultCode = 'DEFEAT';
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
