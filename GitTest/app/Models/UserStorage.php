<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\Log;

    use App\Models\Node;
    use App\Models\NodeLog;

    class UserStorage extends Model
    {
        use SoftDeletes;
        protected $table = "user_storages";
        protected $guarded = ['id'];

        //the relationship pattern
        const TYPE = array(
            'UNKNOWEN' => 0, //not only or can not point out
            'ENTERPRISE' => 1, //internal
            'COPENTERPRISE' => 2, //co-operation enterprise
            'WORKER' => 3, //worker
            'PROJECT' => 4, //based on project,including chat-group and doc-mgr
        );



        /**
         * 個人への容量統計
         */
        static function userCapacity($group,$userId=''){
            try{
                if(!$userId){
                    $userId=Auth::id();
                }
                $userModel=DB::table('users')->where('id',$userId)->first();
                if(!$userModel){
                    return ['type'=>0];
                }
                $enterprise_id=$userModel->enterprise_id;
                $other_enterprise_id=0;
                if($group->parent_id){
                    //案件サブグループ
                    $project_id=Project::where('group_id',$group->parent_id)->value('id');
                }else{
                    $project_id=Project::where('group_id',$group->id)->value('id');
                }
                $created_user=DB::table('users')->leftJoin('chatgroups', 'chatgroups.user_id', '=', 'users.id')
                    ->where('chatgroups.group_id',$group->id)
                    ->where('chatgroups.admin',1)
                    ->select('users.id','users.name','users.enterprise_id','users.coop_enterprise_id','users.worker')->first();
                if($project_id){//案件
                    $other_enterprise_id=$created_user->enterprise_id;
                    $type=4;
                } else {
                    //協力会社
                    $coopInvite = EnterpriseParticipant::where('user_id', $userModel->id)
                        ->where('agree', 1)
                        ->pluck('enterprise_id')->toArray();
                    if (count($coopInvite) == 1) {
                        $type=2;
                        $other_enterprise_id=$coopInvite[0];
                    } else {
                        //事業者
                        if($userModel->enterprise_id){
                            $type=1;
                            $other_enterprise_id=$userModel->enterprise_id;
                        }else{
                            //職人
                            $chatContact = ChatContact::where('to_user_id', $userModel->id)
                                ->where('contact_agree', 1)->pluck('from_user_id')->toArray();
                            if(count($chatContact) == 1){
                                $user=DB::table('users')->where('id',$chatContact[0])->first();
                                $other_enterprise_id=$user->enterprise_id;
                                $type=3;
                                //協力元が特定できるデータは協力元側に計上し
                                if(!$user->enterprise_id){
                                    $coopInvite = EnterpriseParticipant::where('user_id', $user->id)
                                        ->where('agree', 1)
                                        ->pluck('enterprise_id')->toArray();
                                    if (count($coopInvite) == 1) {
                                        $type=2;
                                        $other_enterprise_id=$coopInvite[0];
                                    } else {
                                        $type=0;
                                    }
                                }
                            }else{
                                if($group->kind){
                                    $created_user = DB::table('users')->leftJoin('chatgroups', 'chatgroups.user_id', '=', 'users.id')
                                        ->where('chatgroups.group_id', $group->id)
                                        ->where('chatgroups.user_id', '<>', $userId)
                                        ->select('users.id', 'users.name', 'users.enterprise_id', 'users.coop_enterprise_id', 'users.worker')->first();
                                }
                                //職人アカウント
                                if($userModel->worker&&!$userModel->enterprise_id&&!$userModel->coop_enterprise_id&&$created_user->enterprise_id){
                                    $type=3;
                                    $other_enterprise_id=$created_user->enterprise_id;
                                }else{
                                    $type=0;
                                }
                            }
                        }
                    }

                }
                $arr=['enterprise_id'=>$enterprise_id,'other_enterprise_id'=>$other_enterprise_id,'project_id'=>$project_id,'type'=>$type];
                return $arr;
            } catch (\Exception $e) {
                Log::info($e);
                return ['type'=>0];
            }
        }

        private function docStorageRestore($projectId) {
            $nodes = Node::findAll($projectId);
            $nodes = array_map(function ($e) {
                return $this->toNodeJson($e);
            }, $nodes);

            //find roor dirs
            $rootDirs = [];
            foreach($nodes as $node) {
                if($node['parent'] === null && $node['type'] === 0) {
                    $rootDirs[] = $node['id'];
                }
            }

            foreach($rootDirs as $rootId) {
                $this->initDocNode($nodes, $rootId, $projectId);
            }
        }

        private function initDocNode($nodes, $nodeId, $projectId) {
            $nodeChildNodes = $this->getChildren($nodeId, $nodes);

            $projectInfo = $this->getProjectInformation($projectId);

             //used storage will restore
             $storageAll = 0; // enterprise storage
             $storageUser = []; //node include more than one user's storage

             //get all nodes' rev
            $doc_db = config('const.db_database_doc');
            $revs = DB::table("$doc_db.revs")
                ->whereIn('node_id', $nodeChildNodes)
                ->whereNull('deleted_at')
                ->select(['file_size', 'user_id_commit'])
                ->get();

            foreach($revs as $revItem) {
                $storageAll += $revItem->file_size;
                $userKeyIndex = 'u' . $revItem->user_id_commit;
                if(array_key_exists($userKeyIndex, $storageUser)) {
                    $storageUser[$userKeyIndex] += $revItem->file_size;
                } else {
                    $storageUser[$userKeyIndex] = $revItem->file_size;
                }
            }

            try {
                DB::beginTransaction();

                //restore
                $userIndexArray = array_keys($storageUser);
                for($i=0; $i<count($userIndexArray); $i++) {
                    $userIdTmp = substr($userIndexArray[$i], 1); //get userid
                    $userInfoArr = $this->getStorageCountOtherUser($userIdTmp);
                    $pattern = $this->checkPattern($projectId, $userIdTmp);
                    $this->changeStorage($userIdTmp, null, $userInfoArr['enterpriseId'], $pattern['otherEnterpriseId'], $projectId, $storageUser[$userIndexArray[$i]]);
                }
                $this->changeEnterpriseStorage($projectInfo->enterprise_id, $projectInfo->enterprise_id, $storageAll);

                DB::commit();

            } catch (\Exception $e) {
                Log::error($e);
                DB::rollback();
            }
        }

        //check pattern
        private function checkPattern($projectId, $userId) {
            $storagePattern = [];

            $projectInfo = $this->getProjectInformation($projectId);
            $userInfo = $this->getStorageCountOtherUser($userId);

            //this doc dir belong to project
            $storagePattern['userId'] = $userId;
            $storagePattern['groupId'] = null;

            $storagePattern['enterpriseId'] = $userInfo['enterpriseId'];
            $storagePattern['otherEnterpriseId'] = $projectInfo->enterprise_id;
            $storagePattern['projectId'] = $projectId;

            return $storagePattern;
        }

        //change storage record
        private function changeStorage($userId, $groupId = null, $enterpriseId, $otherEnterpriseId, $projectId, $storage) {
            //get storage count now
            $storageInfo = DB::table('user_storages')
                ->where('user_id', $userId)
                ->where('enterprise_id', $enterpriseId)
                ->where('other_enterprise_id', $otherEnterpriseId)
                ->where('project_id', $projectId)
                ->whereNull('group_id')
                ->where('type', self::TYPE['PROJECT'])
                ->first();

            $storagePrev = 0;

            if($storageInfo) {
                //data exists
                $storagePrev = $storageInfo->doc_storage;
            }

            $whereRule = array(
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'other_enterprise_id' => $otherEnterpriseId,
                'group_id' => null,
                'project_id' => $projectId,
                'type' => self::TYPE['PROJECT']
            );

            $nxtUsedStorage = $storagePrev + $storage;
            if($nxtUsedStorage < 0) {
                $nxtUsedStorage = 0;
            }


            $flag = DB::table('user_storages')
                ->updateOrInsert($whereRule,['doc_storage' => $nxtUsedStorage, 'created_at' => NOW(), 'updated_at' => NOW()]);

            return $flag;
        }

        //change enterprise storage record
        private function changeEnterpriseStorage($enterpriseId, $otherEnterpriseId, $storage) {
            $id = $enterpriseId;

            if($otherEnterpriseId) {
                $id = $otherEnterpriseId;
            }

            if($storage > 0) {
                $flag = DB::table('enterprises')
                    ->where('id', $id)
                    ->increment('usedStorage', $storage);
            } else {
                $flag = DB::table('enterprises')
                    ->where('id', $id)
                    ->decrement('usedStorage', -$storage);
            }

            return $flag;
        }

        //get user information
        private function getStorageCountOtherUser($otherUserId) {
            $storageRefer = [];

            $user = DB::table('users')
                ->where('id', $otherUserId)
                // ->whereNull('deleted_at') //including deleted
                ->first();

            $storageRefer['enterpriseId'] = $user->enterprise_id;
            $storageRefer['userId'] = $user->id;

            return $storageRefer;
        }

        //get a node's child nodes all
        private function getChildren($p_id, $array)
        {
            $subs = array();
            foreach ($array as $item) {
                if ($item['parent'] == $p_id) {
                    $subs[] = $item['id'];
                    $subs = array_merge($subs, $this->getChildren($item['id'], $array));
                }
            }
            return $subs;
        }

        private function toNodeJson($row)
        {
            return [
                'id' => (int)$row->node_id,
                'parent' => is_null($row->node_id_parent) ? null : (int)$row->node_id_parent,
                'name' => $row->node_name,
                'type' => (int)$row->node_type,
                'owner' => $row->owner,
                'locker' => $row->locker,
                'lockUser' => (int)$row->user_id_lock,
                'lockTime' => $row->lock_time,
                'size' => (int)$row->file_size,
                'time' => $row->last_updated,
                'deleted' => (bool)$row->deleted_at,
                'rev_no' => is_null($row->rev_no) ? null : (int)$row->rev_no,
                'file_name' => $row->file_name,
            ];
        }

        //get project information
        private function getProjectInformation($projectId) {
            $info = DB::table('projects')
                ->where('id', $projectId)
                // ->whereNull('deleted_at')
                ->first();

            if($info) {
                //exists
                return $info;
            } else {
                //not exits or been deleted
                return false;
            }
        }

        //get all nodes count
        private function countAllRootNodes() {
            $doc_db = config('const.db_database_doc');
            $nodes = DB::table("$doc_db.nodes")
                ->whereNull('node_id_parent')
                ->whereNull('deleted_at')
                ->get();

            $projectIdArr = [];
            $enterpriseIdArr = [];

            foreach($nodes as $node) {
                if($node->project_id) {
                    $projectIdArr[] = $node->project_id;
                }
                if($node->enterprise_id) {
                    $enterpriseIdArr[] = $node->enterprise_id;
                }
            }
            $sum = count($projectIdArr) + count($enterpriseIdArr);
            return $sum;
        }

        //get all nodes
        public function initAllRootNodes() {
            $doc_db = config('const.db_database_doc');
            $nodes = DB::table("$doc_db.nodes")
                ->whereNull('node_id_parent')
                ->whereNull('deleted_at')
                ->get();

            $projectIdArr = [];
            $enterpriseIdArr = [];

            foreach($nodes as $node) {
                if($node->project_id) {
                    $projectIdArr[] = $node->project_id;
                }
                if($node->enterprise_id) {
                    $enterpriseIdArr[] = $node->enterprise_id;
                }
            }
            foreach($projectIdArr as $projectId) {
                $this->initProject($projectId);
            }
            foreach($enterpriseIdArr as $enterpriseId) {
                $this->initInternal($enterpriseId);
            }
        }

        //init project
        private function initProject($projectId) {
            $this->docStorageRestore($projectId);
        }

        //init internal
        private function initInternal($enterpriseId) {
            //get all enterprise node
            $doc_db = config('const.db_database_doc');
            $nodes = DB::table("$doc_db.nodes")
                ->where('enterprise_id', $enterpriseId)
                ->whereNull('deleted_at')
                ->get();
            //get all file node
            $nodeFiles = [];
            foreach($nodes as $node) {
                if($node->node_type === 1) {
                    $nodeFiles[] = $node->node_id;
                }
            }
            //get all rev
            $revs = DB::table("$doc_db.revs")
                ->whereIn('node_id', $nodeFiles)
                ->whereNull('deleted_at')
                ->get();
            //get all account
            $accounts = DB::table('users')
                ->where('enterprise_id', $enterpriseId)
                ->get();// including deleted
            $accountsStorage = [];
            //init storage
            $enterpriseStorage =0;
            foreach($revs as $rev) {
                $enterpriseStorage += $rev->file_size;
                $accountsStorage = $this->addToData($accountsStorage, $rev->user_id_commit, $rev->file_size);
            }
            //change enterprise data
            $this->changeEnterpriseStorage($enterpriseId, $enterpriseId, $enterpriseStorage);
            foreach($accountsStorage as $storageItem) {
                $this->addToUserDocStorage($storageItem['userId'], $enterpriseId, $storageItem['storage']);
            }
        }

        //add to data
        private function addToData($data, $userId, $size) {
            foreach($data as $key => $dataItem) {
                if($dataItem['userId'] === $userId) {
                    $data[$key]['storage'] += $size;
                    return $data;
                }
            }
            $dataItemArr = array(
                'userId' => $userId,
                'storage' => $size
            );
            $data[] = $dataItemArr;
            return $data;
        }

        //update user doc storage
        private function addToUserDocStorage($userId, $enterpriseId, $size) {
            $storageInfo = DB::table('user_storages')
                ->where('user_id', $userId)
                ->where('enterprise_id', $enterpriseId)
                ->where('other_enterprise_id', $enterpriseId)
                ->where('project_id', null)
                ->whereNull('group_id')
                ->where('type', self::TYPE['ENTERPRISE'])
                ->first();

            $storagePrev = 0;

            if($storageInfo) {
                //data exists
                $storagePrev = $storageInfo->doc_storage;
            }
            $whereRule = array(
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'other_enterprise_id' => $enterpriseId,
                'group_id' => null,
                'project_id' => null,
                'type' => self::TYPE['ENTERPRISE']
            );

            $nxtUsedStorage = $storagePrev + $size;

            DB::table('user_storages')
                ->updateOrInsert($whereRule,['doc_storage' => $nxtUsedStorage, 'created_at' => NOW(), 'updated_at' => NOW()]);
        }
    }
