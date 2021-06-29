<?php

namespace App\Console\Commands;

use App\Models\ChatGroup;
use App\Models\ChatList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearChatListsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearChatlists:group_id {startId} {endId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clear the data that chatlists exist but chatgroups do not ; Complete data that exists in chatgroups but is missing from chatlists.";

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
        $groupIdRange = [$startId,$endId];

        $this->line('-----------------Clear the data that chatlists exist but chatgroups do not-----------------');
        //get chatgroups data
        $chatgroups = DB::table('chatgroups as cg')
            ->join('groups as g','cg.group_id','=','g.id')
            ->whereBetween('cg.group_id',$groupIdRange)
            ->whereNull('g.deleted_at')
            ->whereNull('cg.deleted_at')
            ->select('cg.group_id','cg.user_id','g.kind')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (empty($chatgroups)){
            $this->error('chatgroups not found');
            return;
        }
        //process data => $arr[group_id] = [user id lists]
        $groupIdToUserList = [];
        foreach ($chatgroups as $v){
            $groupIdToUserList[$v['group_id']][] = $v['user_id'];
        }
        //get chatlists data
        $chatLists = DB::table('chatlists as cl')
            ->join('groups as g','cl.group_id','=','g.id')
            ->whereBetween('cl.group_id',$groupIdRange)
            ->whereNull('g.deleted_at')
            ->whereNull('cl.deleted_at')
            ->select('cl.id','cl.group_id','cl.owner_id')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (empty($chatLists)){
            $this->error('chatlists not found');
            return;
        }
        //check data not in chatgroups but in chatlists
        $deleteData = [];
        foreach ($chatLists as $v){
            if (isset($groupIdToUserList[$v['group_id']]) && !in_array($v['owner_id'],$groupIdToUserList[$v['group_id']])){
                array_push($deleteData, $v);
            }
        }
        if (empty($deleteData)){
            $this->info('No data.');
        }else{
            //show in terminal
            $this->line('There are ' . count($deleteData) . ' data that not in chatgroups.Please confirm delete operation!');
            $headers = ['chatlists_id', 'group_id', 'owner_id'];
            $this->table($headers, $deleteData);
            if ($this->confirm('This operation will delete these records above.Do you wish to continue?')) {
                $this->line('Please wait...');
                $bar = $this->output->createProgressBar(count($deleteData));
                $bar->start();
                foreach ($deleteData as $v) {
                    //delete
                    ChatList::where('id', $v['id'])->delete();
                    //make log
                    $logMsg = "ClearChatListsData[delete]: chatlists_id = {$v['id']}, group_id = {$v['group_id']}, owner_id = {$v['owner_id']} | " . time();
                    Log::info($logMsg);
                    $bar->advance();
                }
                $bar->finish();
                $this->info('The delete task finished successfully.');
            }else{
                return;
            }
        }



        //
        $this->line('-----------------Complete data that exists in chatgroups but is missing from chatlists-----------------');
        $newChatList = DB::table('chatlists as cl')
            ->join('groups as g','cl.group_id','=','g.id')
            ->whereBetween('cl.group_id',$groupIdRange)
            ->whereNull('g.deleted_at')
            ->whereNull('cl.deleted_at')
            ->select('cl.id','cl.group_id','cl.owner_id')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (empty($newChatList)){
            $this->error('chatlists not found');
            return;
        }
        $groupIdToOwnerList = [];
        foreach ($newChatList as $v){
            $groupIdToOwnerList[$v['group_id']][] = $v['owner_id'];
        }
        //check data in chatgroups but not in chatlists
        $dataAll = []; //both group chat and one-on-one chat
        foreach ($chatgroups as $v){
            if (isset($groupIdToOwnerList[$v['group_id']]) && !in_array($v['user_id'],$groupIdToOwnerList[$v['group_id']])){
                array_push($dataAll,$v);
            }
        }

        $waitInsertData = []; //final insert data
        foreach ($dataAll as $k => $v){
            $time = time();
            $waitInsertData[$k] = [
                'owner_id' => $v['user_id'],
                'user_id' => null,
                'group_id' => $v['group_id'],
                'created_at' => date('Y/m/d H:i:s',$time),
            ];
            if ($v['kind'] == 1){
                $group = $groupIdToUserList[$v['group_id']];
                if (count($group) == 2){
                    foreach ($group as $uid){
                        //find another user
                        if ($uid != $v['user_id']){
                            $waitInsertData[$k]['user_id'] = $uid;
                        }
                    }
                }
            }
        }

        if (empty($waitInsertData)){
            $this->info('No data.');
        }else{
            //show in terminal
            $this->line('There are ' . count($waitInsertData) . ' data that not in chatlists.Please confirm insert operation!');
            $headers = ['owner_id', 'user_id', 'group_id','created_at'];
            $this->table($headers, $waitInsertData);
            if ($this->confirm('This operation will insert these records above.Do you wish to continue?')) {
                $this->line('Please wait...');
                $bar = $this->output->createProgressBar(count($waitInsertData));
                $bar->start();
                foreach ($waitInsertData as $v) {
                    //insert
                    DB::table('chatlists')->insert($v);
                    //make log
                    $logMsg = "ClearChatListsData[insert]: group_id = {$v['group_id']}, owner_id = {$v['owner_id']}, user_id = {$v['user_id']} | " . time();
                    Log::info($logMsg);
                    $bar->advance();
                }
                $bar->finish();
                $this->info('The delete task finished successfully.');
            }else{
                return;
            }
        }

    }
}
