<?php
/**
 * ユーザーテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Notifications\User\ResetPassword;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'name', 'email', 'password', 'birth', 'sex',
            'zip',  'pref', 'addr', 'telno1', 'telno2',
            'comp', 'class',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function profile()
    {
        return $this->hasOne(\App\UserProfile::class, 'id', 'id');
    }

    public function userUpdate()
    {
    }

    /**
     * パスワードリセット通知の送信
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    /**
     * 職人情報を取得
     *
     * @param   string  $keyword    検索キーワード
     * @param   string  $place      勤務地（検索用）
     * @return  array   取得したデータ
     */
    static function get($id)
    {
        $sql = <<<EOF
select  u.*, p.name as pref_name, a.city, ifnull(TIMESTAMPDIFF(YEAR, `birth`, CURDATE()), 0) AS age,
        p1.name as area1_name, p2.name as area2_name, p3.name as area3_name, p4.name as area4_name, p5.name as area5_name
from    users u
        left join prefs p
            on ifnull(u.pref, 0) = p.id
        left join prefs p1
            on ifnull(u.area1, 0) = p1.id
        left join prefs p2
            on ifnull(u.area2, 0) = p2.id
        left join prefs p3
            on ifnull(u.area3, 0) = p3.id
        left join prefs p4
            on ifnull(u.area4, 0) = p4.id
        left join prefs p5
            on ifnull(u.area5, 0) = p5.id
        left join mst_address_mini a
            on ifnull(u.addr_code, 0) = a.officialCode
where   u.id = ?
EOF;

        $data = DB::select($sql, [
                $id
        ]);

        return $data;

    }

    /**
     * 引数で指定されたユーザーIDの名前を取得
     *
     * @param   int     $id    ユーザーID
     * @return  string  ユーザーの名前
     */
    static function getUserName($id)
    {
        $name = DB::table('users')->where('id', $id)->value('name');
        return $name;
    }

    /**
     * 引数で指定されたユーザー情報を取得
     *
     * @param   int     $user_id    ユーザーID
     * @return  array   取得したデータ
     */
    static function getMembers($user_id)
    {
        $sql = <<<EOF
select  u.id as user_id, u.name
from    users u
where   u.id = ?
EOF;

        $data = DB::select($sql, [
                $user_id
        ]);

        return $data;
    }

    /**
     * 職人情報を取得
     *
     * @param   string  $keyword    検索キーワード
     * @param   string  $place      勤務地（検索用）
     * @return  array   取得したデータ
     */
    static function getList($keyword, $place)
    {
        $sql = <<<EOF
select  u.*, p.name as pref_name
from    users u
        left join prefs p
            on ifnull(u.pref, 0) = p.id
where   (u.name like ? or u.comp like ?)
and     (p.name is null or p.name like ? or u.addr like ?)
EOF;

        $data = DB::select($sql, [
                '%'. $keyword. '%',
                '%'. $keyword. '%',
                '%'. $place. '%',
                '%'. $place. '%',
        ]);

        return $data;

    }

    static function getAllUsers()
    {
        $sql = "select * from users";

        $result = DB::select($sql);

        return $result;
    }

    static function getUsersFromEmail($emial)
    {
        $sql = "select * from users where email = ?";

        $data = DB::select($sql, [
                $emial
        ]);

        return $data;
    }
}
