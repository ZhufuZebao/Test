<?php

namespace App\Console\Commands;

use App\Models\ChatGroup;
use App\Models\Project;
use App\Models\ProjectParticipant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearChatGroupsData extends Command
{
    /**
     * The name and signature of the console command.
     * example: php artisan chatgroups:project 1 100
     * @var string
     */
    protected $signature = 'chatgroups:project {startId} {endId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear data that are not in the project participants but are displayed in the chat group';

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
        $startId = (int)$this->argument('startId');
        $endId = (int)$this->argument('endId');
        if ($startId <= 0 || $endId <= 0) {
            $this->error('"startId" and "endId" must be greater than 0.');
            return;
        }
        if ($startId > $endId) {
            $this->error('"startId" must be less than "endId" .');
            return;
        }
        $projectIdList = [$startId, $endId];
        //project participants
        $participants = ProjectParticipant::whereBetween('project_id', $projectIdList)->select('project_id', 'user_id')->get()->toArray();

        if (empty($participants)) {
            $this->error('project participants not found.');
            return;
        }
        //process data
        $projectIdToUserIdList = [];
        foreach ($participants as $v) {
            $projectIdToUserIdList[$v['project_id']][] = $v['user_id'];
        }
        $chatGroupsUsers = DB::table('chatgroups as cg')
            ->leftJoin('groups as g', 'cg.group_id', '=', 'g.id')
            ->leftJoin('projects as p', function ($join) {
                $join->on('g.id', '=', 'p.group_id')->orOn('g.parent_id', '=', 'p.group_id');
            })
            ->whereBetween('p.id', $projectIdList)
            ->whereNull('p.deleted_at')
            ->whereNull('cg.deleted_at')
            ->whereNull('g.deleted_at')
            ->select('cg.id', 'cg.user_id', 'cg.group_id', 'p.id as project_id')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();

        $deleteData = [];
        foreach ($chatGroupsUsers as $v) {
            if (!in_array($v['user_id'], $projectIdToUserIdList[$v['project_id']])) {
                array_push($deleteData, $v);
            }
        }
        if (empty($deleteData)) {
            $this->info('No wrong data found.');
        } else {
            $this->line('There are ' . count($deleteData) . ' data that not in the project participants.Please confirm delete operation!');
            $headers = ['chatgroups_id', 'user_id', 'group_id', 'project_id'];
            $this->table($headers, $deleteData);
            if ($this->confirm('This operation will delete these records above.Do you wish to continue?')) {
                $this->line('Please wait...');
                $bar = $this->output->createProgressBar(count($deleteData));
                $bar->start();
                foreach ($deleteData as $v) {
                    //delete
                    ChatGroup::where('id', $v['id'])->delete();
                    //make log
                    $logMsg = "ClearChatGroupsData: chatgroups_id = {$v['id']}, project_id = {$v['project_id']}, group_id = {$v['group_id']}, user_id = {$v['user_id']} | " . time();
                    Log::info($logMsg);
                    $bar->advance();
                }
                $bar->finish();
                $this->info('Task finished successfully.');
            }
        }
    }
}
