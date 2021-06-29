<?php
/**
 * ダッシュボード
 *
 * @author  Reiko Mori
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends \App\Http\Controllers\Controller
{
    /**
     * method: GET
     */
    public function index()
    {
        $projects = \App\Project::take(5)->get();
        $reports  = \App\Report::take(4)->get();

        return view('index', [
            'projects' => $projects,
            'reports'  => $reports,
        ]);
    }

    /**
     * method: GET
     * return string, in text/html
     */
    public function scheduleOfDay($date=null)
    {
        if(!$date)
        {
            $date = date('Y-m-d');
        }

        $count  = 3;
        $models = \App\Schedule::where([])
                               ->take($count)
                               ->get();
        $buf = [];
        $i = 0;
        foreach($models as $model)
        {
            $i++;
            $buf[] = <<<ITEM
		<a id="today-schedule-$i" href="" class="today-schedule">
		<span class="time-start">$model->st_time</span>
		<span class="time-finish">$model->ed_time</span>
		<span class="clearfix"></span>
		<span class="event">$model->subject</span>
		</a>
ITEM;
        }
        while($i < $count)
        {
            $buf[] = '<a class="today-schedule"></a>';
        }

        return implode("\n", $buf);
    }

}
