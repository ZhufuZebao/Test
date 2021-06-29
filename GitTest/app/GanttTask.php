<?php
/**
 * 
 *
 * @author  Reiko Mori
 */

namespace App;

class GanttTask extends Model
{
    protected $table = "gantt_tasks";
    public $primaryKey = "id";
    public $timestamps = false;
}

