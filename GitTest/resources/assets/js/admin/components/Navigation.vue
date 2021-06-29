<template>
  <div>
    <!--nav-wrapper-->
    <div class="nav-wrapper">
      <div class="logo"></div>
      <nav>
        <ul>
          <!--<li><router-link to="/dashboard" class="dashboard">ダッシュボード</router-link></li>-->
          <li><router-link to="/notice"  class="notice">お知らせ</router-link></li>
          <li><router-link to="/contract"  class="contract">契約者</router-link></li>
          <li><router-link to="/worker" class=" worker">職人</router-link></li>

          <!--<li><router-link to="/report" class="report">簡易レポート</router-link></li>-->
          <!--<li><router-link to="/invite" class="partner">協力会社</router-link></li>-->
          <!--<li><router-link to="/friend" class="friend">職人</router-link></li>-->
          <!--<li v-if="((user.enterprise_id && user.plan>1) || (user.coop_enterprise_id && user.displayProject && !user.enterprise_id)||user.displayProject)">-->
            <!--<router-link to="/project" class="project">案件</router-link>-->
          <!--</li>-->
          <!--<li v-if="user.enterprise_id && user.plan>1"><router-link to="/customer" class="customer">施主</router-link></li>-->
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
    <RouterView class="admin" />
  </div>
</template>
<script>
  export default {
    data() {
      return {
        user: {},
      }
    },
    methods: {
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
        })
      },
    },
    created() {
      this.fetch();
    }
  }
</script>
