<?php
/**
 * プロジェクトテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;

class Project extends Model
{
    /**
     * storage path of photo
     */
    public const PHOTO_PATH = 'photo/project';

    protected $fillable = [
        'name',
        'st_date',
        'ed_date',
        'contractor_id',
    ];

    public static function rules()
    {
        return [
            'name'    => ['required', 'string'],
            'st_date' => ['required', 'date'],
            'ed_date' => ['required', 'date'],
            'contractor_id' => ['required', 'exists:contractors,id'],
        ];
    }

    /**
     * 下請け会社
     */
    public function contractor()
    {
        return $this->hasOne('App\Contractor', 'id', 'contractor_id');
    }

    /**
     * 計画工程表
     */
    public function schemes()
    {
        return $this->hasMany(\App\Scheme::class, 'project_id', 'id')
                    ->where('parent_id', 0)
                    ->orWhere('parent_id', null)
                    ->orderByRaw('weight DESC, id ASC');
    }

    /**
     * 実施工程表
     */
    public function tasks()
    {
        return $this->hasMany(\App\Task::class, 'project_id', 'id')
                    ->where('parent_id', 0)
                    ->orWhere('parent_id', null)
                    ->orderByRaw('weight DESC, id ASC');
    }

    /**
     * 引数に応じてプロジェクトを検索する
     *
     * @param   string  $name           プロジェクト名
     * @param   int     $contractor     下請け会社ID
     * @param   string  $st_date        開始日
     * @param   string  $ed_date        完了予定日
     * @param   string  $manager        責任者
     * @param   string  $author         作成者
     * @param   string  $create_date    作成日
     * @return  array   取得したデータ
     */
    static function get($name, $contractor, $st_date, $ed_date, $manager, $author, $create_date)
    {
        $sql = "select * from projects\n";

        $where = '';
        $params = array();

        if ($name != '') {
            $where .= ($where != '' ? 'and ': 'where '). "name like ?\n";
            $params[] = '%'. $name. '%';
        }
        if ($contractor != '') {
            $where .= ($where != '' ? 'and ': 'where '). "contractor_id = ?\n";
            $params[] = $contractor;
        }
        if ($st_date != '') {
            $where .= ($where != '' ? 'and ': 'where '). "st_date >= ?\n";
            $params[] = $st_date;
        }
        if ($ed_date != '') {
            $where .= ($where != '' ? 'and ': 'where '). "ed_date <= ?\n";
            $params[] = $ed_date;
        }
        if ($manager != '') {
            $where .= ($where != '' ? 'and ': 'where '). "manager like ?\n";
            $params[] = '%'. $manager. '%';
        }
        if ($author != '') {
            $where .= ($where != '' ? 'and ': 'where '). "author like ?\n";
            $params[] = '%'. $author. '%';
        }
        if ($create_date != '') {
            $where .= ($where != '' ? 'and ': 'where '). "create_date = ?\n";
            $params[] = $create_date;
        }

        $sql .= $where;
//echo $sql;
//print_r($params);

        $projects = DB::select($sql, $params);
//print_r($projects);
//exit();

        return $projects;
    }

    /**
     * 引数のプロジェクトIDのデータを取得
     *
     * @param   int     $id     プロジェクトID
     * @return  array   取得したデータ
     */
    static function getProject($id)
    {
        $sql  = "select p.*, c.name as contractor_name \n";
        $sql .= "from projects p \n";
        $sql .= "   inner join contractors c \n";
        $sql .= "       on p.contractor_id = c.id \n";
        $sql .= "where p.id = ?\n";

        $data = DB::select($sql, [$id]);

        return $data;
    }

    /**
     * プロジェクトテーブルにデータを登録する
     *
     * @param   string  $name           プロジェクト名
     * @param   int     $contractor     下請け会社ID
     * @param   string  $st_date        開始日
     * @param   string  $ed_date        完了予定日
     * @param   string  $manager        責任者
     * @param   string  $author         作成者
     * @param   string  $create_date    作成日
     * @return  取得したデータ
     */
    static function set($name, $contractor, $st_date, $ed_date, $manager, $author, $create_date)
    {
        $data = self::get($name, $contractor, $st_date, $ed_date, $manager, $author, $create_date);
//print_r($data);
        if (!empty($data)) {
//echo 'koko';
            return $data;
        }
        $sql  = "insert into projects (\n";
        $sql .= "name, contractor_id, st_date, ed_date, manager, author, create_date, created_at\n";
        $sql .= ") values (";
        $sql .= "?, ?, ?, ?, ?, ?, ?, sysdate()\n";
        $sql .= ")";

        $params = [$name, $contractor, $st_date, $ed_date, $manager, $author, $create_date];
//echo $sql;
//exit();

        $ret = DB::insert($sql, $params);
    }
}
