<?php
namespace App\Http\Controllers\Web;

use App\Http\Facades\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\User;
use App\Models\Group;
use App\Models\ChatContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Node;
use App\Models\NodeInternal;
use App\Models\Rev;
use ZipArchive;
use Illuminate\Support\Facades\Log;

class DocController extends \App\Http\Controllers\Controller
{
    const PATH_PATTERN = array(
        'Local' => 1, // It means the file's path is document path mode
        'Chat' => 2, //the other value means the file is other path mode ,like chatGroup.
        'Zip' => 3, //local zip file
    );

    const FILES_PATH_PREFIX = 'files';

    const GROUP_TYPE = array(
        'Single' => 1,
        'Project' => 2,
    );

    const USER_TYPE = array(
        'Enterprise' => 1,
        'CoopEnterprise' => 2,
        'Worker' => 3,
    );

    const RANGE_TYPE = array(
        'Internal' => 1,
        'SingleProject' => 2,
        'ShareProject' =>3,
        'EnterpriseProject' =>4,
    );

    const SELECTED_RANGE = array(
        '1-1' => [self::RANGE_TYPE['Internal'], self::RANGE_TYPE['ShareProject']],
        '1-2' => [self::RANGE_TYPE['ShareProject']],
        '1-3' => [],//APP only
        '2-1' => [self::RANGE_TYPE['Internal'], self::RANGE_TYPE['SingleProject']],
        '2-2' => [self::RANGE_TYPE['SingleProject']],
        '2-3' => [],//APP only
    );

    //get doc file
    public function getFileFromDoc(Request $request) {
        $groupId=$request->input('groupId');
        $enterpriseId = Auth::user()->enterprise_id;
        $coopEnterpriseId = Auth::user()->coop_enterprise_id;
        $userType = 0;
        //check user type
        if($enterpriseId) {
            $userType = self::USER_TYPE['Enterprise'];
        } elseif($coopEnterpriseId) {
            $userType = self::USER_TYPE['CoopEnterprise'];
        }

        if(!$userType) {
            return response()->json(['flag' => 1, 'data' => []]);
        }

        //check group type
        $groupType = 0;
        $groupInfo = DB::table('groups')
            ->where('id', $groupId)
            ->whereNull('deleted_at')
            ->first();
        if($groupInfo->kind == 1) {
            $groupType = self::GROUP_TYPE['Single'];
        }else{
            $groupParentId = $groupInfo->parent_id;
            $gId = $groupId;
            if($groupParentId) {
                $gId = $groupParentId;
            }
            $isProject = DB::table('projects')
                    ->where('group_id', $gId)
                    ->whereNull('deleted_at')
                    ->count();
                if($isProject) {
                    $groupType = self::GROUP_TYPE['Project'];
                }else{
                    return response()->json(['flag' => 0, 'data' => []]);
                }
            }
            //get select range
            $typeStr = $groupType . '-' . $userType;
            $range = self::SELECTED_RANGE[$typeStr];

            $nodesAll = [];

            //internal
            if(in_array(self::RANGE_TYPE['Internal'], $range)) {
                $nodes = NodeInternal::findAll($enterpriseId);
                $nodes = array_map(function ($e) {
                    return $this->toNodeJson($e);
                }, $nodes);
                foreach($nodes as $nodeInternal) {
                    $nodesAll[] = $nodeInternal;
                }
            }

            //share project
            if(in_array(self::RANGE_TYPE['ShareProject'], $range)) {
                //get project files
                $projectIdArr = $this->getGroupsProject($groupId);
                $nodes = $this->getSingleProjectChatGroupFiles($projectIdArr, $groupId);
                foreach($nodes as $nodeShareProjectChat) {
                    $nodesAll[] = $nodeShareProjectChat;
                }
            }
            //single Project Group
            if(in_array(self::RANGE_TYPE['SingleProject'], $range)) {
                //get project files
                $nodes = $this->getSingleProject($groupId);
                $project=Project::where('group_id',$groupId)->first();
                if($project){
                    $doc_node=Node::findAll($project->id);
                    foreach ($doc_node as $key => $val){
                        $node=$this->toNodeJson($val);
                        if(!$node['node_id_parent']){
                            $node['name']=$node['name'].'('.$project->place_name.')';
                        }
                        $nodes[]=$node;
                    }
                }
                foreach($nodes as $nodeSingleProjectChat) {
                    $nodesAll[] = $nodeSingleProjectChat;
                }
            }
        
        return response()->json(['flag' => 1, 'data' => $nodesAll]);
    }

    //get single project chat group files
    private function getSingleProject($groupId) {
        $nodes = [];
        //check if this is a parent group
        $groupInfo = DB::table('groups')
            ->where('id', $groupId)
            ->whereNull('deleted_at')
            ->first();
        if($groupInfo->parent_id) {
            //is child group
            $nodes[] = $this->makeNodeItemByGroup($groupInfo);
            $nodes[0]['parent'] = 0;
            //get all files belong to group
            $files = DB::table("chat_files")
                ->leftJoin("users", "users.id", "=", "chat_files.upload_user_id")
                ->select([
                    "chat_files.id",
                    "chat_files.group_id",
                    "chat_files.file_name",
                    "chat_files.file_size",
                    "chat_files.created_at",
                    "users.name as uploadUserName"
                ])
                ->whereIn("chat_files.group_id", [$groupId])
                ->whereNull("chat_files.deleted_at")
                ->whereNull("users.deleted_at")
                ->get();
            foreach ($files as $file) {
                $nodes[] = $this->makeNodeItemByFile($file);
            }
        } else {
            //is main group
            $nodes[] = $this->makeNodeItemByGroup($groupInfo);
            $nodes[0]['parent'] = 0;
            //get all files belong to group
            $files = DB::table("chat_files")
                ->leftJoin("users", "users.id", "=", "chat_files.upload_user_id")
                ->select([
                    "chat_files.id",
                    "chat_files.group_id",
                    "chat_files.file_name",
                    "chat_files.file_size",
                    "chat_files.created_at",
                    "users.name as uploadUserName"
                ])
                ->whereIn("chat_files.group_id", [$groupId])
                ->whereNull("chat_files.deleted_at")
                ->whereNull("users.deleted_at")
                ->get();
            foreach ($files as $file) {
                $nodes[] = $this->makeNodeItemByFile($file);
            }
        }
        return $nodes;
    }

    //single-chat : get share project-chat-group files
    private function getSingleProjectChatGroupFiles($projectIdArr, $groupId) {
        //get group's user
        $groupsUsers = DB::table('chatgroups')
            ->where('group_id', $groupId)
            ->whereNull('deleted_at')
            ->select(['user_id'])
            ->get();
        $users = [];
        foreach($groupsUsers as $userId) {
            $users[] = $userId->user_id;
        }
        //user1's groups
        $user1Groups = $this->getUsersGroups($users[0]);
        //user2's groups
        $user2Groups = $this->getUsersGroups($users[1]);
        //get projects main group and children groups
        $mainGroups = $this->getProjectMainGroup($projectIdArr);
        $allProjectsGroups = $this->getProjectChildrenGroup($mainGroups);
        //groups 2 all in
        $allGroups = array_intersect($allProjectsGroups, $user1Groups, $user2Groups);

        $nodes = [];

        //get mainGroup remains
        $mainGroupRemains = [];
        foreach($mainGroups as $groupRemains) {
            if(in_array($groupRemains, $allGroups)) {
                $mainGroupRemains[] = $groupRemains;
            }
        }

        $mainGroups = $mainGroupRemains;

        foreach($mainGroups as $mainGroupsIdItem) {
            //get groups which include the user logging in
            //get main group info
            $mainGroup = DB::table("groups")
                ->where('id', $mainGroupsIdItem)
                ->whereNull('deleted_at')
                ->first();
            $nodes[] = $this->makeNodeItemByGroup($mainGroup);
            //check if there are child-group
            $childGroup = DB::table("groups")
                ->where('parent_id', $mainGroupsIdItem)
                ->whereIn('id', $allGroups)
                ->whereNull('deleted_at')
                ->get();
            $groupChild = array();
            foreach ($childGroup as $group) {
                $groupChild[] = $group->id;
                $nodes[] = $this->makeNodeItemByGroup($group);
            }

            $groupChild[] = $mainGroupsIdItem;
            //get all files belong to group
            $files = DB::table("chat_files")
                ->leftJoin("users", "users.id", "=", "chat_files.upload_user_id")
                ->select([
                    "chat_files.id",
                    "chat_files.group_id",
                    "chat_files.file_name",
                    "chat_files.file_size",
                    "chat_files.created_at",
                    "users.name as uploadUserName"
                ])
                ->whereIn("chat_files.group_id", $groupChild)
                ->whereNull("chat_files.deleted_at")
                ->whereNull("users.deleted_at")
                ->get();
            foreach ($files as $file) {
                $nodes[] = $this->makeNodeItemByFile($file);
            }
        }
        foreach ($projectIdArr as $value){
            $project_name=Project::where('id',$value)->value('place_name');
            $doc_node=Node::findAll($value);
            foreach ($doc_node as $key => $val){
                $node=$this->toNodeJson($val);
                if(!$node['node_id_parent']){
                    $node['name']=$node['name'].'('.$project_name.')';
                }
                $nodes[]=$node;
            }
        }

        return $nodes;
    }

    //get project's main group
    private function getProjectMainGroup($projectIds) {
        $project = DB::table("projects")
            ->whereIn("id", $projectIds)
            ->whereNull("deleted_at")
            ->select([
                "group_id",
            ])
            ->get();

        $groupsId = [];

        foreach($project as $p) {
            $groupsId[] = $p->group_id;
        }

        return $groupsId;
    }

    //get projects's children group
    private function getProjectChildrenGroup($mainGroupId) {
        $groups = [];

        for($i=0;$i<count($mainGroupId);$i++) {
            $groups[] = $mainGroupId[$i];
        }

        $groupChildren = DB::table('groups')
            ->whereIn('parent_id', $mainGroupId)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();
        
        for($i=0;$i<count($groupChildren);$i++) {
            $groups[] = $groupChildren[$i];
        }

        return $groups;
    }

    //get user's groups
    private function getUsersGroups($userId) {
        $groups =DB::table('chatgroups')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->select(['group_id'])
            ->get();
        $groupIdArr = [];
        foreach($groups as $groupItem) {
            $groupIdArr[] = $groupItem->group_id;
        }
        array_unique($groupIdArr);
        return $groupIdArr;
    }

    //get group's project
    private function getGroupsProject($groupId) {
        //get group's user
        $groupsUsers = DB::table('chatgroups')
            ->where('group_id', $groupId)
            ->whereNull('deleted_at')
            ->select(['user_id'])
            ->get();
        $users = [];
        foreach($groupsUsers as $userId) {
            $users[] = $userId->user_id;
        }
        if(count($users) > 2 || !count($users)) {
            return [];
        }
        //get project
        $UserProjects = DB::table('project_participants')
            ->where('user_id', $users[0])
            ->whereNull('deleted_at')
            ->pluck('project_id')
            ->toArray();
        $projects = DB::table('project_participants')
            ->where('user_id', $users[1])
            ->whereNull('deleted_at')
            ->pluck('project_id')
            ->toArray();
        $result=array_values(array_intersect($UserProjects,$projects));
        $res=[];
        //職人は調べられません
        foreach ($result as $key => $value){
              $project_enterprise_id=Project::where('id',$value)->value('enterprise_id');
              $users=User::where('enterprise_id',$project_enterprise_id)->pluck('id')->toArray();
              $chatContact=Chatcontact::whereIn('from_user_id',$users)->where('contact_agree',1)->pluck('to_user_id')->toArray();
              if(!in_array(Auth::id(),$chatContact)){
                  $res[]=$value;
              }
        }
        return $res;
    }

    private function toNodeJson($row){
        return [
            'id' => (int)$row->node_id,
            'node_id' => (int)$row->node_id,
            'node_id_parent'=>is_null($row->node_id_parent) ? 0 : $row->node_id_parent,
            'parent' => is_null($row->node_id_parent) ? 0 : (int)$row->node_id_parent,
            'name' => $row->node_name,
            'node_name' => $row->node_name,
            'node_type' => (int)$row->node_type,
            'type' => (int)$row->node_type,
            'owner' => $row->owner,
            'size' => (int)$row->file_size ? (int)$row->file_size : 0,
            'time' => $row->last_updated,
            'revNo' => is_null($row->rev_no) ? 0 : (int)$row->rev_no,
            'fileName' => $row->file_name ? $row->file_name : '',
            'fileType' => (int)$row->node_type > 0 ? $this->checkType($row->node_name) : '',
        ];
    }

    //get a node's child nodes all
    private function getChildren($p_id, $array){
        $subs = array();
        foreach ($array as $item) {
            if ($item['parent'] == $p_id) {
                $subs[] = $item['id'];
                $subs = array_merge($subs, $this->getChildren($item['id'], $array));
            }
        }
        return $subs;
    }

    //get a node's parent nodes all
    private function getParent($p_id, $array){
        $subs = array();
        foreach ($array as $item) {
            if ($item['id'] == $p_id) {
                $subs[] = $item['id'];
                $subs = array_merge($subs, $this->getParent($item['parent'], $array));
            }
        }
        return $subs;
    }

    private function makeNodeItemByGroup($groupInfo){
        $arr = array();
        //get group admin
        $owner = $this->getGroupAdmin($groupInfo->id);
        if ($groupInfo->parent_id) {
            $groupInfo->parent_id = -$groupInfo->parent_id;
        } else {
            $groupInfo->parent_id = null;
        }
        $arr['id'] = -$groupInfo->id;
        $arr['node_id'] = -$groupInfo->id;
        $arr['parent'] = $groupInfo->parent_id ? $groupInfo->parent_id : 0;
        $arr['node_id_parent'] = $groupInfo->parent_id ? $groupInfo->parent_id : 0;
        $arr['name'] = $groupInfo->name;
        $arr['node_name'] = $groupInfo->name;
        $arr['type'] = 0;
        $arr['node_type'] = 0;
        $arr['owner'] = $owner;
        $arr['size'] = 0;
        $arr['time'] = $groupInfo->created_at;
        $arr['revNo'] = 0;
        $arr['fileName'] = '';
        $arr['fileType'] = '';
        return $arr;
    }

    private function getGroupAdmin($group_id){
        $userInfo = DB::table("chatgroups")
            ->leftJoin("users", "users.id", "=", "chatgroups.user_id")
            ->select([
                "users.name as userName"
            ])
            ->where("chatgroups.group_id", $group_id)
            ->where("chatgroups.admin", 1)
            ->whereNull("chatgroups.deleted_at")
            ->whereNull("users.deleted_at")
            ->first();
        if ($userInfo) {
            return $userInfo->userName;
        } else {
            return '未知';
        }
    }

    private function makeNodeItemByFile($file){
        $arr = array();
        $arr['id'] = -$file->id;
        $arr['node_id'] = -$file->id;
        $arr['parent'] = -$file->group_id;
        $arr['node_id_parent'] = is_null($file->group_id) ? 0 : -$file->group_id;
        $arr['name'] = $file->file_name;
        $arr['node_name'] = $file->file_name;
        $arr['type'] = 1;
        $arr['node_type'] = 1;
        $arr['owner'] = $file->uploadUserName;
        $arr['size'] = $file->file_size;
        $arr['time'] = $file->created_at;
        $arr['revNo'] = 0;
        $arr['fileName'] = $file->file_name;
        $arr['fileType'] = $this->checkType($file->file_name);
        return $arr;
    }

    private function checkType($fileName)
    {
        $arr = explode('.', $fileName);
        if (count($arr) > 1) {
            return strtoupper(end($arr));
        } else {
            return '';
        }
    }

    private function filterRootFolder($rootFolder,$path){
        if($rootFolder) {
            $length = strlen($rootFolder.'/');
            return substr_replace($path, '', 0, $length);
        } else {
            return $path;
        }
    }

    private function getRootPathFolder($pathArr,$nodeId) {
        foreach($pathArr as $path) {
            if($path['node_id'] === $nodeId) {
                return $path['node_name'];
            }
        }
    }

    private function flitSelected($selectedStr)
    {
        $arr = [];
        foreach ($selectedStr as $itemSelected) {
            $arr[] = (int)$itemSelected;
        }
        return $arr;
    }

    private function makeDirChat($arr, $item)
    {
        if ($item['node_id_parent']) {
            $item['node_name'] = $this->getFileBaseDir($arr, $item['node_id_parent']) . '/' . $item['node_name'];
        }
        return $item;
    }

    private function flitDuplicateFile($arr, $nodeId) {
        $arrTmp = [];
        foreach($arr as $checkItem) {
            if($checkItem['node_id'] !== $nodeId) {
                $arrTmp[] = $checkItem;
            }
        }
        return $arrTmp;
    }

    //選択されたファイルを取得する
    private function getFileFilter($selectedFileId, $allFileNode)
    {
        $result = [];

        foreach ($allFileNode as $fileNode) {
            if (in_array($fileNode['node_id'], $selectedFileId)) {
                $result[] = $fileNode;
            }
        }

        return $result;
    }

    //1つのノードのすべての下位層を取得し,自身を含む
    private function getChildDirNode($dirArr, $dirId)
    {
        $dirTarget = [];

        $dirTarget[] = $dirId;

        $flagToLoop = true;
        while ($flagToLoop) {
            $flagToLoop = false;
            foreach ($dirArr as $keyIndex => $itemTarget) {
                if (in_array($itemTarget['node_id_parent'], $dirTarget)) {
                    $dirTarget[] = $itemTarget['node_id'];
                    array_splice($dirArr, $keyIndex, 1);
                    $flagToLoop = true;
                }
            }
        }

        return $dirTarget;
    }


    private function getFilePathToDownload($node)
    {
        $filePath = implode('/',
            [self::FILES_PATH_PREFIX, $node['node_id'], $node['rev_no'], $node['file_name']]);
        return $filePath;
    }

    private function getFileBaseDir($baseDirArr, $fileParentId)
    {
        foreach ($baseDirArr as $item) {
            if ($item['node_id'] == $fileParentId) {
                return $item['node_name'];
            }
        }
    }

    private function childNodeElement($node, $node_parent)
    {
        if ($node['node_id_parent'] == $node_parent['node_id']) {
            $node['node_name'] = $node_parent['node_name'] . '/' . $node['node_name'];
            return $node;
        }
    }

    private function getNodeElement($nodes, $node_id)
    {
        foreach ($nodes as $node) {
            if ($node['node_id'] == $node_id) {
                return $node;
            }
        }
    }

    private function getParentDir($dirs)
    {
        $parentDirIds = [];
        foreach ($dirs as $item) {
            if (!$item['node_id_parent']) {
                $parentDirIds[] = $item['node_id'];
            }
        }
        return $parentDirIds;
    }

    private function getDirItem($nodes)
    {
        $dirs = [];
        $files = [];
        foreach ($nodes as $item) {
            if ($item['node_type']) {
                $files[] = $item;
            } else {
                $dirs[] = $item;
            }
        }
        $arr['dirs'] = $dirs;
        $arr['files'] = $files;
        return $arr;
    }

    //get chat group file's S3 path
    private function getSingleChatGroupFilePath($nodeId)
    {
        //make s3 path
        $fileS3Path = '';

        $fileInfo = DB::table("chat_files")
            ->where("id", $nodeId)
            ->first();
        if ($fileInfo) {
            Log::info(__METHOD__ . ': chat-group file not exists');
            $groupId = $fileInfo->group_id;
            $fileName = $fileInfo->file_name;
            $fileS3Path = sprintf('upload/g%s/%s', $groupId, $fileName);
        }

        return $fileS3Path;
    }

    //get some group files's S3 path
    private function getChatGroupFilePath($nodeIds)
    {
        //make s3 path
        $fileS3Path = [];

        $fileInfo = DB::table("chat_files")
            ->whereIn("id", $nodeIds)
            ->whereNull('deleted_at')
            ->get();
        if ($fileInfo) {
            foreach ($fileInfo as $groupFileItem) {
                $groupId = $groupFileItem->group_id;
                $fileName = $groupFileItem->file_name;
                $fileS3Path = sprintf('upload/g%s/%s', $groupId, $fileName);
                $arrTmp['node_id'] = $groupFileItem->id;
                $arrTmp['s3_file_path'] = $fileS3Path;
                $fileS3Path[] = $arrTmp;
            }
        }

        return $fileS3Path;
    }

    //get chat group file's info
    private function getChatGroupFileInfo($fileNodeArr, $nodeId)
    {
        $fileInfo = false;

        foreach ($fileNodeArr as $chatFileItem) {
            if ($chatFileItem['node_id'] == $nodeId) {
                $fileInfo = $chatFileItem;
                return $fileInfo;
            }
        }

        return $fileInfo;
    }

    //make child-dir path
    private function addDir($basePath, $path)
    {
        return $basePath . '/' . $path;
    }

    //get same dir files
    private function getSameDirFiles($files, $nodeIdParent)
    {
        $arr = [];
        foreach ($files as $file) {
            if ($file['node_id_parent'] === $nodeIdParent) {
                $arr[] = $file;
            }
        }
        return $arr;
    }

    //check duplicate file name
    private function checkDuplicateFileName($fileNames, $fileName)
    {
        $fileNamesArr = [];
        foreach ($fileNames as $fileNameExi) {
            $fileNamesArr[] = $fileNameExi['file_name'];
        }
        $nameCountArr = array_count_values($fileNamesArr);
        if (in_array($fileName, $fileNamesArr)) {
            return true;
        } else {
            return false;
        }
    }

    //re-name file
    private function renameFileWhenDuplicate($fileName, $sort)
    {
        if ($sort > 0) {
            $typeArr = explode('.', $fileName);
            $type = end($typeArr);
            array_splice($typeArr, -1);
            $newFileName = implode('.', $typeArr) . '(' . $sort . ').' . $type;
            return $newFileName;
        } else {
            return $fileName;
        }
    }

    private function changeZipFilePathName($originPath, $newFileName)
    {
        $arr = explode('/', $originPath);
        $last = count($arr);
        $arr[$last - 1] = $newFileName;
        return implode('/', $arr);
    }

    private function makeNodeItemByFileSimple($file)
    {
        $arr = array();
        $arr['id'] = -$file->id;
        $arr['parent'] = -$file->group_id;
        $arr['name'] = $file->file_name;
        $arr['type'] = 1;
        $arr['owner'] = ''; //todo need re-check
        $arr['size'] = $file->file_size;
        $arr['time'] = $file->created_at;
        $arr['revNo'] = 0;
        $arr['fileName'] = $file->file_name;
        $arr['fileType'] = $this->checkType($file->file_name);
        return $arr;
    }

    //get base informations when attempt to send files 
    public function sendToChat(Request $request) {
        $current = $request->input('current');
        $groupId = $request->input('groupId');
        $db_doc = config('web.db_database_doc');

        $project_id = 0;

        $fileSource = 0; //0 is doc,1 is chat
        
        if($current > 0) {
            $fileSource = 0;
            //check if is project-group
            
            $nodeInfo = DB::table("$db_doc.nodes")
                ->where('node_id', $current)
                ->whereNull('deleted_at')
                ->first();
            if($nodeInfo->project_id) {
                $project_id = $nodeInfo->project_id;
            }
        } else{
            $fileSource = 1;
        }
        //root files/folders can not be selected

        //return data
        $files = []; //files will be sent

        $selectedNodeIds = $request->input('selected');
        $selectedNodeIds = $this->flitSelected($selectedNodeIds);

        //storage setting
        $disk = Storage::disk('s3doc');
        $diskSite = Storage::disk('s3');

        //selected nodes from document
        $documentNodeSelected = [];
        //selected nodes from chatfile
        $chatfileNodeSelected = [];

        foreach ($selectedNodeIds as $selectedNodeId) {
            if ($selectedNodeId > 0) {
                $documentNodeSelected[] = $selectedNodeId;
            } else {
                $chatfileNodeSelected[] = $selectedNodeId;
            }
        }

        //get all nodes from document
        //Get the associated node
        $nodes = [];
        if($current > 0) {
            if($project_id) {
                $nodes = Node::findAll($project_id);
            } else {
                $nodes = NodeInternal::findAll(Auth::user()->enterprise_id);
            }
        }
        
        //Clean up redundant fields
        $nodesArr = [];
        foreach ($nodes as $key => $item) {
            $nodesArr[$key]['node_id'] = $item->node_id;
            $nodesArr[$key]['node_id_parent'] = $item->node_id_parent;
            $nodesArr[$key]['node_type'] = $item->node_type;
            $nodesArr[$key]['node_name'] = $item->node_name;
            $nodesArr[$key]['rev_no'] = $item->rev_no;
            $nodesArr[$key]['file_name'] = $item->file_name;
        }

        //Distinguish node types
        $nodeTmp = $this->getDirItem($nodesArr);

        $nodeDir = $nodeTmp['dirs'];
        $nodeFile = $nodeTmp['files'];

        //Structured directory(Tree)
        $nodeDirSort = [];
        $nodeDirFlat = [];
        $nodeDirFlatPure = [];
        //Structured file
        $nodeFileFlat = [];
        $nodeFileFlatPure = [];

        $parentDirIds = $this->getParentDir($nodeDir);

        $nodeDirSortCount = 0;
        foreach ($parentDirIds as $parentDir) {
            $dirSortItem = $this->getNodeElement($nodeDir, $parentDir);
            $nodeDirSort[0][] = $dirSortItem;
            $nodeDirFlat[] = $dirSortItem;
            $nodeDirFlatPure[] = $dirSortItem['node_id'];
            $nodeDirSortCount++;
        }

        $nodeCount = count($nodeDir);
        $deep = 1;

        while ($nodeDirSortCount < $nodeCount) {
            foreach ($nodeDir as $dirItemSort) {
                foreach ($nodeDirSort[$deep - 1] as $key => $parentNodeElement) {
                    $nodeExi = $this->childNodeElement($dirItemSort, $parentNodeElement);
                    if ($nodeExi) {
                        $nodeDirSort[$deep][] = $nodeExi;
                        $nodeDirFlat[] = $nodeExi;
                        $nodeDirFlatPure[] = $nodeExi['node_id'];
                        $nodeDirSortCount++;
                    }
                }
            }
            $deep++;
        }

        foreach ($nodeFile as $keyFileItem => $fileItem) {
            $nodeFile[$keyFileItem]['node_name_with_path'] = $this->getFileBaseDir($nodeDirFlat,
                    $fileItem['node_id_parent']) . '/' . $nodeFile[$keyFileItem]['file_name'];
            $nodeFile[$keyFileItem]['file_path'] = $this->getFilePathToDownload($nodeFile[$keyFileItem]);
            $nodeFileFlat[] = $nodeFile[$keyFileItem];
            $nodeFileFlatPure[] = $nodeFile[$keyFileItem]['node_id'];
        }

        //flit
        $dirSelect = [];
        $fileSelect = [];
        if (count($documentNodeSelected)) {
            foreach ($documentNodeSelected as $selectedItem) {
                if (in_array($selectedItem, $nodeDirFlatPure)) {
                    $dirSelect[] = $selectedItem;
                } else {
                    $fileSelect[] = $selectedItem;
                }
            }
        }

        $selectDir = [];
        foreach ($dirSelect as $flitedDir) {
            $selectDirTmp = $this->getChildDirNode($nodeDirFlat, $flitedDir);
            foreach ($selectDirTmp as $tmpSelectedItem) {
                $selectDir[] = $tmpSelectedItem;
            }
        }

        foreach ($nodeFileFlat as $fileFlatItem) {
            if (in_array($fileFlatItem['node_id_parent'], $selectDir)) {
                $fileSelect[] = $fileFlatItem['node_id'];
            }
        }

        $nodeFileDocument = $this->getFileFilter($fileSelect, $nodeFile);

        //get all nodes from chatfile
        $project = DB::table("projects")
            ->leftJoin("users", "users.id", "=", "projects.created_by")
            ->where("projects.id", $project_id)
            ->where("projects.deleted_at", null)
            ->whereNull("users.deleted_at")
            ->select([
                "projects.id",
                "projects.group_id",
                "users.name as userName"
            ])
            ->first();
        //add project chat-group
        $groupId = $project->group_id;

        //get groups which include the user logging in
        $userGroups = DB::table("chatgroups")
            ->where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->select([
                'group_id'
            ])
            ->get();
        $userGroupsArr = array();
        foreach ($userGroups as $groupItem) {
            $userGroupsArr[] = $groupItem->group_id;
        }

        //get main group info
        $mainGroup = DB::table("groups")
            ->where('id', $groupId)
            ->whereNull('deleted_at')
            ->first();
        $nodesChat[] = $this->makeNodeItemByGroup($mainGroup);
        //check if there are child-group
        $childGroup = DB::table("groups")
            ->where('parent_id', $groupId)
            ->whereNull('deleted_at')
            ->get();
        $groupChild = array();
        foreach ($childGroup as $group) {
            $nodesChat[] = $this->makeNodeItemByGroup($group);
            $groupChild[] = $group->id;
        }
        //get groups this user in
        $groupChild = array_intersect($groupChild, $userGroupsArr);
        //get all files belong to group
        $groupChild[] = $groupId;

        $files = DB::table("chat_files")
            ->leftJoin("users", "users.id", "=", "chat_files.upload_user_id")
            ->select([
                "chat_files.id",
                "chat_files.group_id",
                "chat_files.file_name",
                "chat_files.file_size",
                "chat_files.created_at",
                "users.name as uploadUserName"
            ])
            ->whereIn("chat_files.group_id", $groupChild)
            ->whereNull("chat_files.deleted_at")
            ->whereNull("users.deleted_at")
            ->get();
        foreach ($files as $file) {
            $nodesChat[] = $this->makeNodeItemByFile($file);
        }

        //Clean up redundant fields
        $nodesChatArr = [];
        foreach ($nodesChat as $key => $item) {
            $nodesChatArr[$key]['node_id'] = $item['id'];
            $nodesChatArr[$key]['node_id_parent'] = $item['parent'];
            $nodesChatArr[$key]['node_type'] = $item['type'];
            $nodesChatArr[$key]['node_name'] = $item['name'];
        }

        $chatGroupDirArr = [];
        $chatGroupFileArr = [];

        foreach ($nodesChatArr as $keyChatItem => $chatItem) {
            if ($chatItem['node_type']) {
                $chatGroupFileArr[] = $chatItem;
            } else {
                $chatGroupDirArr[] = $chatItem;
            }
        }

        //Structured directory(Tree)
        $nodeChatDirFlat = [];
        $nodeChatDirFlatPure = [];
        //Structured file
        $nodeChatFileFlat = [];
        $nodeChatFileFlatPure = [];

        //make chatgroup dirs
        foreach ($chatGroupDirArr as $chatItemDir) {
            $nodeChatDirFlat[] = $this->makeDirChat($chatGroupDirArr, $chatItemDir);
            $nodeChatDirFlatPure[] = $chatItemDir['node_id'];
        }

        //make chatgroup files
        foreach ($chatGroupFileArr as $keyChatFile => $chatFileItem) {
            $chatGroupFileArr[$keyChatFile]['node_name_with_path'] = $this->getFileBaseDir($nodeChatDirFlat,
                    $chatFileItem['node_id_parent']) . '/' . $chatGroupFileArr[$keyChatFile]['node_name'];
            $chatGroupFileArr[$keyChatFile]['file_path'] = $this->getSingleChatGroupFilePath(-$chatGroupFileArr[$keyChatFile]['node_id']);
            $nodeChatFileFlat[] = $chatGroupFileArr[$keyChatFile];
            $nodeChatFileFlatPure[] = $chatGroupFileArr[$keyChatFile]['node_id'];
        }

        //flit
        $dirChatSelect = [];
        $fileChatSelect = [];
        if (count($chatfileNodeSelected)) {
            foreach ($chatfileNodeSelected as $selectedChatItem) {
                if (in_array($selectedChatItem, $nodeChatDirFlatPure)) {
                    $dirChatSelect[] = $selectedChatItem;
                } else {
                    $fileChatSelect[] = $selectedChatItem;
                }
            }
        }

        $selectChatDir = [];
        foreach ($dirChatSelect as $flitedChatDir) {
            $selectChatDirTmp = $this->getChildDirNode($nodeChatDirFlat, $flitedChatDir);
            foreach ($selectChatDirTmp as $tmpChatSelectedItem) {
                $selectChatDir[] = $tmpChatSelectedItem;
            }
        }

        foreach ($nodeChatFileFlat as $fileChatFlatItem) {
            if (in_array($fileChatFlatItem['node_id_parent'], $selectChatDir)) {
                $fileChatSelect[] = $fileChatFlatItem['node_id'];
            }
        }

        $nodeChatDocument = $this->getFileFilter($fileChatSelect, $chatGroupFileArr);

        if (count($nodeChatDocument) + count($nodeFileDocument) == 0) {
            return response()->json(['error' => 'NO_FILE_SELECTED']);
        }

        //re-name file when same name file exists
        foreach ($nodeFileDocument as $renameKey => $renameValue) {
            $arrSameDir = $this->getSameDirFiles($nodeFileDocument, $renameValue['node_id_parent']);

            $sort = 0;

            if (count($arrSameDir) > 1) {
                $fileNameOrigin = $renameValue['node_name_with_path'];
                $fileNamePure = $renameValue['file_name'];

                //remove self
                $arrSameDir = $this->flitDuplicateFile($arrSameDir, $renameValue['node_id']);
                
                while ($this->checkDuplicateFileName($arrSameDir, $renameValue['file_name'])) {
                    $renameValue['file_name'] = $this->renameFileWhenDuplicate($fileNamePure, $sort);
                    $sort++;
                }

                $nodeFileDocument[$renameKey]['file_name'] = $renameValue['file_name'];
                $nodeFileDocument[$renameKey]['node_name_with_path'] = $this->changeZipFilePathName($fileNameOrigin,
                    $renameValue['file_name']);
            }
        }

        if($current){
            if($current > 0) {
                $rootPathFolder = $this->getRootPathFolder($nodeDirFlat,$current['id']);
            } else {
                $rootPathFolder = $this->getRootPathFolder($nodeChatDirFlat,$current['id']);
            }
        }else{
            $rootPathFolder = '';
        }

        //release used var
        $files = [];
        //check the files which will be sent to chat
        if($fileSource > 0) {
            //chat
            $numChat = count($nodeChatDocument);
            if(!$numChat){
                return response()->json(['error' => 'NO_FILE_SELECTED']); //add check 
            }
            if ($numChat > 10) {
                //make zip
                //create zip file
                $zip = new ZipArchive();

                //create file in public dir
                $prueName = 'batchDownload' . time() . '.zip';
                $zipName = 'public/' . $prueName;
                $diskLocal = Storage::disk('local');
                $diskLocal->put($zipName, '');
                $zip->open(storage_path('app/' . $zipName), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                foreach ($nodeChatDocument as $fileChatDownLoad) {
                    $exi = $diskSite->exists($fileChatDownLoad['file_path']);
                    if ($exi) {
                        $fileContent = $diskSite->get($fileChatDownLoad['file_path']);
                        $zip->addFromString($this->filterRootFolder($rootPathFolder,$fileChatDownLoad['node_name_with_path']), $fileContent);
                    }
                }
                $statusExi = $zip->getNameIndex(0);
                $status = $zip->close();
                //zip file
                if ($status && $statusExi) {
                    $files[0] = array(
                        'fileName' => $prueName,
                        'path' => $this->downloadZipPath($prueName), 
                        'stage' => self::PATH_PATTERN['Zip'] //zip must be Local
                    );
                } else {
                    return response()->json(['error' => 'NO_FILE_DOWNLOAD']);
                }
            } else {
                for($i=0;$i<$numChat;$i++) {
                    $files[$i] = array(
                        'fileName' => $nodeChatDocument[$i]['node_name'],
                        'path' => $nodeChatDocument[$i]['file_path'], //Chat fileSystem pattern
                        'stage' => self::PATH_PATTERN['Chat'] 
                    );
                }
            }
        } else{
            //doc
            $numFile = count($nodeFileDocument);
            if(!$numFile){
                return response()->json(['error' => 'NO_FILE_SELECTED']); //add check 
            }
            if ($numFile > 10) {
                //make zip
                //create zip file
                $zip = new ZipArchive();

                //create file in public dir
                $prueName = 'batchDownload' . time() . '.zip';
                $zipName = 'public/' . $prueName;
                $diskLocal = Storage::disk('local');
                $diskLocal->put($zipName, '');
                $zip->open(storage_path('app/' . $zipName), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                foreach ($nodeFileDocument as $fileDownLoad) {
                    $exi = $disk->exists($fileDownLoad['file_path']);
                    if ($exi) {
                        $fileContent = $disk->get($fileDownLoad['file_path']);
                        $zip->addFromString($this->filterRootFolder($rootPathFolder,$fileDownLoad['node_name_with_path']), $fileContent);
                    }
                }
                $statusExi = $zip->getNameIndex(0);
                $status = $zip->close();
                //zip file
                if ($status && $statusExi) {
                    $files[0] = array(
                        'fileName' => $prueName,
                        'path' => $this->downloadZipPath($prueName), //Document self fileSystem pattern
                        'stage' => self::PATH_PATTERN['Zip'] 
                    );
                } else {
                    return response()->json(['error' => 'NO_FILE_DOWNLOAD']);
                }
            } else {
                for($i=0;$i<$numFile;$i++) {
                    $files[$i] = array(
                        'fileName' => $nodeFileDocument[$i]['file_name'],
                        'path' => $nodeFileDocument[$i]['file_path'], //Document self fileSystem pattern
                        'stage' => self::PATH_PATTERN['Local'] 
                    );
                }
            }
        }

        return response()->json([
            'files' => $files
        ]);
    }

    //get zip download path
    public function downloadZipPath($fileName)
    {
        $zipName = 'public/' . $fileName;
        return $zipName;
    }

    //move file to website project
    public function moveFileToChat(Request $request) {
        //return data
        $filesData = [];
        $groupId = $request->input('groupId');
            $current = $request->input('current');
            $selectedNodeIds = $request->input('selected');
            $enterpriseId = Auth::user()->enterprise_id;
            $coopEnterpriseId = Auth::user()->coop_enterprise_id;
            $userType = 0;
            //check user type
            if($enterpriseId) {
                $userType = self::USER_TYPE['Enterprise'];
            }
            if($coopEnterpriseId) {
                $userType = self::USER_TYPE['CoopEnterprise'];
            }

            //check group type
            $groupType = 0;
            $groupInfo = DB::table('groups')
                ->where('id', $groupId)
                ->whereNull('deleted_at')
                ->first();
            if($groupInfo->kind == 1) {
                $groupType = self::GROUP_TYPE['Single'];
            }else{
                $groupParentId = $groupInfo->parent_id;
                $gId = $groupId;
                if($groupParentId) {
                    $gId = $groupParentId;
                }
                $isProject = DB::table('projects')
                    ->where('group_id', $gId)
                    ->whereNull('deleted_at')
                    ->count();
                if($isProject) {
                    $groupType = self::GROUP_TYPE['Project'];
                }
            }

            $typeStr = $groupType . '-' . $userType;
            $range = self::SELECTED_RANGE[$typeStr];

            $selectedNodeIds = $this->flitSelected($selectedNodeIds);
            $fileSource = 0; //0 is doc,1 is chat
            $nodes=[];
            if($current > 0) {
                $fileSource = 0;
                $doc_db = config('const.db_database_doc');
                //check nodeRoot is Internal or Project
                $nodeInfo = DB::table("$doc_db.nodes")
                    ->where('node_id', $current)
                    ->whereNull('deleted_at')
                    ->first();
                $isInternal = $nodeInfo->enterprise_id ? true : false;

                if($isInternal) {
                    $nodes = NodeInternal::findAll(Auth::user()->enterprise_id);
                    $node = NodeInternal::findFirst(
                        Auth::user()->enterprise_id,
                        [
                            'node_id' => $current,
                        ]);
                } else {
                    $nodes = Node::findAll($nodeInfo->project_id);
                    $node = Node::findFirst(
                        $nodeInfo->project_id,
                        [
                            'node_id' => $current,
                        ]);
                }
                $current=$this->toNodeJson($node);
            } else{
                $current=['id'=>$current];
                $current['name']=Group::where('id',-$current['id'])->value('name');
                $fileSource = 1;
            }
            //return data
            $files = []; //files will be sent

            //storage setting
            $disk = Storage::disk('s3doc');
            $diskSite = Storage::disk('s3');

            //selected nodes from document
            $documentNodeSelected = [];
            //selected nodes from chatfile
            $chatfileNodeSelected = [];

            foreach ($selectedNodeIds as $selectedNodeId) {
                if ($selectedNodeId > 0) {
                    $documentNodeSelected[] = $selectedNodeId;
                } else {
                    $chatfileNodeSelected[] = $selectedNodeId;
                }
            }

            //Clean up redundant fields
            $nodesArr = [];
            foreach ($nodes as $key => $item) {
                $nodesArr[$key]['node_id'] = $item->node_id;
                $nodesArr[$key]['node_id_parent'] = $item->node_id_parent;
                $nodesArr[$key]['node_type'] = $item->node_type;
                $nodesArr[$key]['node_name'] = $item->node_name;
                $nodesArr[$key]['rev_no'] = $item->rev_no;
                $nodesArr[$key]['file_name'] = $item->file_name;
            }

            //Distinguish node types
            $nodeTmp = $this->getDirItem($nodesArr);

            $nodeDir = $nodeTmp['dirs'];
            $nodeFile = $nodeTmp['files'];

            //Structured directory(Tree)
            $nodeDirSort = [];
            $nodeDirFlat = [];
            $nodeDirFlatPure = [];
            //Structured file
            $nodeFileFlat = [];
            $nodeFileFlatPure = [];

            $parentDirIds = $this->getParentDir($nodeDir);

            $nodeDirSortCount = 0;
            foreach ($parentDirIds as $parentDir) {
                $dirSortItem = $this->getNodeElement($nodeDir, $parentDir);
                $nodeDirSort[0][] = $dirSortItem;
                $nodeDirFlat[] = $dirSortItem;
                $nodeDirFlatPure[] = $dirSortItem['node_id'];
                $nodeDirSortCount++;
            }

            $nodeCount = count($nodeDir);
            $deep = 1;

            while ($nodeDirSortCount < $nodeCount) {
                foreach ($nodeDir as $dirItemSort) {
                    foreach ($nodeDirSort[$deep - 1] as $key => $parentNodeElement) {
                        $nodeExi = $this->childNodeElement($dirItemSort, $parentNodeElement);
                        if ($nodeExi) {
                            $nodeDirSort[$deep][] = $nodeExi;
                            $nodeDirFlat[] = $nodeExi;
                            $nodeDirFlatPure[] = $nodeExi['node_id'];
                            $nodeDirSortCount++;
                        }
                    }
                }
                $deep++;
            }

            foreach ($nodeFile as $keyFileItem => $fileItem) {
                $nodeFile[$keyFileItem]['node_name_with_path'] = $this->getFileBaseDir($nodeDirFlat,
                        $fileItem['node_id_parent']) . '/' . $nodeFile[$keyFileItem]['file_name'];
                $nodeFile[$keyFileItem]['file_path'] = $this->getFilePathToDownload($nodeFile[$keyFileItem]);
                $nodeFileFlat[] = $nodeFile[$keyFileItem];
                $nodeFileFlatPure[] = $nodeFile[$keyFileItem]['node_id'];
            }

            //flit
            $dirSelect = [];
            $fileSelect = [];
            if (count($documentNodeSelected)) {
                foreach ($documentNodeSelected as $selectedItem) {
                    if (in_array($selectedItem, $nodeDirFlatPure)) {
                        $dirSelect[] = $selectedItem;
                    } else {
                        $fileSelect[] = $selectedItem;
                    }
                }
            }

            $selectDir = [];
            foreach ($dirSelect as $flitedDir) {
                $selectDirTmp = $this->getChildDirNode($nodeDirFlat, $flitedDir);
                foreach ($selectDirTmp as $tmpSelectedItem) {
                    $selectDir[] = $tmpSelectedItem;
                }
            }

            foreach ($nodeFileFlat as $fileFlatItem) {
                if (in_array($fileFlatItem['node_id_parent'], $selectDir)) {
                    $fileSelect[] = $fileFlatItem['node_id'];
                }
            }

            $nodeFileDocument = $this->getFileFilter($fileSelect, $nodeFile);

            //get groups which include the user logging in
            $userGroups = DB::table("chatgroups")
                ->where('user_id', Auth::id())
                ->whereNull('deleted_at')
                ->select([
                    'group_id'
                ])
                ->get();
            $userGroupsArr = array();
            foreach ($userGroups as $groupItem) {
                $userGroupsArr[] = $groupItem->group_id;
            }


            $filesChat = [];

            //get project files
            $nodesChatArr = [];

//            $projectIdArr = $this->getGroupsProject($groupId);
//            $nodes = $this->getSingleProjectChatGroupFiles($projectIdArr, $groupId);
//            foreach($nodes as $nodeShareProjectChat) {
//                $nodesChatArr[] = $nodeShareProjectChat;
//            }

            //share project
            if(in_array(self::RANGE_TYPE['ShareProject'], $range)) {
                //get project files
                $projectIdArr = $this->getGroupsProject($groupId);
                $nodes = $this->getSingleProjectChatGroupFiles($projectIdArr, $groupId);
                foreach($nodes as $nodeShareProjectChat) {
                    $nodesChatArr[] = $nodeShareProjectChat;
                }
            }

            //single Project Group
            if(in_array(self::RANGE_TYPE['SingleProject'], $range)) {
                //get project files
                $nodes = $this->getSingleProject($groupId);
                foreach($nodes as $nodeSingleProjectChat) {
                    $nodesChatArr[] = $nodeSingleProjectChat;
                }
            }

            $chatGroupDirArr = [];
            $chatGroupFileArr = [];

            foreach ($nodesChatArr as $keyChatItem => $chatItem) {
                if ($chatItem['node_type']) {
                    $chatGroupFileArr[] = $chatItem;
                } else {
                    $chatGroupDirArr[] = $chatItem;
                }
            }

            //Structured directory(Tree)
            $nodeChatDirFlat = [];
            $nodeChatDirFlatPure = [];
            //Structured file
            $nodeChatFileFlat = [];
            $nodeChatFileFlatPure = [];

            //make chatgroup dirs
            foreach ($chatGroupDirArr as $chatItemDir) {
                $nodeChatDirFlat[] = $this->makeDirChat($chatGroupDirArr, $chatItemDir);
                $nodeChatDirFlatPure[] = $chatItemDir['node_id'];
            }

            //make chatgroup files
            foreach ($chatGroupFileArr as $keyChatFile => $chatFileItem) {
                $chatGroupFileArr[$keyChatFile]['node_name_with_path'] = $this->getFileBaseDir($nodeChatDirFlat,
                        $chatFileItem['node_id_parent']) . '/' . $chatGroupFileArr[$keyChatFile]['node_name'];
                $chatGroupFileArr[$keyChatFile]['file_path'] = $this->getSingleChatGroupFilePath(-$chatGroupFileArr[$keyChatFile]['node_id']);
                $nodeChatFileFlat[] = $chatGroupFileArr[$keyChatFile];
                $nodeChatFileFlatPure[] = $chatGroupFileArr[$keyChatFile]['node_id'];
            }

            //flit
            $dirChatSelect = [];
            $fileChatSelect = [];
            if (count($chatfileNodeSelected)) {
                foreach ($chatfileNodeSelected as $selectedChatItem) {
                    if (in_array($selectedChatItem, $nodeChatDirFlatPure)) {
                        $dirChatSelect[] = $selectedChatItem;
                    } else {
                        $fileChatSelect[] = $selectedChatItem;
                    }
                }
            }

            $selectChatDir = [];
            foreach ($dirChatSelect as $flitedChatDir) {
                $selectChatDirTmp = $this->getChildDirNode($nodeChatDirFlat, $flitedChatDir);
                foreach ($selectChatDirTmp as $tmpChatSelectedItem) {
                    $selectChatDir[] = $tmpChatSelectedItem;
                }
            }

            foreach ($nodeChatFileFlat as $fileChatFlatItem) {
                if (in_array($fileChatFlatItem['node_id_parent'], $selectChatDir)) {
                    $fileChatSelect[] = $fileChatFlatItem['node_id'];
                }
            }

            $nodeChatDocument = $this->getFileFilter($fileChatSelect, $chatGroupFileArr);

            if (count($nodeChatDocument) + count($nodeFileDocument) == 0) {
                return response()->json(['message' => 'ダウンロードするファイルがありません。','status'=>0]);
            }

            //re-name file when same name file exists
            foreach ($nodeFileDocument as $renameKey => $renameValue) {
                $arrSameDir = $this->getSameDirFiles($nodeFileDocument, $renameValue['node_id_parent']);

                $sort = 0;

                if (count($arrSameDir) > 1) {
                    $fileNameOrigin = $renameValue['node_name_with_path'];
                    $fileNamePure = $renameValue['file_name'];

                    //remove self
                    $arrSameDir = $this->flitDuplicateFile($arrSameDir, $renameValue['node_id']);

                    while ($this->checkDuplicateFileName($arrSameDir, $renameValue['file_name'])) {
                        $renameValue['file_name'] = $this->renameFileWhenDuplicate($fileNamePure, $sort);
                        $sort++;
                    }

                    $nodeFileDocument[$renameKey]['file_name'] = $renameValue['file_name'];
                    $nodeFileDocument[$renameKey]['node_name_with_path'] = $this->changeZipFilePathName($fileNameOrigin,
                        $renameValue['file_name']);
                }
            }

            if($current){
                if($current['id'] > 0) {
                    $rootPathFolder = $this->getRootPathFolder($nodeDirFlat,$current['id']);
                } else {
                    $rootPathFolder = $this->getRootPathFolder($nodeChatDirFlat,$current['id']);
                }
                if(isset($current['name']) && $current['name']){
                        $nodeName=$current['name'];
                }else{
                    $nodeName='batchDownload';
                }
            }else{
                $rootPathFolder = '';
                $nodeName='batchDownload';
            }

            //release used var
            $files = [];
            //check the files which will be sent to chat
            if($fileSource > 0) {
                //chat
                $numChat = count($nodeChatDocument);
                if(!$numChat){
                    return response()->json(['message' => 'ダウンロードするファイルがありません。','status'=>0]); //add check
                }
                if ($numChat > 10) {
                    //make zip
                    //create zip file
                    $zip = new ZipArchive();

                    //create file in public dir
                    $prueName = $nodeName . time() . '.zip';
                    $zipName = 'public/' . $prueName;
                    $diskLocal = Storage::disk('local');
                    $diskLocal->put($zipName, '');
                    $zip->open(storage_path('app/' . $zipName), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    foreach ($nodeChatDocument as $fileChatDownLoad) {
                        $exi = $diskSite->exists($fileChatDownLoad['file_path']);
                        if ($exi) {
                            $fileContent = $diskSite->get($fileChatDownLoad['file_path']);
                            $zip->addFromString($this->filterRootFolder($rootPathFolder,$fileChatDownLoad['node_name_with_path']), $fileContent);
                        }
                    }
                    $statusExi = $zip->getNameIndex(0);
                    $status = $zip->close();
                    //zip file
                    if ($status && $statusExi) {
                        $files[0] = array(
                            'fileName' => $prueName,
                            'path' => $this->downloadZipPath($prueName),
                            'stage' => self::PATH_PATTERN['Zip'] //zip must be Local
                        );
                        $chatFileName = $this->makeChatGroupFileName($files[0]['fileName']);
                        $chatFilePath = $this->makeChatGroupFilePath($groupId, $chatFileName);

                        //get local file
                        $zipName = $files[0]['path']; //zip type only one file
                        $size = Storage::size($zipName);
                        $contents = Storage::get($zipName);
                        //get chat s3 setting
                        Storage::disk('s3')->put($chatFilePath, $contents);
                        //delete zip file
                        Storage::delete($zipName);
                        $fileInfoTmp = [];
                        $fileInfoTmp['name'] = $chatFileName;
                        $fileInfoTmp['size'] = $size;
                        $filesData[] = $fileInfoTmp;
                        return response()->json([
                            'filesData' => $filesData,
                            'status'=>1,
                        ]);
                    } else {
                        return response()->json(['message' => 'ダウンロードするファイルがありません。','status'=>0]);
                    }
                } else {
                    for($i=0;$i<$numChat;$i++) {
                        $files[$i] = array(
                            'fileName' => $nodeChatDocument[$i]['node_name'],
                            'path' => $nodeChatDocument[$i]['file_path'], //Chat fileSystem pattern
                            'stage' => self::PATH_PATTERN['Chat']
                        );
                    }
                    for($i=0;$i<$numChat;$i++) {
                        if(Storage::disk('s3')->exists($files[$i]['path'])){
                            $size = Storage::disk('s3')->size($files[$i]['path']);
                            $contents = Storage::disk('s3')->get($files[$i]['path']);
                            //get chat s3 setting
                            $chatFileName = $this->makeChatGroupFileName($files[$i]['fileName']);
                            $chatFilePath = $this->makeChatGroupFilePath($groupId, $chatFileName);
                            Storage::disk('s3')->put($chatFilePath, $contents);
                            $fileInfoTmp = [];
                            $fileInfoTmp['name'] = $chatFileName;
                            $fileInfoTmp['size'] = $size;
                            $filesData[] = $fileInfoTmp;
                        }
                    }
                    if(count($filesData)){
                        return response()->json(['status'=>1,'filesData'=>$filesData]);
                    }else{
                        return response()->json(['message' => 'ダウンロードするファイルがありません','status'=>0]);
                    }
                }
            } else{
                //doc
                $numFile = count($nodeFileDocument);
                if(!$numFile){
                    return response()->json(['message' => 'ダウンロードするファイルがありません。','status'=>0]); //add check
                }
                if ($numFile > 10) {
                    //make zip
                    //create zip file
                    $zip = new ZipArchive();

                    //create file in public dir
                    $prueName = 'batchDownload' . time() . '.zip';
                    $zipName = 'public/' . $prueName;
                    $diskLocal = Storage::disk('local');
                    $diskLocal->put($zipName, '');
                    $zip->open(storage_path('app/' . $zipName), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    foreach ($nodeFileDocument as $fileDownLoad) {
                        $exi = $disk->exists($fileDownLoad['file_path']);
                        if ($exi) {
                            $fileContent = $disk->get($fileDownLoad['file_path']);
                            $zip->addFromString($this->filterRootFolder($rootPathFolder,$fileDownLoad['node_name_with_path']), $fileContent);
                        }
                    }
                    $statusExi = $zip->getNameIndex(0);
                    $status = $zip->close();
                    //zip file
                    if ($status && $statusExi) {
                        $files[0] = array(
                            'fileName' => $prueName,
                            'path' => $this->downloadZipPath($prueName), //Document self fileSystem pattern
                            'stage' => self::PATH_PATTERN['Zip']
                        );
                        //doc move to chat
                        //only zip file is Local
                        //make chat s3 path
                        $chatFileName = $this->makeChatGroupFileName($files[0]['fileName']);
                        $chatFilePath = $this->makeChatGroupFilePath($groupId, $chatFileName);

                        //get local file
                        $zipName = $files[0]['path']; //zip type only one file
                        $size = Storage::size($zipName);
                        $contents = Storage::get($zipName);
                        //get chat s3 setting
                        Storage::disk('s3')->put($chatFilePath, $contents);
                        //delete zip file
                        Storage::delete($zipName);
                        $fileInfoTmp = [];
                        $fileInfoTmp['name'] = $chatFileName;
                        $fileInfoTmp['size'] = $size;
                        $filesData[] = $fileInfoTmp;
                        return response()->json(['status'=>1,'filesData'=>$filesData]);
                    } else {
                        return response()->json(['message' => 'ダウンロードするファイルがありません。','status'=>0]);
                    }
                } else {
                    for($i=0;$i<$numFile;$i++) {
                        $files[$i] = array(
                            'fileName' => $nodeFileDocument[$i]['file_name'],
                            'path' => $nodeFileDocument[$i]['file_path'], //Document self fileSystem pattern
                            'stage' => self::PATH_PATTERN['Local']
                        );
                    }
                    $fileInfoTmp='';
                    for($i=0;$i<$numFile;$i++) {
                        if(Storage::disk('s3doc')->exists($files[$i]['path'])) {
                            $size = Storage::disk('s3doc')->size($files[$i]['path']);
                            $contents = Storage::disk('s3doc')->get($files[$i]['path']);
                            //get chat s3 setting
                            $chatFileName = $this->makeChatGroupFileName($files[$i]['fileName']);
                            $chatFilePath = $this->makeChatGroupFilePath($groupId, $chatFileName);
                            Storage::disk('s3')->put($chatFilePath, $contents);
                            $fileInfoTmp = [];
                            $fileInfoTmp['name'] = $chatFileName;
                            $fileInfoTmp['size'] = $size;
                            $filesData[] = $fileInfoTmp;
                        }
                    }
                    if(count($filesData)){
                        return response()->json(['status'=>1,'filesData'=>$filesData]);
                    }else{
                        return response()->json(['message' => 'ダウンロードするファイルがありません','status'=>0]);
                    }
                }
            }
    }

    //get chat files' path with pattern
    private function getFilePath($type, $file_name)
    {
        if (is_numeric($type)) {
            // チャット画面でのファイル送信
            // バケット/upload/g3/ファイル名
            return sprintf('upload/g%s/%s', $type, $file_name);
        } // プロフィール画像
        elseif ($type == 'users' || $type == 'groups' || $type == 'projects') {
            // バケット/users/100.jpg
            // バケット/groups/3.jpg
            // バケット/projects/3.jpg
            return sprintf('%s/%s', $type, $file_name);
        } else {
            return 'not_found.png';
        }
    }

    //rename file with pattern
    private function renameChatFile($groupId, $originalName) {
        $newName = date('YmdHis') . $originalName;
        //配置ファイルにuploads
        $filename = $this->getFilePath($groupId, $newName);
        // $fileStream = file_get_contents($docPath);
        // Storage::disk(config('web.imageUpload.disk'))->put($filename, file_get_contents($realPath));
        return $filename;
    }

    //get common chat groups (1 only single chat 2 only Project partner)
    private function getChatGroupWithPartner($projectId) {
        //get project partners
        $partners = $this->getProjectPartner($projectId);
        $partnersArr = [];
        foreach($partners as $partner) {
            $partnersArr[] = $partner->user_id;
        }
        array_unique($partnersArr);//re-check data
        
        $siteweb_db = config('const.db_database_site');

        $groupsSelf = DB::table("$siteweb_db.chatgroups")
        ->leftJoin("$siteweb_db.groups",'groups.id','=','chatgroups.group_id')
        ->where("chatgroups.user_id",Auth::id())
        ->where("groups.kind",1) //only single chat 
        ->whereNull("chatgroups.deleted_at")
        ->whereNull("groups.deleted_at")
        ->select([
            'chatgroups.group_id'
        ])
        ->get()
        ->toArray();
        $groupsSelfArr =  [];
        foreach($groupsSelf as $groupSelfItem) {
            $groupsSelfArr[] = $groupSelfItem->group_id;
        }

        $groups = DB::table("$siteweb_db.chatgroups")
            ->leftJoin("$siteweb_db.users","users.id","=","chatgroups.user_id")
            ->leftJoin("$siteweb_db.groups","groups.id","=","chatgroups.group_id")
            ->whereIn("chatgroups.user_id",$partnersArr)
            ->whereIn("chatgroups.group_id",$groupsSelfArr)
            ->whereNull("$siteweb_db.chatgroups.deleted_at")
            ->whereNull("$siteweb_db.users.deleted_at")
            ->whereNull("$siteweb_db.groups.deleted_at")
            ->select([
                'chatgroups.group_id',
                'users.name',
                'groups.kind',
                'users.file'
            ])
            ->get()
            ->toArray();
        
        $groupsArr = [];
        foreach($groups as $item) {
            $arrTmp = [];
            $arrTmp['groupId'] = $item->group_id;
            $arrTmp['groupName'] = $item->name;
            $arrTmp['groupKind'] = $item->kind;
            $arrTmp['groupFile'] = $item->file;
            $arrTmp['pro'] = 0;
            $groupsArr[] = $arrTmp;
        }
        return $groupsArr;
    }

    //get project partner's userId
    private function getProjectPartner($projectId) {
        //table project_participants
        $siteweb_db = config('const.db_database_site');
        //get project information
        $partners = DB::table("$siteweb_db.project_participants")
            ->where("$siteweb_db.project_participants.project_id", $projectId)
            ->whereNotIn("$siteweb_db.project_participants.user_id", [Auth::id()])
            ->whereNull("$siteweb_db.project_participants.deleted_at")
            ->select([
                "$siteweb_db.project_participants.user_id",
            ])
            ->get()->toArray();
        
        return $partners;
    }

    //get project chat groups
    private function getProjectGroup($projectId) {
        $siteweb_db = config('const.db_database_site');
        //get project information
        $project = DB::table("$siteweb_db.projects")
            ->where("$siteweb_db.projects.id", $projectId)
            ->where("$siteweb_db.projects.deleted_at", null)
            ->whereNull("$siteweb_db.projects.deleted_at")
            ->select([
                "$siteweb_db.projects.group_id",
            ])
            ->first();

        //get project's main group
        $groupId = $project->group_id;
        //get groups which include the user logging in
        $userGroups = DB::table("$siteweb_db.chatgroups")
            ->where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->select([
                'group_id'
            ])
            ->get();
        $userGroupsArr = array();
        foreach ($userGroups as $groupItem) {
            $userGroupsArr[] = $groupItem->group_id;
        }
        //check if there are child-group (with main group)
        $childGroup = DB::table("$siteweb_db.groups")
            ->where(function ($query) use ($groupId){
                $query->where('parent_id', $groupId)
                    ->orWhere('id', $groupId);
            })
            ->whereIn('id', $userGroupsArr)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'name',
                'kind',
                'file'
            ])
            ->get();
        $groupsArr = [];
        foreach($childGroup as $groupItem) {
            $arrTmp = [];
            $arrTmp['groupId'] = $groupItem->id;
            $arrTmp['groupName'] = $groupItem->name;
            $arrTmp['groupKind'] = $groupItem->kind;
            $arrTmp['groupFile'] = $groupItem->file;
            $arrTmp['pro'] = 0;
            $groupsArr[] = $arrTmp;
        }
        return $groupsArr;
    }

    private function getProjectSingleGroup($groupId) {
        $siteweb_db = config('const.db_database_site');
        $groupInfo = DB::table("$siteweb_db.groups")
            ->where('id', $groupId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'name',
                'kind',
                'file'
            ])
            ->first();
        $groupsArr = [];
        $arrTmp['groupId'] = $groupInfo->id;
        $arrTmp['groupName'] = $groupInfo->name;
        $arrTmp['groupKind'] = $groupInfo->kind;
        $arrTmp['groupFile'] = $groupInfo->file;
        $arrTmp['pro'] = 0;
        
        $groupsArr[] = $arrTmp;

        return $groupsArr;
    }

    //make chatGroup file name
    private function makeChatGroupFileName($fileName) {
        $newName = date('YmdHis') . $fileName;
        return $newName;
    }

    //make chatGroup file path
    private function makeChatGroupFilePath($groupId,$fileName) {
        //配置ファイルにuploads
        $filename = $this->getFilePath($groupId, $fileName);
        return $filename;
    }

    public function showFile(Request $request, $project_id, $nodeId, $revNo)
    {
        if ($nodeId > 0) {
            try {
                // リビジョン情報取得
                $rev = Rev::findFirst([
                    'node_id' => $nodeId,
                    'rev_no' => $revNo,
                ]);

                $filePath = implode('/', [self::FILES_PATH_PREFIX, $rev->node_id, $rev->rev_no, $rev->file_name]);

                // ファイルがない
                if (!Storage::disk('s3')->exists($filePath)) {
                    Log::info(__METHOD__ . ': file not exists' . $filePath);
                    return response()->json(['error' => 'FILE_NOT_EXISTS']);
                }

                $content = Storage::disk('s3')->get($filePath);
                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $rev->file_name);
            } catch (Exception $e) {
                Log::error($e);
                DB::rollback();

                return response()->json(['error' => 'UNKNOWN_ERROR']);
            }
        } else {
            try {
                //chat-group file
                $filePath = $this->getSingleChatGroupFilePath(-$nodeId);
                $fileNameArr = explode('/', $filePath);
                $last = count($fileNameArr) - 1;
                $fileName = $fileNameArr[$last];
                // ファイルがない
                if (!Storage::disk('s3site')->exists($filePath)) {
                    Log::info(__METHOD__ . ': chat file not exists' . $filePath);
                    return response()->json(['error' => 'FILE_NOT_EXISTS']);
                }

                $content = Storage::disk('s3site')->get($filePath);
                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $fileName);
            } catch (Exception $e) {
                Log::error($e);
                DB::rollback();

                return response()->json(['error' => 'UNKNOWN_ERROR']);
            }
        }
    }

    //get file type icon
    public function getDocFileIcon($nodeType) {
        switch ($nodeType) {
            case 'Folder':
                $fileName = 'img/icon/Folder.png';
                break;
            case 'XLS':
            case 'XLSX':
                $fileName = 'img/icon/Excel.png';
                break;
            case 'DOC':
            case 'DOCX':
                $fileName = 'img/icon/Word.png';
                break;
            case 'PPT':
            case 'PPTX':
                $fileName = 'img/icon/PowerPoint.png';
                break;
            case 'PDF':
                $fileName = 'img/icon/PDF.png';
                break;
            default:
                $fileName = 'img/icon/Other.png';
                break;
        }
        return response()->download($fileName);
    }

    //get image thumbnail
    public function getDocFileThumb($nodeId, $revNo = 0, $fileName = '') {
        if ($nodeId > 0) {
            try {
                // リビジョン情報取得
                $rev = Rev::findFirst([
                    'node_id' => $nodeId,
                    'rev_no' => $revNo,
                ]);

                $filePath = implode('/', [self::FILES_PATH_PREFIX, $rev->node_id, $rev->rev_no, $rev->file_name]);

                // ファイルがない
                if (!Storage::disk('s3doc')->exists($filePath)) {
                    Log::info(__METHOD__ . ': file not exists' . $filePath);
                    return response()->json(['error' => 'FILE_NOT_EXISTS']);
                }

                $content = Storage::disk('s3doc')->get($filePath);
                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $rev->file_name);
            } catch (Exception $e) {
                Log::error($e);
                return response()->json(['error' => 'UNKNOWN_ERROR']);
            }
        } else {
            try {
                //chat-group file
                $filePath = $this->getSingleChatGroupFilePath(-$nodeId);
                $fileNameArr = explode('/', $filePath);
                $last = count($fileNameArr) - 1;
                $fileName = $fileNameArr[$last];
                // ファイルがない
                if (!Storage::disk('s3')->exists($filePath)) {
                    Log::info(__METHOD__ . ': chat file not exists' . $filePath);
                    return response()->json(['error' => 'FILE_NOT_EXISTS']);
                }

                $content = Storage::disk('s3')->get($filePath);
                return response()->streamDownload(function () use ($content) {
                    echo $content;
                }, $fileName);
            } catch (Exception $e) {
                Log::error($e);
                return response()->json(['error' => 'UNKNOWN_ERROR']);
            }
        }
    }
}
