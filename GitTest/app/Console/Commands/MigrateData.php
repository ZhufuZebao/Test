<?php

namespace App\Console\Commands;

use App\Models\CustomerOffice;
use App\Models\Project;
use App\Models\ProjectCustomer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mData{name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data';

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
        $name = $this->argument("name");
        switch ($name) {
            //php artisan mData project_customer_office
            case 'project_customer_office':
                return $this->addData();
                break;
            //php artisan mData project_and_customer_transfer_tel
            case 'project_and_customer_transfer_tel':
                return $this->transfer_tel();
                break;
            default;
        }
    }

    private function transfer_tel()
    {
        $word = '-';
        $projects = Project::where('tel', "like", "%{$word}%")->get()->toArray();
        $customerOffices = CustomerOffice::where('tel', "like", "%{$word}%")->get()->toArray();
        DB::beginTransaction();
        try {
            foreach ($projects as $project) {
                $item = Project::find($project['id']);
                $item->tel = explode('-', $project['tel'])[0];
                if (strstr($project['tel'], '-')) {
                    $item->tel_in = explode('-', $project['tel'])[1];
                } else {
                    $item->tel_in = '';
                }
                $item->save();
            }

            foreach ($customerOffices as $customerOffice) {
                $item = CustomerOffice::find($customerOffice['id']);
                $item->tel = explode('-', $customerOffice['tel'])[0];
                if (strstr($customerOffice['tel'], '-')) {
                    $item->tel_in = explode('-', $customerOffice['tel'])[1];
                } else {
                    $item->tel_in = '';
                }
                $item->save();
            }
            DB::commit();
        } catch (\PDOException $e) {
            // データベースエラー
            Log::error(trans('messages.error.update'));
            DB::rollBack();
        }
    }

    private function addData()
    {
        $sql = <<<EOF
select p.id from projects p
  inner join projects_customers c 
  on c.project_id = p.id
  and c.customer_id = p.customer_id
  and c.office_id = p.customer_office_id;
EOF;
        $idArr = [];
        $items = DB::select($sql);
        foreach ($items as $item){
            $idArr[] = $item->id;
        }
        $project = Project::whereNotIn('id',$idArr)->whereNull('deleted_at')->get(['id','customer_id','customer_office_id'])->toArray();
        DB::beginTransaction();
        try {
            foreach ($project as $item) {
                if ($item['customer_id']){
                    $projectCustomer = new ProjectCustomer();
                    $projectCustomer->project_id = $item['id'];
                    $projectCustomer->customer_id = $item['customer_id'];
                    $projectCustomer->office_id = $item['customer_office_id'];
                    $projectCustomer->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            // データベースエラー
            Log::error(trans('messages.error.insert'));
            DB::rollBack();
        }
    }
}
