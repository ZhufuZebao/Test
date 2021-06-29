<template>
  <div class="container clearfix  dashboard datapoper commonAll">
    <header>
      <h1>
        <router-link to="/dashboard">
          <div class="commonLogo">
            <ul>
              <li class="bold">DASHBOARD</li>
              <li>ダッシュボード</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/dashboard' }"><span>ダッシュボード</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>システムお知らせ詳細</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <!-- 機能タイトルヘッダー -->
      <UserProfile/>
    </header>
    <section class="dashboard-list dashboard-first notice-detail">
      <div class="el-tabs__nav-scroll">
        <p class="dashboard-title notice-title">{{dashboard.title}}</p>
        <p class="dashboard-content">{{dashboard.st_date.split('-')[0]+'.'+
          dashboard.st_date.split('-')[1]+'.'+
          dashboard.st_date.split('-')[2]}}</p>
        <p class="dashboard-content">{{dashboard.content}}</p>
      </div>
    </section>
  </div>
</template>
<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../mixins/Messages";
    import Calendar from "../../mixins/Calendar";

    export default {
        components: {
            UserProfile
        },
        mixins: [Messages, Calendar],
        data: function () {
            return {
                dashboard: {}
            }
        },
        methods: {
            fetchNoticeDetail() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/getSysNoticeDetail', {
                    id: this.$route.params.id
                }).then((res) => {
                    this.dashboard = res.data[0];
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
            }
        },
        mounted() {
            this.fetchNoticeDetail();
        }
    }
</script>
<style scoped>
  .dashboard-list {
    margin-left: 20px;
    background: #FFFFFF 0% 0% no-repeat padding-box;
    box-shadow: 0px 0px 20px #00000033;
    border: 1px solid #EEEEEE;
    border-radius: 10px;
    opacity: 1;
  }

  .dashboard-content {
    margin-top: 10px;
    margin-left: 20px;
    text-align: left;
    letter-spacing: 0;
    opacity: 1;
  }

  .dashboard-title {
    display: flex;
    margin-top: 20px;
    text-align: left;
    font: Bold 24px/24px YuGothic;
    letter-spacing: 0;
    color: #252429;
    opacity: 1;
  }

  .notice-detail {
    margin-top: 150px;
    margin-right: 20px;
    min-height: auto;
    max-height: 800px;
  }

  .notice-title {
    padding-left: 550px;
  }
</style>

