<?php

    namespace App\Models;

    class GameState
    {
        public string $code;
        public string $name;

        public function __construct($code, $name)
        {
            $this->code = $code;
            $this->name = $name;
        }
    }
