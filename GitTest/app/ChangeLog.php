<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class ChangeLog extends \Illuminate\Database\Eloquent\Model
{
    protected $table      = "change_logs";
    public    $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
    ];

    public function feed($model)
    {
        // model
        $this->tbl_name = $model->getTable();
        $this->tbl_id   = $model->id;
        $this->before   = serialize($model->getOriginal());
        $this->after    = serialize($model->getDirty());

        // meta data
        $this->user_id  = \Auth::user()->id;
        $this->ip       = \Request::ip();
        $this->url      = \URL::full();
    }

    public function getAfter()
    {
        return unserialize($this->after);
    }

    public function getBefore()
    {
        return unserialize($this->before);
    }

    /**
     * 現在の当該レコードの値
     */
    public function getCurrent()
    {
        return \DB::table($this->tbl_name)->find($this->tbl_id);
    }

    public function getDiff()
    {
        $diff = [];
        $orig = $this->getBefore();

        foreach($this->getAfter() as $key => $value)
        {
            $diff[$key] = [
                $orig[$key] ?? null,
                $value,
            ];
        }

        return $diff;
    }

    public function validate()
    {
        
        $rules = [
            'user_id'  => ['required', 'exists:users,id'],
            'original' => ['nullable'],
            'changed'  => ['required'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

}
