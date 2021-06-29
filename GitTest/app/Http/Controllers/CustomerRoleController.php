<?php
/**
 * 担当区分のコントローラー
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use DB;
use App\CustomerRole;
use Session;

/**
 * 担当区分
 */
class CustomerRoleController extends \App\Http\Controllers\Controller
{
    public function show()
    {
        $model = CustomerRole::all();
        return $model;
    }
}