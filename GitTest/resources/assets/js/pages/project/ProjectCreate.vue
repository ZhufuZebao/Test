<template>
  <!--container-->
  <div class="container clearfix commonAll">
    <header>
      <h1>
        <a>
          <div class="commonLogo" @click="toProject">
            <ul>
              <li class="bold">PROJECT</li>
              <li>案件</li>
            </ul>
          </div>
        </a>
      </h1>
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/project',query:{flag: 'success'}}"><span>案件一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>案件新規登録</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--案件基本情報-->
    <ProjectBasisInformation v-if="projectCreateFlag.projectBasisShow" :projectBasis="projectBasis"
                             :projectCreateFlag="projectCreateFlag"
                             @saveFile="saveFile"></ProjectBasisInformation>
    <!--管理会社・不動産屋 情報-->
    <ProjectCompanyInformation v-if="projectCreateFlag.projectCompanyShow" :projectCompany="projectCompany"
                               :projectCreateFlag="projectCreateFlag"></ProjectCompanyInformation>
    <!--安全管理情報-->
    <ProjectSafetyInformation v-if="projectCreateFlag.projectSafetyShow"
                              :projectSafety="projectSafety"
                              :projectCreateFlag="projectCreateFlag"></ProjectSafetyInformation>
  </div>
</template>
<script>
  import ProjectBasisInformation from '../../components/project/ProjectBasisInformation.vue'
  import ProjectCompanyInformation from '../../components/project/ProjectCompanyInformation.vue'
  import ProjectSafetyInformation from '../../components/project/ProjectSafetyInformation.vue'
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectCreate",
    mixins: [
      Messages
    ],
    components: {
      UserProfile,
      ProjectBasisInformation,
      ProjectCompanyInformation,
      ProjectSafetyInformation,
    },
    data: function () {
      return {
        closed: false,
        projectCreateFlag: {
          projectBasisShow: true,
          projectCompanyShow: false,
          projectSafetyShow: false,
          projectCreate: true,
        },
        projectBasis: {
          customer:[],
          localeChief: [],
        },
        projectCompany: {},
        projectSafety: {
          tradesChief: [],
          hospital: [],
        },
        isCreateFlag: true,
        doSubmitForm: false,
        doZipCheck: false,
        zipFlag: '',
        zipTmp: {},
      }
    },
    methods: {
      //案件一覧画面を移動する
      toProject: function () {
        this.$router.push({path: '/project',query:{flag: 'success'}});
      },
      //「電話番号」を処理
      setTel: function () {
        // if (this.projectBasis.telIn === undefined) {
        //   this.projectBasis.telIn = '';
        // }
        // if (this.projectBasis.telIn !== "" && this.projectBasis.telOut !== "") {
        //   this.projectBasis.tel = this.projectBasis.telOut + '-' + this.projectBasis.telIn;
        // } else if (this.projectBasis.telIn === "" && this.projectBasis.telOut !== "") {
        //   this.projectBasis.tel = this.projectBasis.telOut;
        // } else if (this.projectBasis.telOut === "" && this.projectBasis.telIn !== "") {
        //   this.projectBasis.tel = this.projectBasis.telIn;
        // } else {
        //   this.projectBasis.tel = "";
        // }
      },
      //案件画像
      saveFile: function (file) {
        this.rawFile = file;
      },
      //「project」テーブルにを登録
      commit: function () {
        this.isSubmit = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let url;
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        this.setTel();
        //案件場所
        if(!this.projectBasis.pref){
          this.projectBasis.pref=''
        }
        if(!this.projectBasis.town){
          this.projectBasis.town=''
        }
        if(!this.projectBasis.street){
          this.projectBasis.street=''
        }
        if(!this.projectBasis.house){
          this.projectBasis.house=''
        }
        this.projectBasis.address = this.projectBasis.pref + this.projectBasis.town +
            this.projectBasis.street + this.projectBasis.house;
        let newProjectBasis = JSON.parse(JSON.stringify(this.projectBasis));
        if(!newProjectBasis.customer_id){
          delete newProjectBasis.customer_id;
          delete newProjectBasis.customer_name;
          delete newProjectBasis.customer_office_id;
        }
        delete newProjectBasis.localeChief;
        let newProjectSafety = JSON.parse(JSON.stringify(this.projectSafety));
        delete newProjectSafety.tradesChief;
        delete newProjectSafety.hospital;
        data.append('projectBasis', JSON.stringify(newProjectBasis));
        data.append('projectCompany', JSON.stringify(this.projectCompany));
        data.append('projectSafety', JSON.stringify(newProjectSafety));
        data.append('localeChief', JSON.stringify(this.projectBasis.localeChief));
        data.append('tradesChief', JSON.stringify(this.projectSafety.tradesChief));
        data.append('hospital', JSON.stringify(this.projectSafety.hospital));
        if (this.rawFile) {
          data.append("file", this.rawFile);
        }
        let errMsg = this.commonMessage.error.projectCreate;
        url = '/api/setProject';
        axios.post(url, data, headers).then(res => {
          this.doSubmitForm = false;
          if (res.data.result === 0) {
            this.$alert(res.data.params, {showClose: false}).then(() => {
              this.$router.push({path: "/project",query:{flag: 'success'}});
              loading.close();
              this.closed = true;
            });
            this.deferGo();
          } else if (res.data.result === 1) {
            loading.close();
            this.$alert(res.data.errors, {showClose: false});
          }
        }).catch(error => {
          loading.close();
          this.$alert(errMsg);
        });
        this.isSubmit = false;
      },
      //自動帰る
      deferGo() {
        const TIME_COUNT = 5;
        if (!this.timer) {
          this.count = TIME_COUNT;
          this.show = false;
          this.timer = setInterval(() => {
            if (this.count > 0 && this.count <= TIME_COUNT) {
              this.count--;
            } else {
              this.show = true;
              clearInterval(this.timer);
              this.timer = null;
              if (!this.closed) {
                this.$msgbox.close();
                this.$router.push({path: "/project",query:{flag: 'success'}});
              }
            }
          }, 1000)
        }
      }
    }
  }
</script>
