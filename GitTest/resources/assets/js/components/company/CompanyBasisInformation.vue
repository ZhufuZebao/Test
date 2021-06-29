<template>
  <!--container-->
  <div class="content clearfix commonAll">
    <section class="common-wrapper Nesting-page">
      <div class="common-view">
        <div class="content">
          <h3>{{companyName('baseMsg')}}</h3>
          <el-form :model="companyBasis" :rules="rules" ref="form" label-width="200px"
                   :class="[projectCreateFlag? 'formStyle' :'updateFormStyle']" onkeypress="if (event.keyCode === 13) return false;">
            <div class="form-group">
              <el-form-item :label="companyName('type')" prop="type">
                <el-select class="schdeulecreat-input"  v-model="companyBasis.type" placeholder="" popper-class="select-common">
                  <el-option v-for="companyType in type" :key="companyType.id" :label="companyType.name"
                             :value="companyType.value"></el-option>
                </el-select>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('name')" prop="name">
                <el-input v-model="companyBasis.name" maxlength="51" ref="firstInput"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('phonetic')" prop="phonetic">
                <el-input v-model="companyBasis.phonetic" maxlength="51"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('zip')" prop="zip">
                <span class="zip-sign">〒</span>
                <el-input v-model="companyBasis.zip" class='pro-basis-zip' maxlength="7"></el-input>
                <span style="font-size: 12px">※（半角数字）ハイフン（-）なしで入力してください。</span>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('pref')" prop="pref">
                <el-input v-model="companyBasis.pref" maxlength="5"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('town')" prop="town">
                <el-input v-model="companyBasis.town" maxlength="51"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('street')" prop="street">
                <el-input v-model="companyBasis.street" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('house')" prop="house">
                <el-input v-model="companyBasis.house" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group offices_tel">
              <el-form-item :label="companyName('tel')" prop="tel" class="officestel">
                <el-input v-model="companyBasis.tel" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('fax')" prop="fax">
                <el-input v-model="companyBasis.fax" maxlength="15"></el-input>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label=" companyName('localeChief')">
                <div class="form-group formadd" v-if="companyBasis.localeChief"
                     v-for="(locale,index) in companyBasis.localeChief"
                     :key="index">
                  <el-form-item :label="localeChiefName('name')" class="nameupdata"
                                :prop="'localeChief.' + index + '.name'" :rules="localeChiefRules.name">
                    <el-input v-model="locale.name" maxlength="30" ></el-input>
                    <button @click.prevent="showLocaleChief(index)">削除</button>
                  </el-form-item>
                  <el-form-item :label="localeChiefName('position')" :prop="'localeChief.' + index + '.position'"
                                :rules="localeChiefRules.position">
                    <el-input v-model="locale.position" maxlength="191" ></el-input>
                  </el-form-item>
                  <el-form-item :label="localeChiefName('dept')" :prop="'localeChief.' + index + '.dept'"
                                :rules="localeChiefRules.dept">
                    <el-input v-model="locale.dept" maxlength="191" ></el-input>
                  </el-form-item>
                  <el-form-item :label="localeChiefName('tel')" :prop="'localeChief.' + index + '.tel'"
                                :rules="localeChiefRules.tel">
                    <el-input v-model="locale.tel" maxlength="15" ></el-input>
                  </el-form-item>
                  <el-form-item :label="localeChiefName('mail')" :prop="'localeChief.' + index + '.email'"
                                :rules="localeChiefRules.email">
                    <el-input v-model="locale.email" maxlength="191" ></el-input>
                  </el-form-item>
                </div>
                <el-button type="primary" round @click.prevent="openLocaleChiefModal">
                  {{companyName('localeChiefAdd')}}
                </el-button>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item :label="companyName('remarks')" prop="remarks">
                <el-input
                        type="textarea"
                        :rows="2"
                        v-model="companyBasis.remarks">
                </el-input>
              </el-form-item>
            </div>


            <div :class="[projectCreateFlag? 'pro-button' :'updateButton']" v-if="updateAllFlag||updateOneFlag">
              <a :class="[projectCreateFlag? 'back' :'updateBack']" @click="cancel()">
                戻る
              </a><a :class="[projectCreateFlag? 'save' :'updateSave']" @click="submitForm">登録</a>
            </div>
          </el-form>
        </div>
        <div class="pro-button" v-if="projectCreateFlag">
          <a class="modoru" @click="returnCompany">
            戻る
          </a>
          <a class="nextPage" @click="validation()">登録</a>
        </div>
      </div>
    </section>
    <CompanyLocalModel :localeChiefTmp="localeChiefTmp" :projectLocalChief="companyBasis.localeChief" @addLocaleChief="addLocaleChief"
                       v-if="showLocaleChiefModal"  />
  </div>

</template>
<script>
    import CompanyLocalModel from '../../components/company/CompanyLocalModel.vue'
    import UserProfile from "../../components/common/UserProfile";
    import CompanyLists from '../../mixins/CompanyLists'
    import validation from '../../validations/company.js'
    import Messages from "../../mixins/Messages";

    export default {
        name: "CompanyBasisInformation",
        mixins: [
            validation,
            CompanyLists,
            Messages,
        ],
        props: {
            projectCreateFlag: {
                companyBasisShow: Boolean,
            },
            companyBasis: {},
            updateOneFlag: Boolean,
            updateAllFlag: Boolean,
        },
        components: {
            UserProfile,
            // ProjectCustomerSelectModal,
            CompanyLocalModel,
        },
        data: function () {
            return {
                idArr:[],
                zipFlag:0,
                clearable: false,
                imgFlag:false,
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
                type: [
                    {"name": '管理会社', "id": '0', "value": "1"},
                    {"name": '不動産会社', "id": '1', "value": "2"},
                    {"name": '消防署', "id": '2', "value": "3"},
                    {"name": '警察署', "id": '3', "value": "4"},
                    {"name": '病院', "id": '4', "value": "5"},

                ],
            };
        },
        mounted() {
            if (this.projectCreateFlag) {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
            } else {
                this.$refs.firstInput.focus();
            }
            this.zipFlag = this.companyBasis.zip;
        },
        methods: {

            //会社情報新規の帰る
            returnCompany: function () {
                this.$router.push({path: '/company/'});
            },
            //会社情報変更の取消
            cancel: function () {
                if (!this.updateAllFlag) {
                    this.$parent.showDetail();
                } else {
                    this.$router.push({path: '/company/detail/' + this.$route.params.id});
                }
            },
            //メッセージを検証
            validation: function () {
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
            //変更
            submitForm: function () {
                this.isSubmit = true;
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                this.$refs['form'].validate((valid) => {
                    if (valid) {
                        let url;
                        let data = new FormData();
                        let headers = {headers: {"Content-Type": "multipart/form-data"}};
                        let proBasis = true;
                        let typeArr=['管理会社','不動産会社', '消防署', '警察署', '病院'];
                        for(let i=0;i<typeArr.length;i++){
                            if(typeArr[i].indexOf(this.companyBasis.type)>=0){
                                this.companyBasis.type=i+1;
                            }
                        }
                        let newcompanyBasis = JSON.parse(JSON.stringify(this.companyBasis));
                        delete newcompanyBasis.localeChief;
                        data.append('companyBasis', JSON.stringify(newcompanyBasis));
                        data.append('localeChief', JSON.stringify(this.companyBasis.localeChief));
                        data.append('proBasis', JSON.stringify(proBasis));
                        let msg = this.commonMessage.success.insert;
                        let errMsg = this.commonMessage.error.companyEdit;
                        url = '/api/editCompany';
                        data.append('id', this.$route.params.id);
                        axios.post(url, data, headers).then(res => {
                            if (res.data.result === 0) {
                                this.$alert(res.data.params, {showClose: false}).then(() => {
                                    this.$emit('submitFormFlag', true);
                                    if (!this.updateAllFlag) {
                                        this.$parent.showDetail();
                                    } else {
                                        this.$router.push({path: '/company/detail/' + this.$route.params.id});
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
                this.companyBasis.localeChief.splice(this.tmpDelIndex, 1);
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
                if (data.name&&data.name !== undefined && data.name.trim() !== "") {
                    this.companyBasis.localeChief.push(data);
                }
            },


        }
    }
</script>
