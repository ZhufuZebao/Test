<?php

namespace Tests\Unit;

use App\Http\Facades\Common;
use App\Models\Report;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        print_r(trans('messages.error.system'));
        $this->assertTrue(true);
    }

    public function testValidate()
    {
        $model = new Report();
        $v = $model->validate();
        print_r($v->errors());
        $this->assertTrue($v->fails());
    }
}
