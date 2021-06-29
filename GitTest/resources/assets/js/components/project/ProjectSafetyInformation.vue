<template>
  <!--container-->
  <div class="content clearfix commonAll">
    <section class="common-wrapper Nesting-page">
      <div class="common-view">
        <div class="content">
          <h3>{{projectName('securityMsg')}}</h3>
          <el-form :model="projectSafety" :rules="rules" ref="form" label-width="200px"
                   :class="[projectCreateFlag? 'formStyle' :'updateFormStyle']">
            <div class="form-group">
              <el-form-item :label="projectName('security_management_tel')" prop="security_management_tel">
                <el-input v-model="projectSafety.security_management_tel" maxlength="15" ref="firstInput"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('security_management_chief')">
                <el-input v-model="projectSafety.security_management_chief" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('security_management_deputy')">
                <el-input v-model="projectSafety.security_management_deputy" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="tradesChiefName('name')">
                <div class="form-group formadd" v-if="projectSafety.tradesChief"
                     v-for="(trades,index) in projectSafety.tradesChief"
                     v-bind:key="index">
                  <el-form-item :label="tradesChiefName('trades_type')" class="nameupdata" :rules="tradesChiefRules.trades_type">
                    <span class="trades_type" v-if="trades.trades_type">{{tradesTypeName(trades.trades_type)}}</span>
                    <span class="trades_type" v-else="trades.trades_type"></span>
                    <button @click.prevent="showTradesChief(index)">削除</button>
                  </el-form-item>
                  <el-form-item :label="tradesChiefName('trades_type_detail')"
                                :prop="'tradesChief.' + index + '.trades_type_detail'" v-if="trades.trades_type === '5'">
                    <el-input v-model="trades.trades_type_detail" maxlength="20"></el-input>
                  </el-form-item>
                  <el-form-item :label="tradesChiefName('company')" :prop="'tradesChief.' + index + '.company'">
                    <el-input v-model="trades.company" maxlength="30"></el-input>
                  </el-form-item>
                  <el-form-item :label="tradesChiefName('name')" :prop="'tradesChief.' + index + '.name'"
                                :rules="tradesChiefRules.name">
                    <el-input v-model="trades.name" maxlength="30"></el-input>
                  </el-form-item>
                  <el-form-item :label="tradesChiefName('tel')" :prop="'tradesChief.' + index + '.tel'"
                                :rules="tradesChiefRules.tel">
                    <el-input v-model="trades.tel" maxlength="15"></el-input>
                  </el-form-item>
                </div>
                <el-button type="primary" round @click.prevent="openTradesChiefModal">
                  {{projectName('tradesChiefAdd')}}
                </el-button>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('fire_station_name')">
                <el-input v-model="projectSafety.fire_station_name" maxlength="70"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('fire_station_chief')">
                <el-input v-model="projectSafety.fire_station_chief" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('fire_station_tel')" prop="fire_station_tel">
                <el-input v-model="projectSafety.fire_station_tel" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('police_station_name')">
                <el-input v-model="projectSafety.police_station_name" maxlength="70"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('police_station_chief')">
                <el-input v-model="projectSafety.police_station_chief" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('police_station_tel')" prop="police_station_tel">
                <el-input v-model="projectSafety.police_station_tel" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="hospitalName('labelName')">
                <div class="form-group formadd" v-if="projectSafety.hospital"
                     v-for="(hos,index) in projectSafety.hospital"
                     v-bind:key="index+ '-label1'">
                  <el-form-item :label="hospitalName('name')" class="nameupdata" :prop="'hospital.' + index + '.name'"
                                :rules="hospitalRules.name">
                    <el-input v-model="hos.name" maxlength="50"></el-input>
                    <button @click.prevent="showDelHospital(index)">削除</button>
                  </el-form-item>
                  <el-form-item :label="hospitalName('tel')" :prop="'hospital.' + index + '.tel'"
                                :rules="hospitalRules.tel">
                    <el-input v-model="hos.tel" maxlength="15"></el-input>
                  </el-form-item>
                </div>
                <el-button type="primary" round @click.prevent="openHospitalModal">
                  {{projectName('hospitalAdd')}}
                </el-button>
              </el-form-item>
            </div>
            <div :class="[projectCreateFlag? 'pro-button' :'updateButton']" v-if="updateOneFlag">
              <a :class="[projectCreateFlag? 'back' :'updateBack']" @click="cancel()">
                キャンセル
              </a><a :class="[projectCreateFlag? 'save' :'updateSave']" @click="submitForm">保存</a>
            </div>
          </el-form>
        </div>
        <div class="pro-button" v-if="projectCreateFlag">
          <a class="modoru" @click="projectCreateBack">戻る</a>
          <a class="nextPage" @click="login()">登録</a>
        </div>
      </div>
    </section>
    <ProjectHospitalModal :hospitalTmp="hospitalTmp" @addHospital="addHospital" v-if="showHospitalModal"/>
    <ProjectTradesChiefModal :tradesChiefTmp="tradesChiefTmp" @addTradesChief="addTradesChief"
                             v-if="showTradesChiefModal"/>
  </div>

</template>
<script>
  import ProjectHospitalModal from '../../components/project/ProjectHospitalModal.vue'
  import ProjectTradesChiefModal from '../../components/project/ProjectTradesChiefModal.vue'
  import ProjectLists from '../../mixins/ProjectLists'
  import validation from '../../validations/project.js'
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectSafetyInformation",
    mixins: [
      validation,
      ProjectLists,
      Messages,
    ],
    props: {
      projectCreateFlag: {
        projectBasisShow: Boolean,
        projectCompanyShow: Boolean,
        projectSafetyShow: Boolean,
      },
      projectSafety: Object,
      updateOneFlag: Boolean,
      updateAllFlag: Boolean,
    },
    components: {
      ProjectTradesChiefModal,
      ProjectHospitalModal,
    },
    data: function () {
      return {
        showTradesChiefModal: false,
        showHospitalModal: false,
        tmpDelIndex: '',
        hospitalTmp: null,
        tradesChiefTmp: null,
        confirmShowFlg: false,
        rawFile: null,
        isSubmit: false,
      };
    },
    methods: {
      //案件変更の取消
      cancel: function () {
        if (!this.updateAllFlag) {
          this.$parent.$parent.$parent.showDetail();
        } else {
          this.$router.push({path: '/project/details/' + this.$route.params.id});
        }
      },
      //案件登録
      login: function () {
        this.$refs['form'].validate((valid) => {
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
          if (valid) {
            this.$parent.commit();
          } else {
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        })
      },
      //案件新規の帰る
      projectCreateBack: function () {
        this.$set(this.projectCreateFlag, 'projectBasisShow', false);
        this.$set(this.projectCreateFlag, 'projectCompanyShow', true);
        this.$set(this.projectCreateFlag, 'projectSafetyShow', false);
      },
      //工種別責任者の削除
      showTradesChief: function (index) {
        this.$confirm(this.commonMessage.confirm.delete.func('工種別責任者')).then(() => {
          this.tmpDelIndex = index;
          this.delTradesChief();
          this.$alert(this.commonMessage.success.delete, {showClose: false});
        }).catch(action => {
        });
      },
      //工種別責任者の削除
      delTradesChief: function () {
        this.projectSafety.tradesChief.splice(this.tmpDelIndex, 1);
        this.confirmShowFlg = false;
      },
      //最寄病院の削除
      showDelHospital: function (index) {
        this.$confirm(this.commonMessage.confirm.delete.func('該当最寄病院情報')).then(() => {
          this.tmpDelIndex = index;
          this.delHospital();
          this.$alert(this.commonMessage.success.delete, {showClose: false});
        }).catch(action => {
        });
      },
      //最寄病院の削除
      delHospital: function () {
        this.projectSafety.hospital.splice(this.tmpDelIndex, 1);
        this.confirmShowFlg = false;
      },
      //最寄病院のモデルを開ける
      openHospitalModal: function () {
        this.hospitalTmp = null;
        this.showHospitalModal = true;
      },
      //最寄病院を追加
      addHospital: function (data) {
        this.showHospitalModal = false;
        if (data.name !== undefined && data.name.trim() !== "") {
          this.projectSafety.hospital.push(data);
        }
      },
      //工種別責任者の開ける
      openTradesChiefModal: function () {
        this.tradesChiefTmp = null;
        this.showTradesChiefModal = true;
      },
      //工種別責任者の追加
      addTradesChief: function (data,) {
        this.showTradesChiefModal = false;
        if (data.name !== undefined && data.name.trim() !== "") {
          this.projectSafety.tradesChief.push(data);
        }
      },
      //案件の変更
      submitForm: function () {
        //メッセージを検証
        this.isSubmit = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let url;
            let data = new FormData();
            let headers = {headers: {"Content-Type": "multipart/form-data"}};
            let proSafety = true;
            let newProjectSafety = JSON.parse(JSON.stringify(this.projectSafety));
            delete newProjectSafety.tradesChief;
            delete newProjectSafety.hospital;
            data.append('projectSafety', JSON.stringify(newProjectSafety));
            data.append('tradesChief', JSON.stringify(this.projectSafety.tradesChief));
            data.append('hospital', JSON.stringify(this.projectSafety.hospital));
            data.append('proSafety', JSON.stringify(proSafety));
            if (this.rawFile) {
              data.append("file", this.rawFile);
            }
            let msg = this.commonMessage.success.insert;
            let errMsg = this.commonMessage.error.insert;
            url = '/api/editProject';
            data.append('id', this.$route.params.id);
            msg = this.commonMessage.success.update;
            errMsg = this.commonMessage.error.projectEdit;
            axios.post(url, data, headers).then(res => {
              if (res.data.result === 0) {
                this.$alert(res.data.params, {showClose: false}).then(() => {
                  this.$emit('submitFormFlag', true);
                  if (!this.updateAllFlag) {
                    this.$parent.$parent.$parent.showDetail();
                  } else {
                    this.$router.push({path: '/project/details/' + this.$route.params.id});
                  }
                  loading.close();
                });
                this.rawFile = null;
              } else if (res.data.result === 1) {
                loading.close();
                this.$alert(res.data.errors, {showClose: false});
              }
            }).catch(err => {
              loading.close();
              this.$alert(errMsg);
            });
          }else{
            this.isSubmit = false;
            loading.close();
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
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
              this.$emit('submitFormFlag', true);
              this.$msgbox.close();
              if (!this.updateAllFlag) {
                this.$parent.showDetail();
              } else {
                this.$router.push({path: '/project/details/' + this.$route.params.id});
              }
            }
          }, 1000)
        }
      }
    },
    mounted() {
      if (this.projectCreateFlag) {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      } else {
        if(this.$route.name === 'ProjectUpdate'){
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
        }else{
          this.$refs.firstInput.focus();
        }
      }
    }
  }
</script>
