<?php
/**
 * チャットグループ管理のモデル
 *
 * @author  Miyamoto
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Chatgroup extends Model
{
    /**
     * チャットグループに登録
     *
     * @param   int     $group_id   グループID
     * @param   array   $member     メンバーの配列
     */
    static function set($group_id, $member)
    {
        if (!is_array($member) || empty($member)) {
            return;
        }

        foreach ($member as $user_id => $val) {
            $data = DB::table('chatgroups')->where('group_id', $group_id)
                    ->where('user_id', $user_id)->first();

            if (!$data) {
                DB::table('chatgroups')->insertGetId([
                        'user_id'    => $user_id,
                        'group_id'   => $group_id,
                        'admin'      => $val,
                        'created_at' => date('Y/m/d H:i:s')
                ]);
            }
        }
    }

    /**
     * 指定されたユーザーが登録されているグループの一覧を取得
     *
     * @param   int     $user_id    ユーザーID
     * @return  array   取得したグループ一覧
     */
    static function get($user_id)
    {
        $sql = <<<EOF
select  g.name as group_name, c.group_id
from    chatgroups c
        inner join groups g
            on c.group_id = g.id
        inner join users u
            on c.user_id = u.id
where   c.user_id = ?
and     g.kind = 0
EOF;

        $data = DB::select($sql, [
                $user_id
        ]);
//print_r($data);

        return $data;
    }

    /**
     * グループのメンバー一覧を取得（自分以外）
     *
     * @param   int     $group_id   グループID
     * @param   int     $user_id    自分のユーザーID
     * @return  array   取得したメンバー一覧
     */
    static function getMembers($group_id, $user_id)
    {
        $sql = <<<EOF
select  g.user_id, u.name, u.file
from    chatgroups g
        inner join users u
            on g.user_id = u.id
where   g.group_id = ?
and     g.user_id <> ?
EOF;

        $data = DB::select($sql, [
                $group_id,
                $user_id
        ]);

        return $data;
    }

    /**
     * グループのメンバー一覧を取得（自分含む）
     *
     * @param   int     $group_id   グループID
     * @return  array   取得したメンバー一覧
     */
    static function getMembers2($group_id)
    {
        $sql = <<<EOF
select  u.id, u.name, g.admin, u.file
from    chatgroups g
        inner join users u
            on g.user_id = u.id
where   g.group_id = ?
EOF;

        $data = DB::select($sql, [
                $group_id
        ]);

        return $data;
    }

    /**
     * グループのメンバー一覧を取得（自分含む／ダイレクトチャットがあるかどうかも含む）
     *
     * @param   int     $group_id   グループID
     * @return  array   取得したメンバー一覧
     */
    static function getMembers3($user_id, $group_id)
    {
        $sql = <<<EOF
select  u.id, u.name, g.admin, u.file, d.id as group_id, d.name as group_name
from    chatgroups g
        inner join users u
            on g.user_id = u.id
        left join (
            select  g0.id, g0.name, g0.kind, g1.user_id
            from    groups g0
                inner join chatgroups g1
                    on g0.id = g1.group_id
                inner join (select group_id from chatgroups where user_id = ?) g2
                    on g1.group_id = g2.group_id
            where g0.kind = 1
        ) d
            on u.id = d.user_id
            and d.user_id <> ?
where   g.group_id = ?
EOF;

        $data = DB::select($sql, [
                $user_id,
                $user_id,
                $group_id
        ]);

        return $data;
    }

    /**
     * ユーザーを検索する
     *
     * @param   int     $group_id   グループID
     * @param   string  $keyword    検索キーワード
     * @return  取得したユーザーデータ
     */
    static function searchUsers($group_id, $keyword)
    {
        $sql = <<<EOF
select  u.id, u.name, u.file
from    users u
        left join chatgroups c
            on u.id = c.user_id
            and c.group_id = ?
where u.name like ?
and c.user_id is null
EOF;

        $data = DB::select($sql, [
                $group_id,
                '%'. $keyword. '%',
        ]);

        return $data;
    }

    /**
     * チャットグループから削除
     *
     * @param   int     $group_id   グループID
     * @param   array   $member     メンバーの配列
     */
    static function deleteUser($group_id, $member)
    {
        if (!is_array($member) || empty($member)) {
            return;
        }

        foreach ($member as $user_id => $val) {
            if ($val) {
                $data = DB::table('chatgroups')->where('group_id', $group_id)
                ->where('user_id', $user_id)->delete();
            }
        }
    }

    /**
     * チャットのメンバー＆グループを一覧で取得
     */
    static function getMemberGroupList($user_id, $kind=null)
    {
        // ダイレクトチャット＆グループチャット
        if ($kind === null) {

            $sql = <<<EOF
select  1 as kind,
        u.name,
        g.id as group_id,
        u.file,
        m.message,
        (case
            when date_format(sysdate(), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('今日 ', date_format(m.created_at, '%H:%i'))
            when date_format(date_add(sysdate(), interval -1 day), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('昨日 ', date_format(m.created_at, '%H:%i'))
            else date_format(m.created_at, '%e日 %H:%i')
        end) as date_time,
        (select user_id from chatgroups where group_id = g.id and user_id <> ?) as person,
        g1.admin
from    groups g
        inner join chatgroups g1
            on g.id = g1.group_id
            and g.kind = 1
        inner join chatgroups g2
            on g1.group_id = g2.group_id
            and g.kind = 1
        inner join users u
            on u.id = g2.user_id
        left join chatmessages m
            on g.id = m.group_id
            and m.id = (select max(id) as max_id from chatmessages where group_id = g.id)
where   g1.user_id = ?
and     g2.user_id != ?

union

select  0 as kind,
        g.name as name,
        c.group_id,
        g.file as file,
        m.message,
        (case
            when date_format(sysdate(), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('今日 ', date_format(m.created_at, '%H:%i'))
            when date_format(date_add(sysdate(), interval -1 day), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('昨日 ', date_format(m.created_at, '%H:%i'))
            else date_format(m.created_at, '%e日 %H:%i')
        end) as date_time,
        null as person,
        1 as admin
from    chatgroups c
        inner join groups g
            on c.group_id = g.id
        inner join users u
            on c.user_id = u.id
        left join chatmessages m
            on g.id = m.group_id
            and m.id = (select max(id) as max_id from chatmessages where group_id = g.id)
where   c.user_id = ?
and     g.kind = 0

order by kind desc, group_id
EOF;

            $data = DB::select($sql, [
                    $user_id,
                    $user_id,
                    $user_id,
                    $user_id,
                    $user_id,
                    $user_id,
            ]);

        // ダイレクトチャットのみ
        } else if ($kind == 1) {

            $sql = <<<EOF
select  1 as kind,
        u.name,
        g.id as group_id,
        u.file,
        m.message,
        (case
            when date_format(sysdate(), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('今日 ', date_format(m.created_at, '%H:%i'))
            when date_format(date_add(sysdate(), interval -1 day), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('昨日 ', date_format(m.created_at, '%H:%i'))
            else date_format(m.created_at, '%e日 %H:%i')
        end) as date_time,
        (select user_id from chatgroups where group_id = g.id and user_id <> ?) as person,
        g1.admin
from    groups g
        inner join chatgroups g1
            on g.id = g1.group_id
            and g.kind = 1
        inner join chatgroups g2
            on g1.group_id = g2.group_id
            and g.kind = 1
        inner join users u
            on u.id = g2.user_id
        left join chatmessages m
            on g.id = m.group_id
            and m.id = (select max(id) as max_id from chatmessages where group_id = g.id)
where   g1.user_id = ?
and     g2.user_id != ?
EOF;

            $data = DB::select($sql, [
                    $user_id,
                    $user_id,
                    $user_id,
            ]);

        // グループチャットのみ
        } else {

            $sql = <<<EOF
select  0 as kind,
        g.name as name,
        c.group_id,
        g.file as file,
        m.message,
        (case
            when date_format(sysdate(), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('今日 ', date_format(m.created_at, '%H:%i'))
            when date_format(date_add(sysdate(), interval -1 day), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('昨日 ', date_format(m.created_at, '%H:%i'))
            else date_format(m.created_at, '%e日 %H:%i')
        end) as date_time,
        null as person,
        1 as admin
from    chatgroups c
        inner join groups g
            on c.group_id = g.id
        inner join users u
            on c.user_id = u.id
        left join chatmessages m
            on g.id = m.group_id
            and m.id = (select max(id) as max_id from chatmessages where group_id = g.id)
where   c.user_id = ?
and     g.kind = 0

order by kind desc, group_id
EOF;

            $data = DB::select($sql, [
                    $user_id,
                    $user_id,
                    $user_id,
            ]);
        }

        return $data;
    }

    /**
     * メンバーの人数を取得
     *
     */
    static function getMemberCount($group_id) {
        $sql = <<<EOF
select  count(user_id) as cnt
from    chatgroups
where   group_id = ?
EOF;

        $data = DB::select($sql, [
                $group_id
        ]);

        return (is_array($data) && !empty($data) && isset($data[0])) ? $data[0]->cnt : 0;
    }

    /**
     * ダイレクトチャットメンバーをチェック
     */
    static function checkDirectChatMember($user_id1, $user_id2) {

        $sql = <<<EOF
select  g.id, g.name, g.kind
from    groups g
            inner join (select group_id from chatgroups where user_id = ?) g1
                on g.id = g1.group_id
            inner join (select group_id from chatgroups where user_id = ?) g2
                on g1.group_id = g2.group_id
where g.kind = 1
EOF;

        $data = DB::select($sql, [
                $user_id1,
                $user_id2,
        ]);
//print_r($data);

        return (is_array($data) && !empty($data)) ? true : false;
    }

    /**
     * ダイレクトチャットメンバーを取得
     *
     * @param unknown $user_id
     * @return unknown
     */
    static function getDirectMember($user_id) {

        $sql = <<<EOF
select  u.id as user_id,
        u.name,
        g.id as group_id,
        u.file,
        m.message,
        (case
            when date_format(sysdate(), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('今日 ', date_format(m.created_at, '%H:%i'))
            when date_format(date_add(sysdate(), interval -1 day), '%Y%m%d') = date_format(m.created_at, '%Y%m%d')
                then concat('昨日 ', date_format(m.created_at, '%H:%i'))
            else date_format(m.created_at, '%e日 %H:%i')
        end) as date_time,
        (select user_id from chatgroups where group_id = g.id and user_id <> ?) as person,
        g1.admin
from    groups g
        inner join chatgroups g1
            on g.id = g1.group_id
            and g.kind = 1
        inner join chatgroups g2
            on g1.group_id = g2.group_id
            and g.kind = 1
        inner join users u
            on u.id = g2.user_id
        left join chatmessages m
            on g.id = m.group_id
            and m.id = (select max(id) as max_id from chatmessages where group_id = g.id)
where   g1.user_id = ?
and     g2.user_id != ?
EOF;

        $data = DB::select($sql, [
                $user_id,
                $user_id,
                $user_id,
        ]);

        return $data;
    }
}
