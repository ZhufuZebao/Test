<?php
/**
 * 評価テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;

class Evaluation extends Model
{
    /**
     * 評価データを取得
     *
     * @param   int     $user_id        ユーザーID
     * @param   int     $project_id     プロジェクトID
     * @return  array   取得した評価データ
     */
    static function get($user_id, $project_id='')
    {
        $params = array($user_id);

        $where = '';
        if ($project_id != '') {
            $where = 'and project_id = ?';
            $params[] = $project_id;
        }

        $sql  = "select * \n";
        $sql .= "from evaluations \n";
        $sql .= "where user_id = ? $where";

        $data = DB::select($sql, $params);

        $rows = null;

        if (is_array($data)) {
            foreach ($data as $items) {
                $rows[$items->project_id] = $items;
            }
        }

        return $rows;
    }

    /**
     * 評価データをデータベースに登録
     *
     * @param   int     $user_id        ユーザーID
     * @param   int     $project_id     プロジェクトID
     * @param   int     $satisfaction   満足度
     * @param   string  $comment        本文
     */
    static function set($user_id, $project_id, $satisfaction, $comment)
    {
        $data = self::get($user_id, $project_id);

        if (is_array($data)) {
            $sql = "update evaluations set satisfaction = ?, comment = ? where user_id = ? and project_id = ?";

            $ret = DB::update($sql, [$satisfaction, $comment, $user_id, $project_id]);

        } else {

            $sql  = "insert into evaluations (user_id, project_id, satisfaction, comment)";
            $sql .= " values (?, ?, ?, ?)";

            $ret = DB::insert($sql, [$user_id, $project_id, $satisfaction, $comment]);
        }
    }
}
