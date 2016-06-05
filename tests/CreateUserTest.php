<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateUserTest extends TestCase
{
    public static $testUser = [
        'email' => 'user-for-unit-tests@test.ru',
        'password' => 'test123',
        'name' => 'user-for-unit-tests',
        'apitoken' => '1234567890qwertyuiop-u4ut'];

    public function testCreateUser()
    {
        $this->deleteTestUser();

        $response = $this->call('GET', '/auth?' . http_build_query(self::$testUser));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReCreateExistingUser()
    {
        $response = $this->call('GET', '/auth?' . http_build_query(self::$testUser));

        $this->assertEquals(400, $response->getStatusCode());

        $this->deleteTestUser();
    }

    public function deleteTestUser() {
        $database = $this->app->make('db');
        $connection = $database->getDefaultConnection();
        $database->connection($connection)->table('users')->where(['email' => CreateUserTest::$testUser['email']])->delete();
    }
}
