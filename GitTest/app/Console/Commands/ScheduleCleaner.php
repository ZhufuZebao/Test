<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Schedule;
use App\Models\ScheduleSub;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:clean {startDate} {endDate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run ScheduleCleaner will softDelete the dirty data. ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stDate = $this->argument('startDate');
        $edDate = $this->argument('endDate');

        $edDate=date('Y/m/d',strtotime("$edDate +1 day"));
        $edDate=date('Y/m/d H:i',strtotime("$edDate -1 Minute"));
        //-1day
        $schedule = DB::table('schedules')->where(function ($q2) use ($stDate, $edDate) {
            $q2->where(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_datetime', [$stDate, $edDate])
                    ->orWhereBetween('ed_datetime', [$stDate, $edDate])
                    ->orwhere('st_datetime', '<=', $stDate)
                    ->where('ed_datetime', '>=', $edDate);
            });
            $q2->orWhere(function ($q) use ($stDate, $edDate) {
                $q->whereBetween('st_span', [$stDate, $edDate])
                    ->orWhereBetween('ed_span', [$stDate, $edDate])
                    ->orwhere('st_span', '<=', $stDate)
                    ->whereNotNull('ed_span')
                    ->whereNotNull('st_span')
                    ->where('ed_span', '>=', $edDate);
            });
        })
        ->whereNull('deleted_at')
        ->get()
        ->toArray();
        
        $schedule = $this->objectToArray($schedule);

        for($i = 0; $i < count($schedule); $i++) {
            if($schedule[$i]['repeat_kbn']) {
                //get subs
                $subs = DB::table('schedulesubs')
                    ->where('relation_id', $schedule[$i]['id'])
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();
                $schedule[$i]['schedule_subs'] = $subs;
            } else {
                $schedule[$i]['schedule_subs'] = [];
            }
        }

        //wrong data fliter
        //start date must <= end date
        //repeat type must have least 1 sub
        $scheduleEmptySub = [];
        $scheduleWrongDate = [];
        for($i = 0; $i < count($schedule); $i++) {
            if(strtotime($schedule[$i]['st_datetime']) > strtotime($schedule[$i]['ed_datetime'])) {
                $scheduleWrongDate[] = $schedule[$i];
            }
            if((int)$schedule[$i]['repeat_kbn']) {
                if(!count($schedule[$i]['schedule_subs'])) {
                    $scheduleEmptySub[] = $schedule[$i];
                }
            }
        }
        //wrong data fliter end
        $numEmptySub = count($scheduleEmptySub);
        $numWrongDate = count($scheduleWrongDate);

        if(!($numEmptySub + $numWrongDate)) {
            $this->line('No dirty data found.');
        }

        if($numEmptySub) {
            $this->line('These schedules miss some data.Please confirm delete operation!');
            foreach($scheduleEmptySub as $itemEmpty) {
                $this->line($itemEmpty['id'] . ' | ' . $itemEmpty['subject'] . ' | ' . $itemEmpty['st_datetime'] . ' | ' . $itemEmpty['ed_datetime']);
            }
        }

        if ($numEmptySub && $this->confirm('This operation will delete these records above.Do you wish to continue?')) {
            $this->line('Please wait...');
            $bar = $this->output->createProgressBar($numEmptySub);
            $bar->start();
            for($i = 0; $i < $numEmptySub; $i++) {
                //delete
                Schedule::where('id', '=', $scheduleEmptySub[$i]['id'])->delete();
                //make log
                $logMsg = 'ScheduleClean:' . $scheduleEmptySub[$i]['id'] . ' | ' . time();
                Log::info($logMsg);
                $bar->advance();
            }
            $bar->finish();
            $this->line('Task finished successfully.');
        }

        if($numWrongDate) {
            $this->line('These schedules have wrong start date or end date.Please confirm delete operation!');
            foreach($scheduleWrongDate as $itemWrong) {
                $this->line($itemWrong['id'] . ' | ' . $itemWrong['subject'] . ' | ' . $itemWrong['st_datetime'] . ' | ' . $itemWrong['ed_datetime']);
            }
        }

        if ($numWrongDate && $this->confirm('This operation will delete these records above.Do you wish to continue?')) {
            $this->line('Please wait...');
            $bar = $this->output->createProgressBar($numWrongDate);
            $bar->start();
            for($i = 0; $i < $numWrongDate; $i++) {
                //delete
                Schedule::where('id', '=', $scheduleWrongDate[$i]['id'])->delete();
                ScheduleSub::where('relation_id', '=', $scheduleWrongDate[$i]['id'])->delete();
                //make log
                $logMsg = 'ScheduleClean:' . $scheduleWrongDate[$i]['id'] . ' | ' . time();
                Log::info($logMsg);
                $bar->advance();
            }
            $bar->finish();
            $this->line('Task finished successfully.');
        }
    }

    private function objectToArray($e) {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $e[$k] = (array)$this->objectToArray($v);
            }
        }
        return $e;
    }
}
