<?php
/**
 * 教えてサイトの質問＆回答テーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;

class Faq extends Model
{
    /**
     * 教えてサイトのデータを取得
     *
     * @param   int     $category_id    カテゴリーID（省略時は全件）
     * @param   int     $id             質問ID（省略時は全件）
     * @return  array   取得したデータ
     */
    static function get($category_id='', $id='')
    {
        $where = '';
        $params = array();

        if ($category_id != '') {
            $where = "and category_id = ?\n";
            $params[] = $category_id;
        }

        if ($id != '') {
            $where = "and id = ?\n";
            $params[] = $id;
        }

        $sql  = <<< EOF
select *,
   case when date_format(post_date, '%Y%m%d') = date_format(sysdate(), '%Y%m%d')
       then date_format(post_date, '%H:%i')
       else date_format(post_date, '%Y/%m/%d %H:%i')
   end as disp_dt
from faqs
where sub_id = 0
{$where}
order by post_date desc
EOF;


        $data = DB::select($sql, $params);

        return $data;
    }

    /**
     * 解答を登録する
     *
     * @param   object  $faqs       faqsテーブルのオブジェクト
     * @param   int     $user_id    ユーザーID
     * @param   string  $comment    回答
     */
    static function set($faqs, $user_id, $comment)
    {
        $sql = <<< EOF
insert into faqs (
    category_id,
    post_id,
    sub_id,
    contractor_id,
    user_id,
    title,
    comment,
    post_date
) values (
    ?,
    ?,
    (select max(ifnull(f.sub_id, 0)) + 1 from faqs f where f.post_id = ?),
    null,
    ?,
    ?,
    ?,
    sysdate()
)
EOF;

        $params = [
                $faqs->category_id,
                $faqs->post_id,
                $faqs->post_id,
                $user_id,
                $faqs->title,
                $comment
        ];

        DB::insert($sql, $params);
    }
}
