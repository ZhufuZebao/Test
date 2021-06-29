<?php
/**
 *ドキュメント
 */

namespace App\Http\Controllers\Web;


use App\Models\Enterprise;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class DocumentController extends \App\Http\Controllers\Controller
{
    private $pagination = 10;

    protected function getPagesize($default = 30)
    {
        return Input::get('pagesize', $default);
    }

    public function getDocumentList(){
        $pro = Project::whereHas('projectParticipant',function ($q){
            $q->where('user_id',Auth::id());
        })->whereHas('enterprise',function ($q){
            $q->where('enterprise_id',Auth::user()->enterprise_id);
        })->paginate($this->getPagesize($this->pagination));
        $enterprise = Enterprise::where('id',Auth::user()->enterprise_id)->get('name');
        return ['pro'=>$pro,'enterprise'=>$enterprise];
    }
}
