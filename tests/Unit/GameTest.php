<?php

    namespace Tests\Unit;

    use App\Http\Controllers\GamesController;
    use App\Models\Game;
    use Illuminate\Support\Str;

    class GameTest extends \TestCase
    {

        /**
         * Test para preferencias
         *
         * @return void
         */
        public function test_settings_test()
        {
            $response = $this->get('/settings')->response;
            $this->assertEquals(200, $response->status());
            $this->assertCount(3, (array)$response);
            $this->assertCount(2, $response['states']);
            $this->assertIsObject($response);
        }

        /**
         * Test para preferencias
         *
         * @return void
         */
        public function test_create_game_test()
        {
            $uuid = Str::uuid()->toString();
            $mode = 'TRADITIONAL';

            $response = $this->put("/games/$uuid/$mode")->response;
            $this->assertIsObject($response);
        }

        /**
         * Test de borrado en modelo
         *
         * @return void
         */
        public function test_delete_game_in_model_test()
        {
            $uuid = Str::uuid()->toString();
            $result = Game::delete($uuid);
            $this->assertIsBool($result);
        }

        /**
         * Test de creación y borrado de juego en controlador
         *
         * @return void
         */
        public function test_create_and_delete_game_in_controller_test()
        {
            $uuid = Str::uuid()->toString();

            $gamesCtrl = new GamesController();
            $game = $gamesCtrl->create($uuid)->getData();

            $this->assertIsString($game->id);
            $this->assertNull($game->rounds);

            $result = $gamesCtrl->delete($uuid, $game->id)->getData();
            $this->assertTrue($result);
        }
    }
