<?php
/**
 * 下請け業者のテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;
use Illuminate\Support\Facades\Validator;

class Contractor extends Model
{
    protected $fillable = [
        'name', 'zip', 'pref', 'addr', 'establishment', 'representative', 'contents',
    ];

    public function projects()
    {
        return $this->hasMany('App\Project', 'contractor_id', 'id');
    }

    /**
     * 全ての下請け業者を取得
     *
     * @return  array   取得した下請け業者一覧
     */
    static function get()
    {
        $prefs = DB::table('contractors')
        ->select('*')
        ->get();

        foreach ($prefs as $key => $item) {
            $rows[$item->id] = $item->name;
        }

        return $rows;
    }

    /**
     * 指定された下請け業者の情報を取得
     *
     * @param   int     $id     下請け業者ID
     * @return  array   取得した下請け業者の情報
     */
    static function getContractor($id)
    {
        $sql  = "select *, \n";
        $sql .= "date_format(establishment, '%Y') as establishment_y, \n";
        $sql .= "date_format(establishment, '%m') as establishment_m \n";
        $sql .= "from contractors \n";
        $sql .= "where id = ?";

        $data = DB::select($sql, [$id]);

        return $data;
    }

    public function prefecture()
    {
        return $this->hasOne('App\Pref', 'id', 'pref');
    }

    public function validate()
    {
        $rules = [
            'name'          => ['required', 'string', 'max:128'],
            'zip'           => ['required', 'string', 'max:8'],
            'pref'          => ['required', 'exists:prefs,id'],
            'addr'          => ['required', 'string', 'max:190'],
            'establishment' => ['nullable', 'date'],
            'representative'=> ['nullable', 'string', 'max:100'],
            'contents'      => ['nullable', 'string'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }
}
