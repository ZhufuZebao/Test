<?php
/**
 * UserProfile - 職人プロフィール
 */

namespace App;

use Illuminate\Support\Facades\Validator;

class UserProfile extends Model
{
    /**
     * storage path of photo
     */
    public const PHOTO_PATH = 'photo/user_profile';

    protected $table   = "user_profiles";
    public $primaryKey = "id";

    protected $casts = [
    ];
    
    protected $fillable = [
        'title',
        'content',
        'photo',
    ];

    public static function rules()
    {
        return [
            'title'      => ['required', 'string', 'max:256'],
            'content'    => ['required', 'string', 'max:2048'],
            'photo'      => ['nullable', 'string', 'max:256', 'unique:user_profiles,photo'],
        ];
    }

    public function user()
    {
        return $this->hasOne(\App\User::class, 'id', 'id');
    }

    public function validate()
    {
        // unique, でも自分自身と同一でよろしい
        $rules = self::rules();
        array_pop($rules['photo']);//既存ルールの末尾を消去
        $rules['photo'][] = 'unique:user_profiles,photo,'.$this->id;//上書き

        return Validator::make($this->getAttributes(), $rules);
    }

}
