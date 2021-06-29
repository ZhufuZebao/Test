<?php
/**
 * 工程管理ページのコントローラー
 *
 * @author  Miyamoto
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use App\Estate;
use App\Http\Requests\EstateFormRequest;

use Session;

/**
 * 物件管理
 */
class EstateController extends \App\Http\Controllers\Controller
{
    /**
     * 工程管理のトップページ
     * method: GET
     */
    public function index()
    {
        $models = Estate::where('id', '>=', 1)->paginate(10);

        return view('/estate/index', [
            'models' => $models,
        ]);
    }

    public function create()
    {
        $model = new Estate();

        return view('/estate/edit', [
            'model' => $model,
        ]);
    }

    public function edit($id)
    {
        $model = $this->findModel($id);

        return view('/estate/edit', [
            'model' => $model,
        ]);
    }

    public function job_create($id)
    {
        $model = new \App\EstateJob();
        $model->estate_id = $id;

        return view('/estate/job-edit', [
            'model' => $model,
        ]);
    }

    public function job_edit($id)
    {
        $model = \App\EstateJob::find($id);

        return view('/estate/job-edit', [
            'model' => $model,
        ]);
    }

    public function job_store($id, Request $request)
    {
        $model = new \App\EstateJob();
        $model->estate_id = $id;
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($model->save())
        {
            return redirect()->route('estate.show',['id'=>$id]);
        }
        
        return view('/estate/job-edit', [
            'model' => $model,
        ]);
    }

    public function job_update($id, Request $request)
    {
        $model = \App\EstateHospital::find($id);
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($model->save())
        {
            return redirect()->route('estate.show',['id'=>$model->estate_id]);
        }
        
        return view('/estate/job-edit', [
            'model' => $model,
        ]);
    }

    public function hospital_create($id)
    {
        $model = new \App\EstateHospital();
        $model->estate_id = $id;

        return view('/estate/hospital-edit', [
            'model' => $model,
        ]);
    }

    public function hospital_edit($id)
    {
        $model = \App\EstateHospital::find($id);

        return view('/estate/hospital-edit', [
            'model' => $model,
        ]);
    }

    public function hospital_store($id, Request $request)
    {
        $model = new \App\EstateHospital();
        $model->estate_id = $id;
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($model->save())
        {
            return redirect()->route('estate.show',['id'=>$id]);
        }
        
        return view('/estate/hospital-edit', [
            'model' => $model,
        ]);
    }

    public function hospital_update($id, Request $request)
    {
        $model = \App\EstateHospital::find($id);
        $model->user_id = Auth::user()->id;
        $model->fill($request->all());

        if($model->save())
        {
            return redirect()->route('estate.show',['id'=>$model->estate_id]);
        }
        
        return view('/estate/hospital-edit', [
            'model' => $model,
        ]);
    }

    public function show($id)
    {
        $model = $this->findModel($id);

        return view('/estate/show', [
            'model' => $model,
        ]);
    }

    private static function zip2addr($zip, $attr)
    {
        $params = \App\Zipdata::zip2addr($zip);
        return [
            $attr.'pref_id' => array_get($params,'pref_id'),
            $attr.'town'    => array_get($params,'city').array_get($params,'town'),
        ];
    }

    public function store(EstateFormRequest $request)
    {
        $model = new Estate();
        $model->fill($request->all());
        $model->contractor_id = \App\Contractor::first()->id; // TBD, seek by user_id

        if($attr = $request->get('zip2addr'))
        {
            $zip = $request->get($attr.'zip'); // location_zip, maintainer_zip or realtor_zip

            $params = self::zip2addr($zip, $attr);
            $model->fill($params);
        }
        elseif($model->save())
        {
            return redirect()->route('estate.show',['id'=>$model->id]);
        }
        
        return view('/estate/edit', [
            'model' => $model,
        ]);
    }

    public function update($id, EstateFormRequest $request)
    {
        $model = $this->findModel($id);
        $model->fill($request->all());

        if($attr = $request->get('zip2addr'))
        {
            $zip = $request->get($attr.'zip'); // location_zip, maintainer_zip or realtor_zip

            $params = self::zip2addr($zip, $attr);
            $model->fill($params);
        }
        elseif($model->save())
        {
            return redirect()->route('estate.show',['id'=>$model->id]);
        }
        
        return view('/estate/edit', [
            'model' => $model,
        ]);
    }

    /**
     * Estate Model を返す
     * 
     * var $id integer
     * @return Model | 404 Not found
     */
    private function findModel($id)
    {
        $model = Estate::find($id);

        if(! $model)
            abort(404, "見つかりません");

        return $model;
    }

}
