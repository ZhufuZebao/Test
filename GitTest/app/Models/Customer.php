<?php
/**
 * @author  liYanlin
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $table = "customers";
    public $primaryKey = "id";
    public $keyword;
    protected $guarded = ['id'];

    protected $casts = [
    ];

    public function customerOffice()
    {
        return $this->hasMany('App\Models\CustomerOffice','customer_id','id');
    }

    public function offices()
    {
        return $this->hasMany('App\Models\CustomerOffice', 'customer_id', 'id');
    }

    public function projectOffice()
    {
        return $this->belongsToMany('App\Models\CustomerOffice', 'projects_customers','customer_id',
            'office_id')->select('customer_offices.id','customer_offices.name');
    }

    /**
     * 更新: 直前にChangeLogへ記録する
     */
    public function update(array $attributes = [], array $options = [])
    {
        event(new \App\Events\EditedByUser($this));

        parent::update($attributes, $options);
    }

    public function validate()
    {

        $rules = [
            'name' => ['required', 'max:256'],
            'phonetic' => ['required', 'max:256'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    public static function boot()
    {
        parent::boot();
        // 施主を削除したら、事業所も削除
        static::deleted(function ($customer) {
            error_log(print_r('-----customer_deleted-----', true));
            foreach ($customer->offices as $office) {
                $office->delete();
            }
        });
    }

    public function init($params)
    {
        $this->keyword = $params;
    }

    public function search()
    {
        $q = Customer::with("CustomerOffice");

        if($this->keyword)
        {
            $q->where('name',    'LIKE', "%{$this->keyword}%");
        }
        return $q->get();
    }

}
