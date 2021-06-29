<?php
/**
 * 他の Eloquent\Model から hasOne() などリレーションで取得するためのクラス
 *
 * @author  Reiko Mori
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table      = "users";
    public    $primaryKey = "id";

    /* public function getName()
     * {
     *     return sprintf('%s %s', $this->last_name, $this->first_name);
     * }*/
}
