<template>
  <div class="content clearfix commonAll no-scroll enterprise-detail">
    <section class="common-wrapper Nesting-page">
      <div class="common-view modal-view">
        <div class="content">
          <h3>{{ userName }}</h3>
          <el-form :model="user.enterprise" :rules="enterprise" ref="form" label-width="200px"
                   class="updateFormStyle update-form-shadow">
            <el-form-item class="form-item-img" label="プロフィール画像">
              <el-upload v-if="!user.file"
                         class="avatar-uploader imgUpload"
                         action="http"
                         :show-file-list="false"
                         :auto-upload="false"
                         :on-change="handleChange">
                <img class="enterprise-img" v-if="user.file" :src="user.file"/>
                <img class="enterprise-img" v-else src="images/icon-chatperson.png"/>
                <el-button type="text" class="img-button">
                  写真を変更する
                </el-button>
              </el-upload>
              <div class="imgPosition upload-avatar" v-if="user.file">
                <img class="enterprise-img" v-if="user.file" :src="user.file">
                <i v-else class="avatar-uploader-icon"></i>
                <div class="el-form-item__error" v-if="validateMessage">
                  {{validateMessage}}
                </div>
                <div class="upImg">
                  <li class="upBut">
                    <el-upload
                            class="avatar-uploader"
                            action="http"
                            :show-file-list="false"
                            :auto-upload="false"
                            :on-change="handleChange">
                      <el-button type="text" class="img-button">
                        写真を変更する
                      </el-button>
                    </el-upload>
                  </li>
                </div>
              </div>
            </el-form-item>
            <div class="form-group form-name">
              <el-form-item label="アカウント" prop="last_name"  class="form-last-name">
                <el-input v-model="user.enterprise.last_name" placeholder="姓" maxlength="59"></el-input>
              </el-form-item>
              <el-form-item label="" prop="first_name"  class="form-first-name">
                <el-input v-model="user.enterprise.first_name" placeholder="名" maxlength="59"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="メールアドレス" prop="mail">
                <el-input v-model="user.enterprise.mail"></el-input>
                <p class="p-tip">メールアドレスを変更する場合は、変更後に認証を完了する必要があります。 認証完了までは旧メールアドレスでのご利用となります。</p>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="会社名" prop="name">
                <el-input v-model="user.enterprise.name" placeholder="会社名" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="郵便番号" prop="zip">
                <span class="zip-sign">〒</span>
                <el-input v-model="user.enterprise.zip" placeholder="郵便番号" maxlength="7"
                          class='enterprise-zip'></el-input>
                <span class="input-tip">（半角数字）ハイフン（-）なしで入力してください。</span>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="都道府県" prop="pref">
                <el-input v-model="user.enterprise.pref" placeholder="都道府県" maxlength="4"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="区市町村" prop="town">
                <el-input v-model="user.enterprise.town" placeholder="市区町村" maxlength="30"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="番地" prop="street">
                <el-input v-model="user.enterprise.street" placeholder="番地" maxlength="20"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="建物名">
                <el-input v-model="user.enterprise.house" placeholder="建物名" maxlength="70"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="user.enterprise_admin === '1'">
              <el-form-item label="電話番号" prop="tel">
                <el-input v-model="user.enterprise.tel" placeholder="電話番号" maxlength="15"></el-input>
              </el-form-item>
            </div>
          </el-form>
        </div>
        <div class="pro-button">
          <a class="modoru" @click="$emit('closeEdit',0)">キャンセル</a>
          <a class="nextPage" @click="edit">変更する</a>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
  import validation from '../../../validations/user';
  import Messages from "../../mixins/Messages";

  export default {
    name: "EnterpriseDetail",
    props: {
      user: Object
    },
    mixins: [validation, Messages],
    data: function () {
      return {
        zipFlag:0,
        imageUrl: '',
        validateMessage: '',
        rawFile: null,
        userName: '',
        image_path: '',
        isMounted: false,
        isSubmit: false,
        name: '',
        zip: '',
        pref: '',
        town: '',
        street: '',
        tel: '',
        updateImg: false,
        imgName: [],
      }
    },
    methods: {
      //写真用添付書提出
      handleChange(file, fileList) {
        this.validateMessage = this.imageValidate(file);
        if (!this.validateMessage) {
          this.rawFile = file.raw;
          this.imageUrl = URL.createObjectURL(file.raw);
          this.updateImg = true;
          this.image_path = this.imageUrl;
          this.user.file = this.imageUrl;
        }
      },
      //付加データの初期化
      fetch: function () {
        this.zipFlag=this.user.enterprise.zip;
        this.$set(this.user.enterprise, 'first_name', this.user.first_name);
        this.$set(this.user.enterprise, 'last_name', this.user.last_name);
        this.$set(this.user.enterprise, 'mail', this.user.email);
      },
      //帳票を直して提出する
      edit: function () {
        this.isSubmit = true;
        this.$refs['form'].validate((valid) => {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          if (valid) {
            this.isSubmit = false;
            let data = new FormData();
            let headers = {headers: {"Content-Type": "multipart/form-data"}};
            data.append('user', JSON.stringify(this.user));
            if (this.rawFile) {
              data.append("file", this.rawFile);
            }
            let url = '/api/setEnterprisesList/detail';
            let errMsg = this.commonMessage.error.profileEdit;
            //フォームデータの提出
            axios.post(url, data, headers).then(res => {
              //電子メールの不変は、検証をトリガしない
              if (this.user.enterprise && this.user.enterprise.mail !== this.user.email) {
                this.verification(loading);
              }
              if (res.data.result === 0) {
                this.$emit('closeEdit', 1);
                loading.close();
              } else {
                this.$alert(res.data.errors, {showClose: false});
                loading.close();
              }
            }).catch(e => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            loading.close();
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        });
        this.isSubmit = false;
      },
      //電子メール検証
      verification: function (loading) {
        let errMsg = this.commonMessage.error.update;
        this.$set(this.user, 'email', this.user.enterprise.mail);
        axios.post('/api/setEnterprisesList/mail', {
          user: this.user
        }).then(res => {
          if (res.data.result !== 0) {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          }
        }).catch(e => {
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
    },
    mounted() {
      this.isMounted = true;
    },
    created() {
      this.fetch();
    },
  }
</script>
