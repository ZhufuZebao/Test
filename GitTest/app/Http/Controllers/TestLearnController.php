<?php
namespace App\Http\Controllers;
use App\Models\TestLearn;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class TestLearnController extends Controller {
    public function createTestLearn(Request $request) {
        $checkResult = Validator::make($request->all(),[
            'name' => 'regex:/^[a-zA-Z]{1,30}$/',
            'mobile' => 'regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'email' => 'email|max:30',
        ]);
        if ($checkResult->fails()) {
            return [
                'status' => 'exception',
                'code' => 400,
                'message' => $checkResult->errors()
            ];
        }else{
            $datas = new TestLearn();
            $datas->name = $request->get('name');
            $datas->mobile = $request->get('mobile');
            $datas->email = $request->get('email');
            try {
                if($datas->save()){
                    return [
                        'status' => 'success',
                    ];
                }

            }catch (\Exception $e){
                Log::error($e->getMessage());
            }

            return [
                'status' => 'error',
            ];
        }


//
//            $checkResult = $request->validate([
//                'name' => 'regex:/^[a-zA-Z]{1,30}$/',
//                'mobile' => 'regex:/^[1][3,4,5,7,8][0-9]{9}$/',
//                'email' => 'email|max:30',
//            ]);
//            return $checkResult;


//        }
    }
    public function selectTestLearn(){
        $data = TestLearn::all();
        return $data;
    }
}