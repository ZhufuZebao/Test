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
      <div class="title-wrap project-info-top project-info-top-updata">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/project',query:{flag: 'success'} }"><span>案件一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/project/show/'+projectBasis.id} "><span>案件情報：{{projectBasis.place_name}}</span></el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/project/details/'+projectBasis.id} "><span>案件詳細：{{projectBasis.place_name}}</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <ProjectBasisInformation :projectBasis="projectBasis" :updateAllFlag="updateAllFlag"
                             ref="projectBasisCom" @updateFile="updateFile"></ProjectBasisInformation>
    <ProjectCompanyInformation :projectCompany="projectCompany" :updateAllFlag="updateAllFlag"
                               ref="projectCompanyCom" class="projectUpadta"></ProjectCompanyInformation>
    <ProjectSafetyInformation :projectSafety="projectSafety"
                              :updateAllFlag="updateAllFlag" ref="projectSafetyCom"
                              @hospitalDelFlag="hospitalDelFlag" class="projectUpadta"></ProjectSafetyInformation>
    <div class="pro-button allUpdateButton projectupdataButton">
      <a class="back" @click="returnProjectDetail">
        戻る
      </a>
      <a class="up" @click="submitFormAll">保存</a>
      <a class="del" @click="deletes" v-if="showDelButton">削除</a>
    </div>
  </div>

</template>
<script>
  import ProjectBasisInformation from '../../components/project/ProjectBasisInformation.vue'
  import ProjectCompanyInformation from '../../components/project/ProjectCompanyInformation.vue'
  import ProjectSafetyInformation from '../../components/project/ProjectSafetyInformation.vue'
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectUpdate",
    mixins: [
      Messages,
    ],
    components: {
      UserProfile,
      ProjectBasisInformation,
      ProjectCompanyInformation,
      ProjectSafetyInformation,
    },
    data: function () {
      return {
        showDelButton:false,
        proPlaceName: null,
        closed: false,
        updateAllFlag: true,
        projectBasis: {
          localeChief: [],
          customer:[],
        },
        projectCompany: {},
        projectSafety: {
          hospital: [],
          tradesChief: [],
        },
        customerData: {},
        projectBasisComFlag: true,
        projectCompanyComFlag: true,
        projectSafetyComFlag: true,
        isUpdateFlag: true,
        doSubmitForm: false,
        doZipCheck: false,
        zipFlag: '',
        zipTmp: {},
      }
    },
    methods: {
      toProject: function () {
        this.$router.push({path: '/project',query:{flag: 'success'}});
      },
      //戻る「案件詳細」ページ
      returnProjectDetail: function () {
        this.$router.push({path: '/project/details/' + this.$route.params.id});
      },
      //工種別責任者を削除flag
      hospitalDelFlag: function (flag) {
        this.hospitalDelFlag = flag;
      },
      //案件画像を変更
      updateFile: function (file) {
        this.rawFile = file;
      },
      //電話番号をセット
      setTel: function () {
        if (this.projectBasis.telIn === undefined) {
          this.projectBasis.telIn = '';
        }
        if (this.projectBasis.telIn !== "" && this.projectBasis.telOut !== "") {
          this.projectBasis.tel = this.projectBasis.telOut + '-' + this.projectBasis.telIn;
        } else if (this.projectBasis.telIn === "" && this.projectBasis.telOut !== "") {
          this.projectBasis.tel = this.projectBasis.telOut;
        } else if (this.projectBasis.telOut === "" && this.projectBasis.telIn !== "") {
          this.projectBasis.tel = this.projectBasis.telIn;
        } else {
          this.projectBasis.tel = "";
        }
      },
      //案件を削除する
      deletes: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.delete;
        this.$confirm(this.commonMessage.confirm.delete.project(this.proPlaceName),
            { dangerouslyUseHTMLString: true,
              customClass: 'project-delete-confirm',
              confirmButtonText: this.commonMessage.button.ok,
              cancelButtonText: this.commonMessage.button.cancel
            }).then(() => {
          axios.post("/api/deleteProject", {
            id: this.$route.params.id
          }).then(res => {
            if (res.data > 0) {
              // 仕様変更 #1316 案件削除時のドキュメント管理削除
              if (process.env.MIX_DOC_URL) {
                axios.post(process.env.MIX_DOC_URL + "api/deleteProjectFile", {project_id: this.$route.params.id});
              }
              this.$alert(this.commonMessage.success.delete, {showClose: false}).then(() => {
                loading.close();
                this.$router.push({path: "/project",query:{flag: 'success'}});
                this.closed = true;
              });
              this.deferGo();
            } else {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            }
          }).catch(action => {
            loading.close();
          });
        }).catch(action => {
          loading.close();
        });
      },
      //案件情報を表示
      fetchProjectDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getProjectObject", {
          params: {
            id: this.$route.params.id
          }
        }).then(res => {
          let enterpriseId=res.data.enterpriseId;
          res.data=res.data.model;
          this.zipFlag = res.data.zip;
          this.zipTmp = {'address1':res.data.pref,'address2':res.data.town,'address3':res.data.street,prefcode: '1',zipcode: res.data.zip};
          if (res.data.enterprise_id===enterpriseId){
            this.showDelButton=true;
          }
          //案件基本情報
          this.proPlaceName = res.data.place_name;
          this.$set(this.projectBasis, 'id', res.data.id);
          this.$set(this.projectBasis, 'place_name', res.data.place_name);
          // #2790
          // this.$set(this.projectBasis, 'construction_name', res.data.construction_name);
          this.$set(this.projectBasis, 'project_no', res.data.project_no);
          this.$set(this.projectBasis, 'zip', res.data.zip);
          this.$set(this.projectBasis, 'pref', res.data.pref);
          this.$set(this.projectBasis, 'town', res.data.town);
          this.$set(this.projectBasis, 'street', res.data.street);
          this.$set(this.projectBasis, 'house', res.data.house);
          this.$set(this.projectBasis, 'tel', res.data.tel);
          this.$set(this.projectBasis, 'fax', res.data.fax);
          this.$set(this.projectBasis, 'construction', res.data.construction);
          this.$set(this.projectBasis, 'customer_id', res.data.customer_id);
          if(!res.data.subject_image){
            res.data.subject_image="images/no-image.png";
            this.$set(this.projectBasis, 'subject_image', res.data.subject_image);
          }else{
            this.$set(this.projectBasis, 'subject_image', res.data.subject_image);
          }
          this.$set(this.projectBasis, 'progress_status', res.data.progress_status);
          this.$set(this.projectBasis, 'progress_special_content', res.data.progress_special_content);
          this.$set(this.projectBasis, 'building_type', res.data.building_type);
          this.$set(this.projectBasis, 'st_date', res.data.st_date);
          this.$set(this.projectBasis, 'ed_date', res.data.ed_date);
          this.$set(this.projectBasis, 'open_date', res.data.open_date);
          this.$set(this.projectBasis, 'telOut', res.data.tel);
          this.$set(this.projectBasis, 'telIn',  res.data.tel_in);
          //管理会社・不動産屋 情報
          this.$set(this.projectCompany, 'mng_company_name', res.data.mng_company_name);
          this.$set(this.projectCompany, 'mng_company_address', res.data.mng_company_address);
          this.$set(this.projectCompany, 'mng_company_tel', res.data.mng_company_tel);
          this.$set(this.projectCompany, 'mng_company_chief', res.data.mng_company_chief);
          this.$set(this.projectCompany, 'mng_company_chief_position', res.data.mng_company_chief_position);
          this.$set(this.projectCompany, 'realtor_name', res.data.realtor_name);
          this.$set(this.projectCompany, 'realtor_address', res.data.realtor_address);
          this.$set(this.projectCompany, 'realtor_tel', res.data.realtor_tel);
          this.$set(this.projectCompany, 'realtor_chief', res.data.realtor_chief);
          this.$set(this.projectCompany, 'realtor_chief_position', res.data.realtor_chief_position);
          this.$set(this.projectCompany, 'site_area', res.data.site_area);
          this.$set(this.projectCompany, 'floor_area', res.data.floor_area);
          this.$set(this.projectCompany, 'floor_numbers', res.data.floor_numbers);
          this.$set(this.projectCompany, 'construction_company', res.data.construction_company);
          this.$set(this.projectCompany, 'construction_special_content', res.data.construction_special_content);

          //安全管理情報
          this.$set(this.projectSafety, 'security_management_tel', res.data.security_management_tel);
          this.$set(this.projectSafety, 'security_management_chief', res.data.security_management_chief);
          this.$set(this.projectSafety, 'security_management_deputy', res.data.security_management_deputy);
          this.$set(this.projectSafety, 'fire_station_name', res.data.fire_station_name);
          this.$set(this.projectSafety, 'fire_station_chief', res.data.fire_station_chief);
          this.$set(this.projectSafety, 'fire_station_tel', res.data.fire_station_tel);
          this.$set(this.projectSafety, 'police_station_name', res.data.police_station_name);
          this.$set(this.projectSafety, 'police_station_chief', res.data.police_station_chief);
          this.$set(this.projectSafety, 'police_station_tel', res.data.police_station_tel);

          axios.get("/api/getProjectDetail", {
            params: {
              id: this.$route.params.id
            }
          }).then(res => {
            if (res.data[0]){
              this.projectBasis.localeChief = res.data[0].project_locale_chief;
              this.projectSafety.tradesChief = res.data[0].project_trades_chief;
              this.projectSafety.hospital = res.data[0].project_hospital;
              this.projectBasis.customer = res.data[0].customer_office;
            }
          }).catch(action => {
            loading.close();
          });
        }).catch(action => {
          loading.close();
        });
        loading.close();
      },
      //案件基本情報、管理会社・不動産屋 情報、安全管理情報を検証
      submitFormAll: function () {
        this.doSubmitForm = true;
        if(this.doZipCheck){
          return;
        }
        //browser check
        let explorer =navigator.userAgent;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs.projectBasisCom.isSubmit = true;
        //案件基本情報を検証
        this.$refs.projectBasisCom.$refs['form'].validate((validBasisCom) => {
          if (validBasisCom) {
            //管理会社・不動産屋 情報を検証
            this.$refs.projectCompanyCom.$refs['form'].validate((validCompanyCom) => {
              if (validCompanyCom) {
                //安全管理情報を検証
                this.$refs.projectSafetyCom.$refs['form'].validate((validSafetyCom) => {
                  if (validSafetyCom) {
                    this.saveAll();
                  } else {
                    this.doSubmitForm = false;
                    document.body.scrollTop = 0;
                    document.documentElement.scrollTop = 0;
                    let isError = document.getElementsByClassName("is-error");
                    isError[0].querySelector('input').focus();
                    loading.close();
                  }
                });
              } else {
                this.doSubmitForm = false;
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                let isError = document.getElementsByClassName("is-error");
                isError[0].querySelector('input').focus();
                loading.close();
              }
            });
          } else {
            this.doSubmitForm = false;
            loading.close();
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
              if (explorer.indexOf("Firefox") >= 0) {
                document.body.scrollTop  = $("div").scrollTop()+140;
                document.documentElement.scrollTop  = $("div").scrollTop()+140;
              }
              this.$refs.projectBasisCom.isSubmit = false;
            }, 1);
          }
        });
      },
      // 案件基本情報、管理会社・不動産屋 情報、安全管理情報を変更
      saveAll() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let url;
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        let updateAllFlag = true;
        this.setTel();
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
        this.projectBasis.customer.map(x => {
          x.id = x.pivot.customer_id;
          return x;
        });
        this.projectBasis.address = this.projectBasis.pref + this.projectBasis.town +
            this.projectBasis.street + this.projectBasis.house;
        let newProjectBasis = JSON.parse(JSON.stringify(this.projectBasis));
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
        data.append('hospitalDelFlag', JSON.stringify(this.hospitalDelFlag));
        data.append('updateAllFlag', JSON.stringify(updateAllFlag));

        if (this.rawFile) {
          data.append("file", this.rawFile);
        }
        let errMsg = this.commonMessage.error.insert;
        url = '/api/editProject';
        data.append('id', this.$route.params.id);
        errMsg = this.commonMessage.error.projectEdit;
        let $this = this;
        axios.post(url, data, headers).then(res => {
          this.doSubmitForm = false;
          if (res.data.result === 0) {
            $this.$alert(res.data.params, {showClose: false}).then(() => {
              loading.close();
              this.$router.push({path: '/project/details/' + this.$route.params.id});
              this.closed = true;
              this.proPlaceName = this.projectBasis.place_name;
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
    },
    created() {
      this.fetchProjectDetail();

    }
  }
</script>
