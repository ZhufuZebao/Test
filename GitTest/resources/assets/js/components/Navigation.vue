<template>
  <div>
    <!--nav-wrapper-->
    <div class="nav-wrapper">
      <div class="logo"></div>
      <nav>
        <ul>
          <li><router-link to="/dashboard" class="dashboard">ダッシュボード</router-link></li>
          <li @click="browse('project')" v-if="((user.enterprise_id)
          || (user.coop_enterprise_id && user.displayProject && !user.enterprise_id)
          ||user.displayProject)">
            <router-link to="/project" class="project">案件</router-link>
          </li>
          <li @click="browse('chat')"><router-link to="/chat" class="chat">チャット</router-link></li>
          <li @click="browse('schedule')"><router-link to="/schedule" class="schedule">スケジュール</router-link></li>
          <li @click="browse('document')" v-if="user.enterprise_id"><router-link to="/document" class="document">ドキュメント</router-link></li>
          <li @click="browse('report')" v-if="reportFlag"><router-link to="/report" class="report">簡易レポート</router-link></li>
          <li  v-if="user.enterprise_id"><router-link  to="/company" class="partner">会社情報</router-link></li>
           <li><router-link to="/contact" class="friend">連絡先</router-link></li>
          <li @click="browse('customer')" v-if="user.enterprise_id">
            <router-link to="/customer" class="customer">施主</router-link>
          </li>
          <li>
            <router-link to="/TestView" class="customer">Test</router-link>
          </li>
          <!--<li><router-link to="/foobar" class="process">工程</router-link></li>-->
          <!--<li><router-link to="/report" class="report">日報</router-link></li>-->
          <!--<li><router-link to="/document" class="document">書類</router-link></li>-->
          <!--<li><router-link to="/job" class="Job_offer">求人</router-link></li>-->
          <!--<li><router-link to="/camera" class="camera">カメラ</router-link></li>-->
          <!--<li><router-link to="/project" class="question">教えて</router-link></li>-->
          <!--<li><router-link to="/foobar" class="clock">タイムカード</router-link></li>-->
          <!--<li><router-link to="/foobar" class="management">その他の管理</router-link></li>-->
          <!--<li><router-link to="/setting" class="setup">設定</router-link></li>-->
        </ul>
      </nav>
    </div>
    <!--/nav-wrapper-->
    <RouterView />
  </div>
</template>
<script>
  export default {
    data() {
      return {
        user: {},
        reportFlag: false,
      }
    },
    methods: {
      setImgSizeSession(){
        axios.post('/api/getFileSize').then((res) => {
          let data = res.data;
          let file = [];
          if (data && data.length){
            data.filter(item=>{
              if(item && item.file_name){
                file.push({
                  id : item.id,
                  fileName : item.file_name,
                  fileSize : item.file_size,
                  groupId : item.group_id,
                });
              }
            });
          }
          window._group_file = file;
        })

      },
        browse:function(name){
            let id=name;
            axios.post("/api/setBrowse", {id:id
            }).then(res => {
            });
        },
      fetch: function () {
        axios.get('/api/getEnterprisesList').then((res) => {
          let data = res.data.user[0];
          this.user = {};
          this.user.enterprise_id = data.enterprise_id;
          this.user.coop_enterprise_id = data.coop_enterprise_id;
          if (data.enterprise_id) {
            this.user.plan = data.enterprise.plan;
          }
          this.user.displayProject = data.displayProject;
        });
        //report
        axios.get('/api/getReoprtProjectList').then((res) => {
          res.data.model.length > 0 ? this.reportFlag = true : this.reportFlag = false;
        });
      },
    },
    created() {
      this.setImgSizeSession();
      this.fetch();
    }
  }
</script>
