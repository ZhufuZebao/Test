<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Firebaseids extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes; //Turn on softDeletes
    /**
     * FirebaseIDを取得
     * @param  String $kind アプリ識別
     * @param  String $userId ユーザーID
     * @return Model
     */
    static function getFirebaseId($kind, $userId)
    {
        return Firebaseids::where('user_id', $userId)
            ->where("app_kind", $kind)
            ->first();
    }
}
