<template>
  <div class="container clearfix customer customerlist commonAll report">
    <header>
      <h1>
        <router-link to="/report">
          <div class="commonLogo">
            <ul>
              <li class="bold">REPORT</li>
              <li>簡易レポート</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>簡易レポート一覧</h2>
        <a class="title-add">
          <img src="images/add@2x.png" @click.prevent="openCreate"/>
        </a>

        <div class="flo title-form">
          <el-input placeholder="案件名"
                    @change="search(0)" v-model="keyword" suffix-icon="searchForm-submit">
          </el-input>
        </div>
      </div>
      <UserProfile/>
    </header>
    <section class="customer-wrapper report-form delet-img">
      <!-- ページネーション -->
      <div class="countDiv-report">{{total}} 件中 {{from}} 〜 {{to}} 件表示
        <a @click="fetchReports(1)">全件表示する</a>
      </div>
      <div>
        <a class="title-del" v-if="delReportItem.length !== 0" @click.prevent="delReportItems">
          <img class="img-delete" src="images/icon-dust2.png" alt="">
        </a>
      </div>

      <div class="customertable">
        <div class="list-del-common">
          <ul class="list-del-header report-serch clearfix">
            <li class="friend-li">&nbsp;</li>
            <li class="friend-li">{{ reportName('name') }}</li>
            <li class="friend-li">{{ reportName('file_date')}}<span @click="sort('file_date')"><i class="el-icon-d-caret"></i></span></li>
            <li class="friend-li">{{ reportName('user_name') }}</li>
            <li class="friend-li">{{ reportName('location') }}</li>
            <li class="friend-li">{{ reportName('updated_by') }}</li>
            <li class="friend-li">{{ reportName('updated_at') }}</li>
          </ul>
        </div>
        <ul class="table-scroll tablelisthover">
          <el-checkbox-group v-model="delReportItem">
            <li :id="'s'+report.id" class="clearfix btns" date-tgt="wd1" v-for="report in reports"
                :key="'report+'+report.id">
              <span class="friend-li"><el-checkbox :label="report.id">&nbsp;</el-checkbox></span>
              <span class="friend-li" @click="reportDetail(report.id)">{{ checkProjectName(report) }}</span>
              <span class="friend-li" v-if="report.file_date" @click="reportDetail(report.id)">{{ report.file_date }}</span>
              <span class="friend-li" v-else @click="reportDetail(report.id)"> </span>
              <span class="friend-li" @click="reportDetail(report.id)" v-if="report.report_user_name">{{ report.report_user_name }}</span>
              <span class="friend-li" @click="reportDetail(report.id)" v-else></span>
              <span class="friend-li" @click="reportDetail(report.id)" v-if="report.location">{{ report.location }}</span>
              <span class="friend-li" @click="reportDetail(report.id)" v-else></span>
              <span class="friend-li" @click="reportDetail(report.id)" v-if="report.updated_by">{{ report.updated_by.name }}</span>
              <span class="friend-li" @click="reportDetail(report.id)" v-else></span>
              <span class="friend-li" @click="reportDetail(report.id)">{{ report.updated_at }}</span>
            </li>
          </el-checkbox-group>
        </ul>
      </div>
      <div class="pagination-center">
        <el-pagination
                prev-text="◀"
                next-text="▶"
                background
                layout="prev, pager, next"
                @current-change="changePage" :current-page="currentPage" :page-size="perPage"
                :total="total">
        </el-pagination>
      </div>
    </section>

    <ReportCreate v-if="this.createFlag" @closeProject="closeProject"></ReportCreate>

  </div>
</template>

<script>
  import reportName from '../../mixins/ReportCols';
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import ReportCreate from "../../components/report/ReportCreate";

  export default {
    components: {
      ReportCreate,
      Pagination,
      UserProfile
    },
    mixins: [reportName, Messages],
    data() {
      return {
        reports: null,
        total: 1,
        from: 0,
        to: 0,
        perPage: 10,
        currentPage: 1,
        lastPage: 1,
        current: 1,
        deleted: false,
        sortCol: "updated_at",
        order: "desc",
        keyword: '',
        createFlag: false,
        delReportItem: []
      }
    },
    methods: {
      //ドナーリストを取得する
      fetchReports: function (pageNumber = null) {
        return new Promise((resolve, reject) => {
          this.keyword = '';
          let errMessage = this.commonMessage.error.reportList;
          let data = {};
          if (pageNumber != null) {
            //fetch page 1 data
            data = {params: {page: pageNumber}};
          } else {
            data = {params: {page: this.currentPage}};
          }
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.get('/api/report', data).then((res) => {
            this.reports = res.data.data;
            this.currentPage = res.data.current_page;
            this.lastPage = res.data.last_page;
            this.total = res.data.total;
            this.from = res.data.from ? res.data.from : 0;
            this.to = res.data.to ? res.data.to : 0;
            loading.close();
            resolve();
          }).catch(action => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
            reject();
          });
        });
      },
      //序列
      sort(sortCol) {
        if (this.sortCol === sortCol) {
          this.changeOrder();
        } else {
          this.sortCol = sortCol;
          this.order = 'asc'
        }
        let errMessage = this.commonMessage.error.reportList;
        let data = {params: {page: this.currentPage, sortCol: this.sortCol, order: this.order, keyword: this.keyword}};
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/report', data).then((res) => {
          this.reports = res.data.data;
          this.currentPage = res.data.current_page;
          this.lastPage = res.data.last_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
          loading.close();
        }).catch(action => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      //ソート条件の変更
      changeOrder() {
        if (this.order === 'asc') {
          this.order = 'desc';
        } else {
          this.order = 'asc';
        }
      },
      //ページを切り替える
      changePage: function (page) {
        this.currentPage = page;
        if (this.keyword) {
          this.search(1);
        } else {
          this.fetchReports();
        }
      },
      //トランジスター詳細インターフェース
      reportDetail(id) {
        this.$router.push({path: '/report/detail/' + id});
      },
      //キーワード検索
      search(changePage) {
        //changePage = 0 -> search
        //changePage = 1 -> ページを切り替える
        if (changePage == 0) {
          //search
          this.currentPage = 1;
        }
        let errMessage = this.commonMessage.error.reportList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let data = {params: {page: this.currentPage, sortCol: this.sortCol, order: this.order, keyword: this.keyword}};
        axios.get('/api/report', data).then((res) => {
          this.reports = res.data.data;
          this.currentPage = res.data.current_page;
          this.lastPage = res.data.last_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      openCreate() {
        this.createFlag = true;
      },
      closeProject() {
        this.createFlag = false;
      },
      delReportItems() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.delete;
        let errMessageFetchList = this.commonMessage.error.reportList;
        this.$confirm(this.commonMessage.confirm.delete.report).then(() => {
          axios.post("/api/deleteReport", {
            id: this.delReportItem,
          }).then(res => {
            this.delReportItem = [];
            this.fetchReports().then( res => {
              if (this.lastPage < this.currentPage) {
                this.currentPage = this.lastPage;
                this.fetchReports();
              }
            }).catch(error => {
              this.$alert(errMessageFetchList, {showClose: false});
            });
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            this.delReportItem = [];
            loading.close();
          });
        }).catch(action => {
          this.delReportItem = [];
          loading.close();
        });
      },
      checkProjectName(report){
        //init report list
        if (report.project_name) {
          //check:report table project_name col存在
          return report.project_name
        } else {
          //default:project table place_name col
          return report.project.place_name;
        }
      },
    },
    created() {
      this.fetchReports();
    },
  }
</script>
<style scoped>
  .flo {
    float: left;
  }

  .title-form {
    width: auto;
  }

</style>
