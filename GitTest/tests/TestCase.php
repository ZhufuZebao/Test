<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    /**
     * テスト用登録
     * @param String $email
     */
    protected function signIn(String $email = "user1@test.com")
    {
        $user = $email
            ? (User::where("email", $email)->first() ?? factory(User::class)->create(["email" => $email]))
            : factory(User::class)->create();
        $this->actingAs($user);
    }
}
