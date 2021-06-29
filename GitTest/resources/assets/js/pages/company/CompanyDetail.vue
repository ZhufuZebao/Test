<template>
  <!--container-->
  <div class="container clearfix  partner customerlist commonAll">
    <header>
      <h1>
        <router-link :to="{ path: '/company' ,query:{flag: 'success'}}">
          <div class="commonLogo">
            <ul>
              <li class="bold">COMPANY</li>
              <li>会社情報</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap project-info-top">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/company' ,query:{flag: 'success'}}"><span>会社情報一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>会社情報：{{company.name}}</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <section class="common-wrapper">
      <div class="common-wrapper-container">
        <div class="common-view project-detail">
          <CompanyBasisInformation v-if="companyBasisFlag" :companyBasis="companyBasis"
                                   :updateOneFlag="updateOneFlag"
                                   @submitFormFlag="submitFormFlag"></CompanyBasisInformation>
          <div class="content" v-if="companyBasisDetail">
            <h3>会社基本情報</h3>
            <table class="detail-table">
              <a class="edit" @click="companyBasisShow" v-if="editDetail">
                <img src="images/edit@2x.png"/>
              </a>
              <tr>
                <td class="tdLeft">{{companyName('type')}}</td>
                <td class="tdRight">{{company.type}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('name')}}</td>
                <td class="tdRight">{{company.name}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('phonetic')}}</td>
                <td class="tdRight">{{company.phonetic}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('zip')}}</td>
                <td class="tdRight">{{company.zip}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('pref')}}</td>
                <td class="tdRight">{{company.pref}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('town')}}</td>
                <td class="tdRight">{{company.town}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('street')}}</td>
                <td class="tdRight">{{company.street}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('house')}}</td>
                <td class="tdRight">{{company.house}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('tel')}}</td>
                <td class="tdRight">{{company.tel}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('fax')}}</td>
                <td class="tdRight">{{company.fax}}</td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('localeChief')}}</td>
                <td class="tdRight">
                  <div v-for="(proj,index) in company.localeChief">
                    <div class="item">
                      <div class="leftDiv"><span>{{localeChiefName('name')}}</span></div>
                      <div class="rightDiv"><span>{{proj.name}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>{{localeChiefName('position')}}</span></div>
                      <div class="rightDiv"><span>{{proj.position}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>{{localeChiefName('dept')}}</span></div>
                      <div class="rightDiv"><span>{{proj.dept}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>{{localeChiefName('tel')}}</span></div>
                      <div class="rightDiv"><span>{{proj.tel}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>{{localeChiefName('mail')}}</span></div>
                      <div class="rightDiv"><span>{{proj.email}}</span></div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="tdLeft">{{companyName('remarks')}}</td>
                <td class="tdRight">
                  <dl>{{company.remarks}}</dl>
                </td>
              </tr>
            </table>
            <div class="pro-button">
              <a class="modoru" @click="returnProjectList">
                戻る
              </a>
              <a class="henkou" @click="companyBasisShow" v-if="editDetail">
                変更
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import CompanyLists from "../../mixins/CompanyLists";
    import CompanyBasisInformation from '../../components/company/CompanyBasisInformation.vue';
    import Calendar from '../../mixins/Calendar';
    import Messages from "../../mixins/Messages";

    export default {
        name: "CompanyDetail",
        mixins: [CompanyLists,Calendar,Messages],
        components: {
            UserProfile,
            CompanyBasisInformation,
        },
        data: function () {
            return {
                editDetail:true,
                submitFlag: false,
                updateOneFlag: true,
                companyBasisDetail: true,
                companyBasisFlag: false,
                companyBasis: {
                    localeChief: [],
                    customer:[],
                },
                company: {},
                name: '',
            };
        },
        methods: {
            //子ページ「保存」をクリックの場合、flagはtrue
            submitFormFlag(flag) {
                this.submitFlag = flag;
            },
            //flagより、不同の会社情報詳細を表示
            showDetail() {
                this.fetchProject();
                this.companyBasisFlag = false;
                this.companyBasisDetail = true;
                this.submitFlag = false;
            },
            //会社情報を表示
            companyBasisShow() {
                this.companyBasisFlag = true;
                this.companyBasisDetail = false;
            },
            //戻るパタンをクリック
            returnProjectList() {
                this.$router.push({path: '/company/',query:{flag: 'success'}});
            },
            //案件についての情報を取得
            fetchProject: function () {
                let errMessage = this.commonMessage.error.companyList;
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.get("/api/getCompanyObject", {
                    params: {
                        id: this.$route.params.id
                    }
                }).then(res => {
                    res.data=res.data.model;
                    this.company = res.data;
                    this.companyBasis=res.data;
                    let typeArr=['管理会社','不動産会社', '消防署', '警察署', '病院'];
                    res.data.type=typeArr[res.data.type-1];
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
            }
        },
        created() {
            this.fetchProject();
        },
        beforeRouteLeave (to, from, next) {
            let msg = this.commonMessage.confirm.warning.jump;
            if (to.query.flag !== 'success' &&
                (to.path === '/chat'
                    || to.path === '/schedule'
                    || to.path === '/company'
                    || to.path === '/contact'
                    || to.path === '/document'
                    || to.path === '/invite'
                    || to.path === '/friend'
                    || to.path === '/project'
                    || to.path === '/customer') &&
                (this.companyBasisFlag)){
                let res = confirm(msg);
                if (res) {
                    next();
                }else{
                    return false;
                }
            }else{
                next();
            }
        },
    }
</script>
