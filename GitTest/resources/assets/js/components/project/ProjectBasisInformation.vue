<template>
  <!--container-->
  <div class="content clearfix commonAll">
    <section class="common-wrapper Nesting-page">
      <div class="common-view">
        <div class="content">
          <h3>{{projectName('baseMsg')}}</h3>
          <el-form :model="projectBasis" :rules="rules" ref="form" label-width="200px"
                   :class="[projectCreateFlag? 'formStyle' :'updateFormStyle']">
            <div class="form-group">
              <el-form-item :label="projectName('place_name')" prop="place_name">
                <el-input v-model="projectBasis.place_name" maxlength="50" ref="firstInput"></el-input>
              </el-form-item>
            </div>
            <!--#2790 remove construction_name in project-->
            <!--<div class="form-group">-->
              <!--<el-form-item :label="projectName('construction_name')" prop="construction_name">-->
                <!--<el-input v-model="projectBasis.construction_name" maxlength="50"></el-input>-->
              <!--</el-form-item>-->
            <!--</div>-->
            <div class="form-group">
              <el-form-item :label="projectName('project_no')" prop="project_no">
                <el-input v-model="projectBasis.project_no" maxlength="51"></el-input>
              </el-form-item>
            </div>
            <div id="zipLoading">
            <div class="form-group">
              <el-form-item :label="projectName('zip')" prop="zip">
                <span class="zip-sign">〒</span>
                <el-input v-model="projectBasis.zip" class='pro-basis-zip' maxlength="7"></el-input>
                <span style="font-size: 12px">※（半角数字）ハイフン（-）なしで入力してください。</span>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('pref')" prop="pref">
                <el-input v-model="projectBasis.pref" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('town')" prop="town">
                <el-input v-model="projectBasis.town" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('street')" prop="street">
                <el-input v-model="projectBasis.street" maxlength="50"></el-input>
              </el-form-item>
            </div>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('house')">
                <el-input v-model="projectBasis.house" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group offices_tel">
              <el-form-item :label="projectName('telOut')" prop="telOut" class="officestel">
                <el-input v-model="projectBasis.telOut" maxlength="15"></el-input>
              </el-form-item>
              <el-form-item :label="projectName('telIn')" prop="telIn" label-width="150px" class="officestelIn">
                <el-input v-model="projectBasis.telIn" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('fax')">
                <el-input v-model="projectBasis.fax" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('construction')">
                <el-input
                        type="textarea"
                        :rows="2"
                        v-model="projectBasis.construction">
                </el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('customer_id')">
                <div class="form-group formadd" v-if='locale' v-for="(locale,index) in projectBasis.customer"
                     :key="'projectBasis'+index">
                  <el-form-item  class="nameupdata" :label="localeCustomerName('customer_name')" v-if="locale">
                    <el-input disabled="disabled"  v-model="locale.name"></el-input>
                    <button @click.prevent="showLocaleCustomer(index)">削除</button>
                  </el-form-item>
                  <el-form-item :label="localeCustomerName('tel')" v-if="locale">
                    <el-input disabled="disabled"  v-model="locale.tel"></el-input>
                  </el-form-item>
                  <div v-if="locale.people && people" v-for="people in locale.people"
                       :key="'people-'+ people.id" >
                    <el-form-item :label="localeCustomerName('name')" v-if="people">
                      <el-input disabled="disabled"  v-model="people.name"></el-input>
                    </el-form-item>
                    <el-form-item :label="localeCustomerName('email')" v-if="people">
                      <el-input disabled="disabled"  v-model="people.email"></el-input>
                    </el-form-item>
                  </div>
                </div>
                <el-button type="primary" round @click.prevent="openDetailModal">
                  <!--施主情報登録-->
                  {{projectName('customerAdd')}}
                </el-button>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label=" projectName('localeChief')">
                <div class="form-group formadd" v-if="projectBasis.localeChief"
                     v-for="(locale,index) in projectBasis.localeChief"
                     :key="index">
                  <el-form-item :label="localeChiefName('name')" class="nameupdata"
                                :prop="'localeChief.' + index + '.name'" :rules="localeChiefRules.name">
                    <el-input v-model="locale.name" maxlength="30" :disabled=disableTrue></el-input>
                    <button @click.prevent="showLocaleChief(index)">削除</button>
                  </el-form-item>
                  <el-form-item :label="localeChiefName('mail')" :prop="'localeChief.' + index + '.mail'"
                                :rules="localeChiefRules.mail">
                    <el-input v-model="locale.mail" maxlength="191" :disabled=disableTrue></el-input>
                  </el-form-item>
                </div>
                <el-button type="primary" round @click.prevent="openLocaleChiefModal">
                  {{projectName('localeChiefAdd')}}
                </el-button>
              </el-form-item>
            </div>
            <el-form-item :label="projectName('subject_image')">

              <el-upload v-if="!projectBasis.subject_image"
                         class="avatar-uploader imgUpload"
                         action="http"
                         :show-file-list="false"
                         :auto-upload="false"
                         :on-change="handleChange">
                <el-button type="primary" round class="imgButton">
                  {{projectName('subject_imageLogin')}}
                </el-button>
              </el-upload>
              <div class="imgPosition" v-if="projectBasis.subject_image">
                <img v-if="projectBasis.subject_image.match('blob')||projectBasis.subject_image.match('images')" :src="projectBasis.subject_image" class="avatar">
                <img v-else :src="'file/projects/'+projectBasis.subject_image" class="avatar">
                <i v-else class="avatar-uploader-icon"></i>
                <div class="el-form-item__error" v-if="validateMessage">
                  {{validateMessage}}
                </div>
                <div class="upImg">
                  <li class="imgName" v-if="updateImg">{{this.rawFile.name}}</li>
                  <li class="imgName" v-else>
                    {{projectBasis.subject_image.split('/')[projectBasis.subject_image.split('/').length-1].split('?')[0]}}
                  </li>
                  <li class="upBut">
                    <el-upload
                            class="avatar-uploader"
                            action="http"
                            :show-file-list="false"
                            :auto-upload="false"
                            :on-change="handleChange">
                      <a>変更</a>
                    </el-upload>
                  </li>
                </div>
              </div>
            </el-form-item>
            <div class="form-group">
              <el-form-item :label="projectName('progress_status')"class="progressStatus">
                <el-select v-model="projectBasis.progress_status" placeholder='' icon="el-icon-caret-bottom" popper-class="select-common"
                           split-button="true" type="el-icon-caret-bottom">
                  <el-option v-for="progress in progressStatus" :key="progress.id" :label="progress.name"
                             :value="progress.id"></el-option>
                </el-select>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('progress_special_content')">
                <el-input
                        type="textarea"
                        :rows="2"
                        v-model="projectBasis.progress_special_content">
                </el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="projectName('building_type')">
                <el-input v-model="projectBasis.building_type" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group date">
              <el-form-item :label="projectName('workDate')">
                <el-form-item :label="projectName('st_date')" label-width="150px">
                  <div class="dataAndIcon">
                    <el-date-picker type="date" popper-class="projectEdit-date" v-model="projectBasis.st_date" :picker-options="startDateSelect"
                                    value-format="yyyy-MM-dd" maxlength="10"  style="z-index: 0" :clearable="clearable"></el-date-picker>
                  </div>
                </el-form-item>
              </el-form-item>
            </div>
            <div class="form-group date">
              <el-form-item>
                <el-form-item :label="projectName('ed_date')" label-width="150px">
                  <div class="dataAndIcon">
                    <el-date-picker type="date" popper-class="projectEdit-date" v-model="projectBasis.ed_date" :picker-options="endDateSelect"
                                    value-format="yyyy-MM-dd" maxlength="10"  style="z-index: 0" :clearable="clearable"></el-date-picker>
                  </div>
                </el-form-item>
              </el-form-item>
            </div>
            <div class="form-group date">
              <el-form-item>
                <el-form-item :label="projectName('open_date')" label-width="150px">
                  <div class="dataAndIcon">
                    <el-date-picker type="date" popper-class="projectEdit-date" v-model="projectBasis.open_date" :clearable="clearable"
                                    value-format="yyyy-MM-dd" maxlength="10"  style="z-index: 0"></el-date-picker>
                  </div>
                </el-form-item>
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
          <a class="modoru" @click="returnProject">
            戻る
          </a>
          <!--<a class="nextPage" @click="validation()">次へ</a>-->
          <a class="nextPage" @click="validation()">登録</a>
        </div>
      </div>
    </section>
    <ProjectLocaleChiefModal :localeChiefTmp="localeChiefTmp" :projectLocalChief="projectBasis.localeChief" @addLocaleChief="addLocaleChief"
                             v-if="showLocaleChiefModal"/>
    <ProjectCustomerSelectModal ref="pro" @closeModal="closeDetailModal" v-show="showDetailModal"/>
  </div>

</template>
<script>
  import ProjectLocaleChiefModal from '../../components/project/ProjectLocaleChiefModal.vue'
  import ProjectCustomerSelectModal from '../../components/project/ProjectCustomerSelectModal.vue'
  import UserProfile from "../../components/common/UserProfile";
  import ProjectLists from '../../mixins/ProjectLists'
  import validation from '../../validations/project.js'
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectBasisInformation",
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
      projectBasis: {},
      updateOneFlag: Boolean,
      updateAllFlag: Boolean,
    },
    components: {
      UserProfile,
      ProjectCustomerSelectModal,
      ProjectLocaleChiefModal,
    },
    data: function () {
      return {
        idArr:[],
        zipFlag:0,
        clearable: false,
        imgFlag:false,
        startDateSelect: {
          disabledDate: this.startDateDisabled,
        },
        endDateSelect: {
          disabledDate: this.endDateDisabled,
        },
        imgName: [],
        customerData: {},
        updateImg: false,
        showDetailModal: false,
        showLocaleChiefModal: false,
        tmpDelIndex: '',
        localeChiefTmp: null,
        confirmShowFlg: false,
        validateMessage: '',
        rawFile: null,
        isSubmit: false,
        disableTrue: true,
        projectLocalChief: {},
        doSubmitForm: false,
        doZipCheck: false,
      };
    },
    mounted() {
      if (this.projectCreateFlag) {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      } else {
        this.$refs.firstInput.focus();
      }
      if (this.projectBasis.subject_image) {
        this.projectBasis.subject_image  = this.projectBasis.subject_image.split('?')[0] + "?" +Date.now();
      }
      this.zipFlag = this.projectBasis.zip;
      this.zipTmp = {'address1':this.projectBasis.pref,'address2':this.projectBasis.town,'address3':this.projectBasis.street,prefcode: '1',zipcode: this.projectBasis.zip};
    },
    methods: {
      //着工日不表示のデータ
      startDateDisabled(time) {
        if (this.projectBasis.ed_date)
          return time.getTime() > new Date(this.projectBasis.ed_date).getTime();
        return false;
      },
      //竣工日不表示のデータ
      endDateDisabled(time) {
        if (this.projectBasis.st_date)
          return time.getTime() < new Date(this.projectBasis.st_date).getTime();
        return false;
      },
      //案件新規の帰る
      returnProject: function () {
        this.$router.push({path: '/project/'});
      },
      //案件変更の取消
      cancel: function () {
        if (!this.updateAllFlag) {
          this.$parent.$parent.$parent.showDetail();
        } else {
          this.$router.push({path: '/project/details/' + this.$route.params.id});
        }
      },
      //メッセージを検証
      validation: function () {
        this.$parent.doSubmitForm = true;
        if(this.$parent.doZipCheck){
          return;
        }
        this.$refs['form'].validate((valid) => {
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
          if (valid) {
            //新規登録の入力フローの変更#1724
            // this.$set(this.projectCreateFlag, 'projectBasisShow', false);
            // this.$set(this.projectCreateFlag, 'projectCompanyShow', true);
            // this.$set(this.projectCreateFlag, 'projectSafetyShow', false);
            this.$emit('saveFile', this.rawFile);
            //新規登録の入力フローの変更#1724
            this.$parent.commit();
          } else {
            this.$parent.doSubmitForm = false;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        })
      },
      //案件変更
      submitForm: function () {
        this.doSubmitForm = true;
        if(this.doZipCheck){
          return;
        }
        this.isSubmit = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let url;
            let data = new FormData();
            let headers = {headers: {"Content-Type": "multipart/form-data"}};
            this.setTel();
            let proBasis = true;
            this.projectBasis.customer.map(x => {
              x.id = x.pivot.customer_id;
              return x;
            });
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
            delete newProjectBasis.localeChief;
            data.append('projectBasis', JSON.stringify(newProjectBasis));
            data.append('localeChief', JSON.stringify(this.projectBasis.localeChief));
            data.append('proBasis', JSON.stringify(proBasis));
            if (this.rawFile) {
              data.append("file", this.rawFile);
            }
            let msg = this.commonMessage.success.insert;
            let errMsg = this.commonMessage.error.projectEdit;
            url = '/api/editProject';
            data.append('id', this.$route.params.id);
            msg = this.commonMessage.success.update;
            errMsg = this.commonMessage.error.projectEdit;
            axios.post(url, data, headers).then(res => {
              this.doSubmitForm = false;
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
              } else if (res.data.result === 1) {
                loading.close();
                this.$alert(errMsg, {showClose: false});
              }
            }).catch(error => {
              loading.close();
              this.$alert(errMsg);
            });
          } else {
            this.isSubmit = false;
            this.doSubmitForm = false;
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
      // 施主情報を削除
      showLocaleCustomer: function (index) {
        this.$confirm(this.commonMessage.confirm.delete.func('施主情報')).then(() => {
          this.delLocaleCustomer(index);
          this.$alert(this.commonMessage.success.delete, {showClose: false});
        }).catch(action => {
        });
      },
      // 施主情報を削除
      delLocaleCustomer: function (index) {
        this.projectBasis.customer.splice(index, 1);
        this.confirmShowFlg = false;
      },
      // 現場担当者を削除
      showLocaleChief: function (index) {
        this.$confirm(this.commonMessage.confirm.delete.func('担当者')).then(() => {
          this.tmpDelIndex = index;
          this.delLocaleChief();
          this.$alert(this.commonMessage.success.delete, {showClose: false});
        }).catch(action => {
        });
      },
      // 現場担当者を削除
      delLocaleChief: function () {
        this.projectBasis.localeChief.splice(this.tmpDelIndex, 1);
        this.confirmShowFlg = false;
      },
      // 現場担当者モデルを開ける
      openLocaleChiefModal: function () {
        this.localeChiefTmp = null;
        this.showLocaleChiefModal = true;
      },
      //現場担当者を追加
      addLocaleChief: function (data) {
        this.showLocaleChiefModal = false;
        if (data.name !== undefined && data.name.trim() !== "") {
          this.projectBasis.localeChief.push(data);
        }
      },
      //施主情報モデルを開ける
      openDetailModal() {
        let idArr=[];
        this.showDetailModal = true;
        for (let i=0;i<this.projectBasis.customer.length;i++){
          if(this.projectBasis.customer && this.projectBasis.customer[i]
              && this.projectBasis.customer[i].pivot && this.projectBasis.customer[i].pivot.office_id){
            idArr.push(parseInt(this.projectBasis.customer[i].pivot.office_id));
          }
        }
        this.$refs.pro.fetchOffice(idArr);
        this.$refs.pro.openCloseBtn = true;
        this.idArr = idArr;
      },
      //施主情報モデルを閉め
      closeDetailModal(data = "") {
        this.$refs.pro.selectedIndex = null;
        this.$refs.pro.officeIds = null;
        this.$refs.pro.customerOffices = null;
        this.$refs.pro.words = null;
        this.$refs.pro.chooseCustomer = {
          id: '',
          officeId: '',
          name: '',
        };
        this.showDetailModal = false;
        if (data !== "") {
          this.customerData = data;
          let customer = new Object();
          customer.name=data.name;
          customer.id=data.id;
          customer.tel=data.tel;
          customer.people=data.people;
          this.$set(customer, 'pivot', {'office_id':data.officeId,'customer_id':data.id});
          if (!this.projectBasis.customer){
            this.$set(this.projectBasis, 'customer', []);
          }
          this.projectBasis.customer.push(customer);
        }
      },
      //電話番号を処理
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
      //案件画像を変更
      handleChange(file, fileList) {
        this.validateMessage = this.imageValidate(file);
        if (!this.validateMessage) {
          this.rawFile = file.raw;
          this.$set(this.projectBasis, 'subject_image', URL.createObjectURL(file.raw));
          this.updateImg = true;
          this.$emit('updateFile', this.rawFile);
        }
      }
    }
  }
</script>
