<?php

    namespace App\Models;

    use Carbon\Carbon;

    class Round
    {
        public $gameId;
        public $roundNumber;
        public $userActionCode;
        public $machineActionCode;
        public $resultCode;
        public $createdAt;

        public function __construct(array $attrs)
        {
            foreach ($attrs as $key => $value) {
                if (property_exists(self::class, $key)) {
                    $this->$key = $value;
                }
            }
            $this->createdAt = Carbon::now();
        }
    }
