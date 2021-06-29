<?php
/**
 * 求人案件の応募テーブル
 *
 * @author  Miyamoto
 */

namespace App;

use DB;

class Applicant extends Model
{
    protected $fillable = [
            'job_id', 'user_id', 'name', 'telno1', 'telno2', 'notes',
    ];
}
