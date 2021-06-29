<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\UserStorage;
use App\Models\ChatFile;
use App\Models\Enterprise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Group;
use App\Models\ChatMessage;
use App\Models\ChatGroup;
use App\Http\Facades\Common;

class CapacityInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capacity:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize system capacity data';

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
        //init
        if ($this->confirm('This operation will restore all capacity record.Do you wish to continue?')) {

        DB::table('user_storages')->delete();
        Enterprise::query()->update(["storage" => DB::raw("amount*10"),'usedStorage'=>0]);

        $this->line('Init Docment capacity... Please Wait');
        (new UserStorage())->initAllRootNodes();
        $this->line('Init Docment capacity Finished');
        //console
        $this->line('Init... Please Wait');

        // $docSum = (new UserStorage())->countAllRootNodes();

        $groupArr=Group::join('chatmessages','chatmessages.group_id','=','groups.id')->select('group_id as id', 'name' ,'kind','parent_id')->groupBy('group_id')->get();

        $bar = $this->output->createProgressBar(count($groupArr));

        $bar->start();

        foreach ($groupArr as $key=> $group) {
            Log::info('id'.$group->id);

            $chatMessage = ChatMessage::where('group_id', '=', $group->id)->get();
            $userArr = DB::table('chatgroups')->where('group_id', $group->id)->pluck('user_id');
            foreach ($userArr as $k => $v) {
                $messageSize = 0;
                $fileSize = 0;
                foreach ($chatMessage as $key => $value) {
                    if ($value->from_user_id == $v) {
                        $messageSize += strlen($value->message);
                        $fileArr = explode(",", $value->file_name);
                        foreach ($fileArr as $item) {
                            $file = explode(":", $item);
                            if($file){
                                $fileSizeVal=ChatFile::where('file_name',$file[0])->where('group_id',$group->id)->value('file_size');
//                                if(!$fileSizeVal){
//                                    $fileSizeVal = Common::getFileSize($group->id, $file[0]);
//                                }
                                $fileSize+=$fileSizeVal;
                            }
                        }
                    }
                }
                if ($messageSize || $fileSize) {
                    $userStorage=UserStorage::where('user_id', $v)->where('group_id',$group->id)->first();
                    if(!$userStorage){
                        $capacity=UserStorage::userCapacity($group,$v);
                        if(!$capacity['type']){
                            break;
                        }
                        $userStorage=new UserStorage();
                        $userStorage->group_id=$group->id;
                        $userStorage->user_id=$v;
                        $userStorage->enterprise_id=$capacity['enterprise_id'];
                        $userStorage->other_enterprise_id=$capacity['other_enterprise_id'];
                        $userStorage->project_id=$capacity['project_id'];
                        $userStorage->type=$capacity['type'];
                    }
                    $allSize = $messageSize + $fileSize;
                    //会社の容量
                    $enterprise_id = $userStorage->other_enterprise_id;
                    $enterprise = Enterprise::where('id', $enterprise_id)->first();
                    if ($enterprise) {
                        $enterprise->usedStorage += $allSize;
                        $enterprise->save();
                        //個人の容量
                        $userStorage->chat_storage += $messageSize;
                        $userStorage->chat_file_storage += $fileSize;
                        $userStorage->save();
                    }
                }
            }

            $bar->advance();
        }
        $bar->finish();
        $this->line('Init Finished');
        } else {
            $this->line('Operation Abort');
        }
    }
}
