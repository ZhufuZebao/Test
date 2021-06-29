<?php

namespace App\Models;


class ChatNice extends Model
{
    // 2020-10-27 #2298
    use SoftDeletes; //Turn on softDeletes
    protected $table = "chatnices";
}
