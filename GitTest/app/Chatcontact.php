<?php
/**
 * チャット・コンタクトのモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatcontact extends Model
{
    /**
     * chatcontactテーブルに新規登録する
     *
     * @param   int     $from_user_id   コンタクト送信者のユーザーID
     * @param   int     $to_user_id     コンタクト受信者のユーザーID
     * @param   string  $message        メッセージ
     * @param   string  $email          メールアドレス
     */
    static function set($from_user_id, $to_user_id, $message, $email=null)
    {
        $already = null;
        $where = '';

        if ($email != null) {
            $already = DB::table('chatcontacts')
            ->where('from_user_id', $from_user_id)
            ->where('email', $email)
            ->first();
            $where = 'where from_user_id = ? and email = ?';
            $params = [$message, $from_user_id, $email];

        } else if ($to_user_id!= null) {
            $already = DB::table('chatcontacts')
            ->where('from_user_id', $from_user_id)
            ->where('to_user_id', $to_user_id)
            ->first();
            $where = 'where from_user_id = ? and to_user_id = ?';
            $params = [$message, $from_user_id, $to_user_id];
        }

        if ($already) {
            $sql = <<<EOF
update chatcontacts
set contact_date = sysdate(),
    contact_message = ?,
    updated_at = sysdate()
$where
EOF;
            //echo $sql;
            DB::insert($sql, $params);

        } else {
            $sql = <<<EOF
insert into chatcontacts (
    from_user_id, to_user_id, contact_date, contact_message, contact_agree, email, created_at, updated_at
) values (
    ?, ?, sysdate(), ?, 0, ?, sysdate(), sysdate()
)
EOF;
//echo $sql;
            DB::insert($sql, [
                    $from_user_id, $to_user_id, $message, $email
            ]);
        }
    }

    /**
     * コンタクト承認を登録
     *
     * @param   int     $from_user_id   コンタクト送信者のユーザーID
     * @param   int     $to_user_id     コンタクト受信者のユーザーID
     * @param   string  $message        メッセージ
     * @param   string  $email          メールアドレス
     */
    static function setAgree($from_user_id, $to_user_id, $message, $email, $agree="1")
    {
        $sql = <<<EOF
update chatcontacts
set contact_agree = ?,
    to_user_id = ?,
    agree_message = ?,
    updated_at = sysdate()
where from_user_id = ?
and   (to_user_id = ? or (to_user_id = 0 and email = ?))
EOF;
//echo $sql;

        DB::insert($sql, [
                $agree, $to_user_id, $message, $from_user_id, $to_user_id, $email
        ]);

//        print_r(array($message, $from_user_id, $to_user_id, $email));
//        exit();
    }

    /**
     * 登録されているユーザーを検索
     *
     * @param   string  $keyword    検索キーワード
     * @return  検索結果
     */
    static function searchUsers($user_id, $keyword)
    {
        $sql = <<<EOF
select  distinct u.id, u.name
from    users u
        left join chatcontacts c
            on u.id = c.to_user_id
where u.name like ?
and u.id <> ?
and u.id not in (
select  c.from_user_id as id
from    users u
        inner join chatcontacts c
            on u.id = c.from_user_id
where   c.to_user_id = ?
union
select  c.to_user_id as id
from    users u
        inner join chatcontacts c
            on u.id = c.to_user_id
where   c.from_user_id = ?
)
order by u.name
EOF;
//echo $sql;


        $result = DB::select($sql, [
                '%'. $keyword. '%',
                $user_id,
                $user_id,
                $user_id,
        ]);

        return $result;
    }

    /**
     * 登録されているユーザーを検索
     *
     * @param   string  $keyword    検索キーワード
     * @return  検索結果
     */
    static function searchUsers2($user_id, $keyword)
    {
        $sql = <<<EOF
select  u.id, u.name, u.file
from    users u
where u.name like ?
and u.id <> ?
and u.id in (
        select  c.from_user_id as id
        from    users u
                inner join chatcontacts c
                    on u.id = c.from_user_id
        where   c.to_user_id = ?
        union
        select  c.to_user_id as id
        from    users u
                inner join chatcontacts c
                    on u.id = c.to_user_id
        where   c.from_user_id = ?
)
order by u.name
EOF;
        //echo $sql;


        $result = DB::select($sql, [
                '%'. $keyword. '%',
                $user_id,
                $user_id,
                $user_id,
        ]);

        return $result;
    }

    /**
     * コンタクト未承認リストを取得
     *
     * @param   int     $user_id    ユーザーID
     * @param   string  $email      メールアドレス
     * @return  未承認リスト
     */
    static function getUnContactList($user_id, $email)
    {
        $sql = <<< EOF
select  u.id, u.name
from    users u
        inner join chatcontacts c
            on u.id = c.from_user_id
where c.to_user_id = ?
and c.contact_agree = '0'
union
select  u.id, u.name
from    users u
        inner join chatcontacts c
            on u.id = c.from_user_id
where c.email = ?
and c.contact_agree = '0'
order by name
EOF;

        $result = DB::select($sql, [
                $user_id,
                $email,
        ]);

        return $result;
    }

    /**
     * コンタクト承認リストを取得
     *
     * @param   int     $user_id    ユーザーID
     * @return  承認リスト
     */
    static function getContactList($user_id)
    {
        $sql = <<<EOF
select  g.id as group_id, u.id, u.name, u.file
from    groups g
        inner join chatgroups g1
            on g.id = g1.group_id
            and g.kind = 1
        inner join chatgroups g2
            on g1.group_id = g2.group_id
        inner join users u
            on u.id = g2.user_id
where   g1.user_id = ?
and     g2.user_id != ?
EOF;

        $result = DB::select($sql, [
                $user_id,
                $user_id,
                $user_id,
                $user_id,
        ]);

        return $result;
    }

    /**
     * 既に友達かどうかチェックする
     *
     * @param unknown $user_id
     * @param unknown $email
     * @return unknown
     */
    static function checkAlreadyContact($user_id, $email) {

        $sql = <<<EOF
select g.id, g.name, cg.user_id
from groups g
    inner join chatgroups cg
        on g.id = cg.group_id
    inner join (
        select cg2.group_id
        from chatgroups cg2
        where user_id = ?
    ) t
    on cg.group_id = t.group_id
where g.kind = 1
and cg.user_id in (
    select id from users where email = ?
)
EOF;

        $result = DB::select($sql, [
                $user_id,
                $email,
        ]);

        return $result;
    }

    /**
     * QRコード生成時に識別コードを発行する
     *
     * @return unknown|NULL
     */
    static function setMyQR($user_id) {

        $already = DB::table('myqrs')
            ->where('user_id', $user_id)
            ->first();

        if ($already) {
            $qr_key = $already->qrkey;

        } else {
            # 32文字のランダムな文字列を作成.
            $qr_key = str_random(32);

            try {
                DB::table('myqrs')->insert([
                        'user_id'       => $user_id,
                        'qrkey'         => $qr_key,
                        'created_at'    => date("Y/m/d H:i:s"),
                ]);

            } catch (\PDOException $e){
                return 'error';
            }
        }

        return $qr_key;
    }
}
