<?php

namespace Tests\Feature\Controller\Web;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetReportList()
    {
        $this->signIn();
        $response = $this->get('/api/getReportList');
        $response->assertOk();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSetReport()
    {
        $this->signIn();
        $data = ["report" => ["title" => "aaa"]];
        $response = $this->post('api/setReport', $data);
        print_r($response->content());
        print_r($response->decodeResponseJson());
        $response->assertJson(["errors" => ["title" => ['titleは必須です。']]]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetReportDetail()
    {
        $this->signIn();
        $data = ["id" => 11111];
        $response = $this->post('api/getReportDetail', $data);
        print_r($response->content());
        $response->assertOk();
    }
}
