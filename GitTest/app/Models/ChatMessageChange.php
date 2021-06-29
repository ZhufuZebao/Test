<?php
/**
 * Created by PhpStorm.
 * User: P0154081
 * Date: 2020/09/09
 * Time: 16:57
 */
namespace App\Models;

class ChatMessageChange extends Model
{
    protected $table = "chatmessage_changes";
    protected $guarded = ['id'];
    protected $primaryKey = "id";
    protected $fillable = [
        'group_id', 'message_id', 'message'
    ];

}
