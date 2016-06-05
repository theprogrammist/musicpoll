<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiCoveringTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public static function getTestVote()
    {
        return [
            'genre' => 1,
            'alias' => 'kot begemot',
            'api_token' => CreateUserTest::$testUser['apitoken']
        ];
    }

    public function testUnimplementedActions()
    {
        $response = $this->call('GET', '/api/v1/vote/create?' . http_build_query(self::getTestVote()));
        $this->assertEquals(405, $response->getStatusCode());

        $response = $this->call('GET', '/api/v1/vote/1?' . http_build_query(self::getTestVote()));
        $this->assertEquals(405, $response->getStatusCode());

        $response = $this->call('PUT', '/api/v1/vote/1?' . http_build_query(self::getTestVote()));
        $this->assertEquals(405, $response->getStatusCode());

        $response = $this->call('PATCH', '/api/v1/vote/1?' . http_build_query(self::getTestVote()));
        $this->assertEquals(405, $response->getStatusCode());

        $response = $this->call('DELETE', '/api/v1/vote/1?' . http_build_query(self::getTestVote()));
        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testCreateTestUser()
    {
        $this->deleteTestUser();

        //create test user again
        $response = $this->call('GET', '/auth?' . http_build_query(CreateUserTest::$testUser));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('New user created.', json_decode($response->getContent())->message);
    }
    public function testAuthenticatedAccess()
    {
        $testVote = self::getTestVote();
        $testVote['api_token'] = str_random(60);
        $response = $this->call('POST', '/api/v1/vote?' . http_build_query($testVote));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testDidNotVote()
    {
        $this->deleteTestUserVote();

        $response = $this->call('GET', '/api/v1/vote?' . http_build_query(self::getTestVote()));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testVote()
    {
        $response = $this->call('POST', '/api/v1/vote?' . http_build_query(self::getTestVote()));
        $this->assertEquals(200, $response->getStatusCode());
        $currentGenreId = self::getTestVote()['genre'];
        $this->assertTrue(isset(json_decode($response->getContent())->rates->$currentGenreId));
        $this->assertTrue(json_decode($response->getContent())->rates->$currentGenreId->percentage > 0);
    }

    public function testRepetedVote()
    {
        $response = $this->call('POST', '/api/v1/vote?' . http_build_query(self::getTestVote()));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testAlreadyVoted()
    {
        $response = $this->call('GET', '/api/v1/vote?' . http_build_query(self::getTestVote()));
        $this->assertEquals(200, $response->getStatusCode());
        $currentGenreId = self::getTestVote()['genre'];
        $this->assertTrue(isset(json_decode($response->getContent())->rates->$currentGenreId));
        $this->assertTrue(json_decode($response->getContent())->rates->$currentGenreId->percentage > 0);

        $this->deleteTestUserVote();
        $this->deleteTestUser();
    }

    public function deleteTestUser() {
        $database = $this->app->make('db');
        $connection = $database->getDefaultConnection();
        $database->connection($connection)->table('users')->where(['email' => CreateUserTest::$testUser['email']])->delete();
    }

    public function deleteTestUserVote() {
        $database = $this->app->make('db');
        $connection = $database->getDefaultConnection();
        $userId = $database->connection($connection)->table('users')->where(['email' => CreateUserTest::$testUser['email']])->first()->id;

        $database->connection($connection)->table('votes')->where(
            ['user_id' => $userId,
            'genre_id'=>self::getTestVote()['genre']])->delete();
    }
}
