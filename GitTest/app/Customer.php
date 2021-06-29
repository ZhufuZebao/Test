<?php
/**
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $table = "customers";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'name',
        'phonetic',
    ];

    public function offices()
    {
        return $this->hasMany('App\CustomerOffice', 'customer_id', 'id');
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
            'name'      => ['required', 'max:256', 'unique'],
            'phonetic'      => ['required', 'max:256', 'unique'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }

    public static function boot()
    {
        parent::boot();
        // 施主を削除したら、事業所も削除
        static::deleted(function ($customer) {
            error_log(print_r('-----customer_deleted-----',true));
            foreach($customer->offices as $office) {
                $office->delete();
            }
        });
    }

}
