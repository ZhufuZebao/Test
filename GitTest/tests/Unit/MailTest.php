<?php

namespace Tests\Unit;

use App\Mail\AdminAuthKeySend;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $to = 'zhiqiang.song1@pactera.com';
        $auth_key = Str::random(32);
        Mail::to($to)->send(new AdminAuthKeySend($auth_key));
        $this->assertTrue(count(Mail::failures()) == 0);
    }
}
