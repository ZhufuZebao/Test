<template>
  <!--container-->
  <div class="container clearfix  partner customerlist commonAll">
    <header>
      <h1>
        <a>
          <div class="commonLogo" @click="toCompany">
            <ul>
              <li class="bold">COMPANY</li>
              <li>会社情報</li>
            </ul>
          </div>
        </a>
      </h1>
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/company',query:{flag: 'success'}}"><span>会社情報</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>会社情報新規登録</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--会社基本情報-->
    <CompanyBasisInformation v-if="projectCreateFlag.companyBasisShow" :companyBasis="companyBasis"
                             :projectCreateFlag="projectCreateFlag"
    ></CompanyBasisInformation>
  </div>
</template>
<script>
    import CompanyBasisInformation from '../../components/company/CompanyBasisInformation.vue'
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../mixins/Messages";

    export default {
        name: "CompanyCreate",
        mixins: [
            Messages
        ],
        components: {
            UserProfile,
            CompanyBasisInformation,

        },
        data: function () {
            return {
                closed: false,
                projectCreateFlag: {
                    companyBasisShow: true,
                    projectCreate: true,
                },
                companyBasis: {
                    //  customer:[],
                    localeChief: [],
                },
                //projectCompany: {},
                // projectSafety: {
                //     tradesChief: [],
                //     hospital: [],
                // },
            }
        },
        methods: {
            //会社情報一覧画面を移動する
            toCompany: function () {
                this.$router.push({path: '/company',query:{flag: 'success'}});
            },

            //「company」テーブルにを登録
            commit: function () {
                this.isSubmit = true;
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let url;
                let data = new FormData();
                let headers = {headers: {"Content-Type": "multipart/form-data"}};
                if(!this.companyBasis.pref){
                    this.companyBasis.pref=''
                }
                if(!this.companyBasis.town){
                    this.companyBasis.town=''
                }
                if(!this.companyBasis.street){
                    this.companyBasis.street=''
                }
                if(!this.companyBasis.house){
                    this.companyBasis.house=''
                }
                if(!this.companyBasis.zip){
                    this.companyBasis.zip=''
                }
                if(!this.companyBasis.tel){
                    this.companyBasis.tel=''
                }
                if(!this.companyBasis.fax){
                    this.companyBasis.fax=''
                }
                if(!this.companyBasis.remarks){
                    this.companyBasis.remarks=''
                }
                let newcompanyBasis = JSON.parse(JSON.stringify(this.companyBasis));
                delete newcompanyBasis.localeChief;
                data.append('companyBasis', JSON.stringify(newcompanyBasis));
                data.append('localeChief', JSON.stringify(this.companyBasis.localeChief));
                let errMsg = this.commonMessage.error.companyCreate;
                url = '/api/setCompany';
                axios.post(url, data, headers).then(res => {
                    if (res.data.result === 0) {
                        this.$alert(res.data.params, {showClose: false}).then(() => {
                            this.$router.push({path: "/company",query:{flag: 'success'}});
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
                                this.$router.push({path: "/company",query:{flag: 'success'}});
                            }
                        }
                    }, 1000)
                }
            }
        }
    }
</script>
