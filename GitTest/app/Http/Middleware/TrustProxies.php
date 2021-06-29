<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * このアプリケーションで信用するプロキシ
     *
     * @var array
     */
    protected $proxies = '*';

    /**
     * プロキシを検出するために使用するヘッダ
     *
     * @var string
     */
    protected $headers = Request::HEADER_X_FORWARDED_AWS_ELB;
}