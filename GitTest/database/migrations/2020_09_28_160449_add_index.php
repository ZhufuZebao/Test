<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add index #2283
        
        //chat module start
        //chat_last_reads
        Schema::table('chatlastreads', function (Blueprint $table) {
            $table->index(['group_id', 'user_id'],'chatlastreads_group_id_user_id');
        });
        //chat_messages
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->index('group_id','chatmessages_group_id');
            $table->index('from_user_id','chatmessages_from_user_id');
        });
        //chat_groups
        Schema::table('chatgroups', function (Blueprint $table) {
            $table->index('group_id','chatgroups_group_id');
            $table->index('user_id','chatgroups_user_id');
        });
        //chat_lists
        Schema::table('chatlists', function (Blueprint $table) {
            $table->index('group_id','chatlists_group_id');
            $table->index('owner_id','chatlists_owner_id');
            $table->index('user_id','chatlists_user_id');
        });
        //chat_files
        Schema::table('chat_files', function (Blueprint $table) {
            $table->index('group_id','chat_files_group_id');
            $table->index('file_name','chat_files_file_name');
            $table->index('upload_user_id','chat_files_upload_user_id');
        });
        //chat_contacts
        Schema::table('chatcontacts', function (Blueprint $table) {
            $table->index('from_user_id','chatcontacts_from_user_id');
            $table->index('to_user_id','chatcontacts_to_user_id');
            $table->index('contact_agree','chatcontacts_contact_agree');
        });
        //chat module end
        
        //schedule module start
        //schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['st_datetime','ed_datetime'],'schedules_st_datetime_ed_datetime');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['st_span','ed_span'],'schedules_st_span_ed_span');
        });
        //schedulesubs
        Schema::table('schedulesubs', function (Blueprint $table) {
            $table->index('relation_id','schedulesubs_relation_id');
            $table->index('s_date','schedulesubs_s_date');
            $table->index(['relation_id','s_date'],'schedulesubs_relation_id_s_date');
        });
        //scheduleparticipants
        Schema::table('scheduleparticipants', function (Blueprint $table) {
            $table->index('schedule_id','scheduleparticipants_schedule_id');
            $table->index('user_id','scheduleparticipants_user_id');
        });
        //project_participants
        Schema::table('project_participants', function (Blueprint $table) {
            $table->index('project_id','project_participants_project_id');
            $table->index('user_id','project_participants_user_id');
            $table->index(['project_id','user_id'],'project_participants_project_id_user_id');
        });
        //enterprise_participants
        Schema::table('enterprise_participants', function (Blueprint $table) {
            $table->index('enterprise_id','enterprise_participants_enterprise_id');
        });
        //schedule module end
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //chat module start
        //chat_last_reads
        Schema::table('chatlastreads', function (Blueprint $table) {
            $table->dropIndex('chatlastreads_group_id_user_id');
        });
        //chat_messages
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->dropIndex('chatmessages_group_id');
            $table->dropIndex('chatmessages_from_user_id');
        });
        //chat_groups
        Schema::table('chatgroups', function (Blueprint $table) {
            $table->dropIndex('chatgroups_group_id');
            $table->dropIndex('chatgroups_user_id');
        });
        //chat_lists
        Schema::table('chatlists', function (Blueprint $table) {
            $table->dropIndex('chatlists_group_id');
            $table->dropIndex('chatlists_owner_id');
            $table->dropIndex('chatlists_user_id');
        });
        //chat_files
        Schema::table('chat_files', function (Blueprint $table) {
            $table->dropIndex('chat_files_group_id');
            $table->dropIndex('chat_files_file_name');
            $table->dropIndex('chat_files_upload_user_id');
        });
        //chat_contacts
        Schema::table('chatcontacts', function (Blueprint $table) {
            $table->dropIndex('chatcontacts_from_user_id');
            $table->dropIndex('chatcontacts_to_user_id');
            $table->dropIndex('chatcontacts_contact_agree');
        });
        //chat module end
        
        //schedule module start
        //schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_st_datetime_ed_datetime');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_st_span_ed_span');
        });
        //schedulesubs
        Schema::table('schedulesubs', function (Blueprint $table) {
            $table->dropIndex('schedulesubs_relation_id');
            $table->dropIndex('schedulesubs_s_date');
            $table->dropIndex('schedulesubs_relation_id_s_date');
        });
        //scheduleparticipants
        Schema::table('scheduleparticipants', function (Blueprint $table) {
            $table->dropIndex('scheduleparticipants_schedule_id');
            $table->dropIndex('scheduleparticipants_user_id');
        });
        //project_participants
        Schema::table('project_participants', function (Blueprint $table) {
            $table->dropIndex('project_participants_project_id');
            $table->dropIndex('project_participants_user_id');
            $table->dropIndex('project_participants_project_id_user_id');
        });
        //enterprise_participants
        Schema::table('enterprise_participants', function (Blueprint $table) {
            $table->dropIndex('enterprise_participants_enterprise_id');
        });
        //schedule module end
    }
}
