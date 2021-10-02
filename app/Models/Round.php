<?php

    namespace App\Models;

    class Round
    {
        public int $gameId;
        public int $roundNumber;
        public string $userActionCode;
        public string $machineActionCode;
        public string $winnerUUID;
        public string $createdAt;

        public function __construct(array $attrs)
        {
            foreach ($attrs as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
        }
    }
