<?php

    namespace App\Models;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class ChatLike extends Model
    {
        use SoftDeletes;
        protected $table = "chatlikes";
        protected $primaryKey = "id";
        protected $guarded = 'id';
    }