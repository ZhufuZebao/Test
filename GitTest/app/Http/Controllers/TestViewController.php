<?php
    namespace App\Http\Controllers;
    use App\Models\TestView;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class TestViewController extends Controller{
        public function testViewAdd(Request $request){
           $dataAdd = new TestView();
           $dataAdd->user = $request->get('user');
           $dataAdd->sex = $request->get('sex');
           $dataAdd->like = $request->get('like');
           if($dataAdd->save()){
               return 'success';
           }else{
               return 'error';
           }
        }
        public function testViewSelect(){
            $usersSelect = DB::table('testview')->get();

            return $usersSelect;
        }
        public function testViewDelete(Request $request){
            $delId = $request->get('delID');
            $test = TestView::where('id',$delId)->delete();
            if($test){
                return 'success';
            }else{
                return 'error';
            }
        }
        public function testViewUpdateSelect(Request $request){
            $updId = $request->get('updId');
            //$updSelect = TestView::where('id',$updId)->get();
            $updSelect = DB::table('testview')->where('id',$updId)->get();

            return $updSelect;

        }
        public function testViewUpdate(Request $request){
            $updateId = $request->get('update');
            $updateUser = $request->get('updateUser');
            $updateSex = $request->get('updateSex');
            $updateLike = $request->get('updateLike');
            $update = DB::table('testview')->where('id',$updateId)
                ->update(
                    ['user' => $updateUser,'sex' => $updateSex,'like' => $updateLike]

                );
            if($update){
                return 'success';
            }else{
                return 'error';
            }
        }
    }