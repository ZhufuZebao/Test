<template>
  <div class="container clearfix  projectlist datapoper commonAll">
    <header>
      <h1>
        <router-link to="/project/">
          <div class="commonLogo">
            <ul>
              <li class="bold">PROJECT</li>
              <li>案件</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap">
        <h2>案件一覧</h2>
        <div class="title-add" v-if="addProEvent">
          <router-link to="/project/create"><img src="images/add@2x.png"/></router-link>
        </div>
        <el-form :model="project" label-width="200px" class="title-form">
          <el-form-item cenctype="multipart/form-data">
            <el-select v-model="project.progress_status" class='schedule-project' @change="fetchProjects(1)" placeholder="進捗状況">
              <el-option label="すべて" value=" "></el-option>
              <el-option v-for="progress in progressStatus" :key="progress.id" :label="progress.name"
                         :value="progress.id"></el-option>
            </el-select>
          </el-form-item>
          <div class="search">
            <el-input placeholder="案件名・場所・案件Noなど"
                      @change="fetchProjects(1)" v-model="input2" suffix-icon="searchForm-submit">
            </el-input>
          </div>
        </el-form>
        <div class="button-s" hidden @click.prevent="">詳細検索</div>
      </div>
      <UserProfile/>
    </header>
    <section class="common-wrapper">
      <!-- 案件一覧テーブル表示 -->
      <div class="b-head">
        <span>{{from}} 〜 {{to}} 件を表示/全{{total}} 件</span>
        <!--<a href="javascript:void(0)" @click="openProjectsMapModal"> <img src="images/icon-map.png" alt=""><span>地図上に表示する</span></a>-->
      </div>
      <ul class="project-list project-view clearfix">
        <li v-for="project in projects" v-bind:key="project.id">
          <div class="project-list-item clearfix btns" date-tgt="wd1" @click="projectShow(project)">
            <dl class="project-list-item-img-dl">
              <img   v-if="project.subject_image&&!project.subject_image.match('images')" v-bind:src="'file/projects/'+project.subject_image">
              <img  v-else src="images/no-image.png" >
            </dl>
            <dl class="clearfix">
              <dt>案件名 :</dt>
              <dd>{{project.place_name}}</dd>
              <dt>案件No :</dt>
              <dd v-if="project.project_no">{{project.project_no}}</dd>
              <dd v-else></dd>
              <dt>進捗状況 :</dt>
              <dd class="colorChange">
                <span :class="color(project.progress_status)" v-if="project.progress_status">
                  {{progressStatusName(project.progress_status)}}
              </span>
              </dd>
              <dt>現場担当者 :</dt>
              <dd class="colorChange">
                <span v-for="localChiefName in project.project_locale_chief">
                  {{localChiefName.name}}
                  <span v-if="localChiefName.name">さん
                  </span>
                </span>
              </dd>

            </dl>
          </div>
        </li>
      </ul>
      <!-- 案件一覧のページング -->
      <div class="pagination-center">
        <el-pagination
                prev-text="◀"
                next-text="▶"
                background
                layout="prev, pager, next"
                :current-page="currentPage"
                @current-change="changePage" :page="currentPage" :page-size="perPage"
                :total="total">
        </el-pagination>
      </div>
    </section>
    <!-- 施主詳細情報画面 -->
    <ProjectDetailModal :project="postDetail" @closeModal="closeDetailModal" v-if="showDetailModal"/>
    <ProjectMapModal :project="postMap" @closeModal="closeMapModal" v-if="showMapModal"/>
    <ProjectSearchModal :project="postSearch" @closeSearchModal="closeSearchModal" @closeModal="closeModal"
                        v-if="showSearchModal"/>
    <!-- 工程管理画面 -->
    <ProjectManagerModal :project="postManage" @closeModal="closeManageModal" v-if="showManageModal"/>
  </div>
</template>
<script>
  import ProjectDetailModal from '../../components/project/ProjectDetailModal';
  import ProjectMapModal from '../../components/project/ProjectMapModal';
  import ProjectSearchModal from "../../components/project/ProjectSearchModal";
  import ProjectManagerModal from "../../components/project/ProjectManagerModal";
  import Pagination from "../../components/common/Pagination";
  import ProjectList from "../../mixins/ProjectLists";
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";

  export default {
    mixins: [ProjectList,Messages],
    components: {
      ProjectDetailModal,
      ProjectMapModal,
      ProjectSearchModal,
      ProjectManagerModal,
      Pagination,
      UserProfile
    },
    data() {
      return {
        addProEvent:false,
        input2: '',
        style: '',
        count: 0,
        countNumber: 0,
        projects: [],       //案件データ
        showDetailModal: false,//詳細モーダル表示フラグ
        postDetail: null,      //詳細モーダルへ渡すデータ
        showMapModal: false,//詳細モーダル表示フラグ
        showManageModal: false,//詳細モーダル表示フラグ
        postMap: null,
        showSearchModal: false,
        currentPage: 1,        //現在ページ
        perPage: 10,           //一ページの件数
        total: 1,               //全件数
        from: 0,
        to: 0,
        project: {
          place_name: '',
          progress_status: '',
        },
        localChiefName:[]
      }
    },
    methods: {
      //進度状況の色をセット
      color: function (projectProgressStatus) {
        return {
          progressStatus1: projectProgressStatus === '1' ||
              projectProgressStatus === '2' ||
              projectProgressStatus === '3' ||
              projectProgressStatus === '4' ||
              projectProgressStatus === '7'||
              projectProgressStatus === '8',
          progressStatus2: projectProgressStatus === '5' ||
              projectProgressStatus === '6',
          progressStatus3: projectProgressStatus === '9'
        }
      },
      //進度条の色をセット
      borderColor: function (projectProgressStatus) {
        return {
          progressBorderColor1: projectProgressStatus === '1' ||
              projectProgressStatus === '2' ||
              projectProgressStatus === '3' ||
              projectProgressStatus === '4' ||
              projectProgressStatus === '7'||
              projectProgressStatus === '8',
          progressBorderColor2: projectProgressStatus === '5' ||
              projectProgressStatus === '6',
          progressBorderColor3: projectProgressStatus === '9'
        }
      },
      //ページをチェンジ
      changePage: function (page) {
        this.currentPage = page;
        this.fetchProjects();
      },
      //案件を検索 type:1=>ステータスと検索のテキストを改修します;' '=>変更はありません
      fetchProjects: function (type) {
        let errMsg = this.commonMessage.error.projectList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        if(type === 1){
          this.currentPage = 1;
        }
        let data = {
          page: this.currentPage,
          keyword: this.input2,
          progressStatus: this.project.progress_status
        };
        axios.post('/api/getProjectList', data).then((res) => {
          this.formatArray(res.data.model);
          if (res.data.enterpriseId){
            //res.data.enterprisePlan === 2の場合、「スタンダードプラン」です
            this.addProEvent=true;
          }
          loading.close();
        }).catch(error => {
          this.$alert(errMsg, {showClose: false});
          loading.close();
        });

      },
      //施主情報を開ける
      openDetailModal(project) {
        this.postDetail = project;
        this.showDetailModal = true;
      },
      //施主情報を閉め
      closeDetailModal() {
        this.showDetailModal = false;
      },
      //工程管理画面を開ける
      closeModal() {
        this.showSearchModal = false;
      },
      //場所モデルを開ける
      openMapModal(project) {
        this.postMap = project;
        this.showMapModal = true;
      },
      //場所モデルを閉め
      closeMapModal() {
        this.showMapModal = false;
      },
      //検索モデルを開ける
      closeSearchModal($event) {
        this.showSearchModal = false;
        this.formatArray($event);
        this.count = $event.data.length;
      },
      //施工期間モデルを開ける
      openManageModal(project) {
        this.postManage = project;
        this.showManageModal = true;
      },
      //施工期間モデルを閉め
      closeManageModal() {
        this.showManageModal = false;
      },
      //案件データを処理
      formatArray(res) {
        let data = [];
        if (res.data) {
          for (let i = 0; i < res.data.length; i++) {
            let pro = res.data[i];
            //施工期間の日付格式を修正
            if(pro.st_date){
              pro.st_date = (new Date(pro.st_date)).getFullYear() + "/" +
                  ((new Date(pro.st_date)).getMonth() + 1) + "/" +
                  (new Date(pro.st_date)).getDate();
            }
            if(pro.ed_date){
              pro.ed_date = (new Date(pro.ed_date)).getFullYear() + "/" +
                  ((new Date(pro.ed_date)).getMonth() + 1) + "/" +
                  (new Date(pro.ed_date)).getDate();
            }
            if (!pro.customer) {
              pro.customer = {};
            }
            if (!pro.customer_office) {
              pro.customer_office = {};
            }
            // if (!pro.user) {
            //   pro.user = {};
            // }
            //施工期間によって、現時点がどのくらい位置を表示する
            let $pro_course = ((new Date(pro.ed_date)) - (new Date(pro.st_date)));
            let $now_course = ((new Date()) - (new Date(pro.st_date)));
            pro.progress = $now_course / $pro_course;
            if ($now_course > $pro_course) {
              pro.progress = 100
            } else if ($now_course <= 0) {
              pro.progress = 0
            } else if ($pro_course <= 0) {
              pro.progress = 100
            } else {
              pro.progress = $now_course / $pro_course * 100
            }
            data.push(pro);
          }
          this.projects = data;
          this.count = data.length;
          this.countNumber = data.length;
          this.currentPage = res.current_page;
          this.perPage = res.per_page;
          this.total = res.total;
          if (res.from) {
            this.from = res.from;
            this.to = res.to;
          } else {
            this.from = 0;
            this.to = 0;
          }
        }
      },
      //案件情報画面を移動する
      projectShow(project) {
        this.$router.push({path: '/project/show/' + project.id});
      },
      //案件Map画面を移動する
      openProjectsMapModal() {
        this.$router.push({path: '/project/map'});
      },
    },
    created() {
      this.fetchProjects()
    }
  }
</script>
