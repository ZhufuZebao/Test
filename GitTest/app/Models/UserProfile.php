<?php
/**
 * Created by PhpStorm.
 * User: P0123779
 * Date: 2019/07/04
 * Time: 19:11
 */

namespace App\Models;


class UserProfile extends Model
{
    protected $table = "user_profiles";
    protected $guarded = ['id'];
}