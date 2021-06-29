<template>
  <!--container-->
  <div class="content clearfix commonAll">
    <section class="common-wrapper Nesting-page">
      <div class="common-view">
        <div class="content">
          <h3>管理会社・不動産屋 情報</h3>
          <el-form :model="projectCompany" :rules="rules" ref="form" label-width="200px"
                   :class="[projectCreateFlag? 'formStyle' :'updateFormStyle']">
            <table class="companyTable">
              <tr>
                <td class="leftTd">
                  <el-form-item :label="projectName('mng_company')">
                  </el-form-item>
                </td>
                <td class="rightTd">
                  <el-form-item :label="projectName('mng_company_name')">
                    <el-input v-model="projectCompany.mng_company_name" maxlength="50" ref="firstInput"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('mng_company_address')">
                    <el-input v-model="projectCompany.mng_company_address" maxlength="70"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('mng_company_tel')" prop="mng_company_tel">
                    <el-input v-model="projectCompany.mng_company_tel" maxlength="15"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('mng_company_chief')">
                    <el-input v-model="projectCompany.mng_company_chief" maxlength="20"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('mng_company_chief_position')">
                    <el-input v-model="projectCompany.mng_company_chief_position" maxlength="20"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td>
                  <el-form-item :label="projectName('realtor')">
                  </el-form-item>
                </td>
                <td class="rightTd">
                  <el-form-item :label="projectName('realtor_name')">
                    <el-input v-model="projectCompany.realtor_name" maxlength="50"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('realtor_address')">
                    <el-input v-model="projectCompany.realtor_address" maxlength="70"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('realtor_tel')" prop="realtor_tel">
                    <el-input v-model="projectCompany.realtor_tel" maxlength="15"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('realtor_chief')">
                    <el-input v-model="projectCompany.realtor_chief" maxlength="20"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('realtor_chief_position')">
                    <el-input v-model="projectCompany.realtor_chief_position" maxlength="20"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td>
                  <el-form-item :label="projectName('areaMsg')">
                  </el-form-item>
                </td>
                <td class="rightTd">
                  <el-form-item :label="projectName('site_area')" prop="site_area">
                    <el-input v-model="projectCompany.site_area" class="siteArea" maxlength="20"></el-input>
                    <span>㎡</span>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('floor_area')" prop="floor_area">
                    <el-input v-model="projectCompany.floor_area" class="siteArea" maxlength="20"></el-input>
                    <span>㎡</span>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="rightTd">
                  <el-form-item :label="projectName('floor_numbers')" prop="floor_numbers">
                    <el-input v-model="projectCompany.floor_numbers" maxlength="3"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <td>
                  <el-form-item :label="projectName('construction_company')">
                  </el-form-item>
                </td>
                <td class="rightTd">
                  <el-input v-model="projectCompany.construction_company"
                            class="construction_company" maxlength="30"></el-input>
                </td>
              </tr>
              <tr>
                <td>
                  <el-form-item :label="projectName('construction_special_content')">
                  </el-form-item>
                </td>
                <td class="rightTd">
                  <el-input
                          type="textarea"
                          :rows="2"
                          v-model="projectCompany.construction_special_content"
                          class="textareaContent">
                  </el-input>
                </td>
              </tr>
            </table>
            <div :class="[projectCreateFlag? 'pro-button' :'updateButton']" v-if="updateOneFlag">
              <a :class="[projectCreateFlag? 'back' :'updateBack']" @click="cancel()">
                キャンセル
              </a><a :class="[projectCreateFlag? 'save' :'updateSave']" @click="submitForm">保存</a>
            </div>
          </el-form>
        </div>
        <div class="pro-button" v-if="projectCreateFlag">
          <a class="modoru" @click="projectCreateBack">戻る</a>
          <a class="nextPage" @click="validation()">次へ</a>
        </div>
      </div>
    </section>
  </div>
</template>
<script>
  import ProjectLists from '../../mixins/ProjectLists'
  import validation from '../../validations/project.js'
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectCompanyInformation",
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
      projectCompany: Object,
      updateOneFlag: Boolean,
      updateAllFlag: Boolean,
    },
    data: function () {
      return {};
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
      //メッセージを検証
      validation: function () {
        this.$refs['form'].validate((valid) => {
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
          if (valid) {
            this.$set(this.projectCreateFlag, 'projectBasisShow', false);
            this.$set(this.projectCreateFlag, 'projectCompanyShow', false);
            this.$set(this.projectCreateFlag, 'projectSafetyShow', true);
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
        this.$set(this.projectCreateFlag, 'projectBasisShow', true);
        this.$set(this.projectCreateFlag, 'projectCompanyShow', false);
        this.$set(this.projectCreateFlag, 'projectSafetyShow', false);
      },
      //案件変更
      submitForm: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
          if (valid) {
            let url;
            let data = new FormData();
            let headers = {headers: {"Content-Type": "multipart/form-data"}};
            data.append('projectCompany', JSON.stringify(this.projectCompany));
            let errMsg = this.commonMessage.error.insert;
            url = '/api/editProject';
            data.append('id', this.$route.params.id);
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
                });
              } else if (res.data.result === 1) {
                loading.close();
                this.$alert(res.data.errors, {showClose: false});
              }
            }).catch(error => {
              loading.close();
              this.$alert(errMsg);
            });
          }else{
            loading.close();
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        });
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
