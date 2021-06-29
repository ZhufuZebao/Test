<?php
/**
 * ユーザーテーブル
 *
 * @author  Miyamoto
 */

namespace App\Models;

use App\Notifications\User\ResetPassword;
use DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class User extends Model
    implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use SoftDeletes;
    use HasApiTokens, Notifiable;
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    const IMAGE_PATH = "user/";
    const PHOTO_PATH = "images/user/";
    const PHOTO_DEFAULT_PATH = "images/no-image.png";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','email', 'password','last_name','first_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function toContact(){
        return $this->hasOne('App\Models\ChatContact','to_user_id','id');
    }

    public function fromContact(){
        return $this->hasOne('App\Models\ChatContact','from_user_id','id');
    }


    public function profile()
    {
        return $this->hasOne(\App\UserProfile::class, 'id', 'id');
    }

    public function enterprise()
    {
        return $this->hasOne('App\Models\Enterprise','id','enterprise_id');
    }
    public function enterpriseCoop()
    {
        return $this->hasOne('App\Models\Enterprise','id','coop_enterprise_id');
    }
    public function storage()
    {
        return $this->hasOne('\App\Models\UserStorage', 'user_id', 'id');
    }
    /**
     * パスワードリセット通知の送信
     *
     * @param  string $token
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

    public function scheduleParticipants()
    {
        return $this->hasMany('App\Models\ScheduleParticipant', 'user_id', 'id');
    }

    public function chatPerson(){
        return $this->hasOne('App\Models\ChatPerson', 'user_id', 'id');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group', 'chatgroups', 'user_id', 'group_id')
            ->wherePivot('admin',Auth::id());
    }

    public function chatTask()
    {
        return $this->belongsTo('App\Models\ChatTask', 'create_user_id', 'id');
    }

    public function chatTaskCharge()
    {
        return $this->belongsTo('App\Models\ChatTaskCharge', 'user_id', 'id');
    }

    public function enterpriseParticipants()
    {
        return $this->hasMany('App\Models\EnterpriseParticipant', 'user_id', 'id');
    }

    public function enterpriseCreateBy()
    {
        return $this->hasMany('App\Models\EnterpriseParticipant', 'created_by', 'id');
    }

    public function userworkareas()
    {
        return $this->hasMany('App\Models\UserWorkarea', 'user_id', 'id');
    }


    public function getImagePathAttribute()
    {
        if ($this->file) {
            $image_path = User::PHOTO_PATH . $this->file;
        } else {
            $image_path = User::PHOTO_DEFAULT_PATH;
        }
        return $image_path;
    }


    public function validate()
    {
        $rules = [
            'last_name'=> ['required','max:49'],
            'first_name'=> ['required','max:49'],
            'name' => ['required', 'max:191'],
            'email' => ['email','required','unique:users,email','max:191'],
            'password' => ['required','max:191','confirmed'],
            'password_confirmation' => ['max:191'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
    public function detailValidate()
    {
        $rules = [
            'last_name'=> ['required','max:49'],
            'first_name'=> ['required','max:49'],
            'name' => ['required', 'max:191'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }

    public function pwdValidate()
    {

        $rules = [
            'oldPassword' => ['required','max:191'],
            'password' => ['required','max:191','confirmed'],
            'password_confirmation' => ['required','max:191'],
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
    public function mailValidate()
    {
        $rules = [
            'email' => "required|email|max:191|" . Rule::unique('users')->where('deleted_at', null)->ignore(Auth::id())
        ];
        return Validator::make($this->getAttributes(), $rules);
    }
    public function systemValidate(){
        $rules = [
            'email' => ['required','email','max:191'],
            'password' => ['required','max:191'],
        ];
        $message = [
            'email.required' => trans('messages.error.emailNull'),
            'password.required' => trans('messages.error.passwordNull'),
        ];
        return Validator::make($this->getAttributes(), $rules,$message);
    }
}
