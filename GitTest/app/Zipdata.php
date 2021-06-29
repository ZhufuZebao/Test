<?php
/**
 * 郵便番号テーブル
 *
 * @author  Reiko Mori
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Zipdata extends Model
{
    protected $table = "zipdatas";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'zipcode','state','city','town',
    ];

    public function pref()
    {
        return $this->hasOne('App\Pref', 'name', 'state');
    }

    public static function zip2addr($zipcode)
    {
        $model = self::where('zipcode', '=', $zipcode)->first();

        if(! $model)
            return [
                'pref_id' => null,
                'city'    => null,
                'town'    => null,
                'street'  => null,
            ];

        return [
            'pref_id' => $model->pref->id,//array_get($model,'pref.id'),
            'city'    => $model->city,
            'town'    => $model->town,
            'street'  => "{$model->city}{$model->town}",
        ];
    }

}
