<template>
  <div class="container clearfix project">
    <header>
      <div class="title-wrap">
      </div>
      <UserProfile/>
    </header>
    <section class="project-wrapper">
      <div class="report-deteil-wrap edit clearfix">
        <el-form ref="form" :model="user.enterprise" :rules="enterprise" label-width="200px">
          <section v-if="user.enterprise_admin==='1'">
            <el-row>
              <h2 class="mig">管理アカウント</h2>
            </el-row>
            <el-form-item label="管理者名" prop="named">
              <el-input v-model="user.enterprise.named" placeholder="管理者名" maxlength="30"></el-input>
            </el-form-item>
            <el-form-item label="写真" prop="">
              <el-row>
                <img v-if="imageUrl" v-bind:src="imageUrl" class="avatar">
                <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                <div class="el-form-item__error" v-if="validateMessage">
                  {{validateMessage}}
                </div>
              </el-row>
              <el-upload
                      action="http"
                      class="avatar-uploader"
                      :auto-upload="false"
                      :show-file-list="false"
                      :on-change="handleChange">
                <el-button class="is-hidden" type="primary">写真を選択
                </el-button>
              </el-upload>
            </el-form-item>
          </section>
          <section v-if="user.enterprise_admin==='0'">
            <el-row>
              <h2 class="mig">一般アカウント</h2>
            </el-row>
            <el-form-item label="アカウント名" prop="named">
              <el-input v-model="user.enterprise.named"></el-input>
            </el-form-item>
            <el-form-item label="写真" prop="">
              <el-row>
                <img v-if="imageUrl" v-bind:src="imageUrl" class="avatar">
                <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                <div class="el-form-item__error" v-if="validateMessage">
                  {{validateMessage}}
                </div>
              </el-row>
              <el-upload
                      action="http"
                      class="avatar-uploader"
                      :auto-upload="false"
                      :show-file-list="false"
                      :on-change="handleChange">
                <el-button class="is-hidden" type="primary">写真を選択
                </el-button>
              </el-upload>
            </el-form-item>
          </section>
          <section v-if="user.enterprise_admin==='1'">
            <el-row>
              <h2 class="mig">プロフィール</h2>

            </el-row>
            <el-form-item label="会社名" prop="name">
              <el-input v-model="user.enterprise.name" placeholder="会社名" maxlength="50">
              </el-input>
            </el-form-item>
            <el-form-item label="郵便番号" prop="zip">
              <el-input v-model="user.enterprise.zip" placeholder="郵便番号" maxlength="7"></el-input>
              <!--<el-input>※(半角数字)ハイン(-)なしで入力してください。</el-input>-->
            </el-form-item>
            <el-form-item label="都道府県" prop="pref">
              <el-input v-model="user.enterprise.pref" placeholder="都道府県" maxlength="20">
              </el-input>
            </el-form-item>
            <el-form-item label="市区町村" prop="town">
              <el-input v-model="user.enterprise.town" placeholder="市区町村" maxlength="30">
              </el-input>
            </el-form-item>
            <el-form-item label="番地" prop="street">
              <el-input v-model="user.enterprise.street" placeholder="番地" maxlength="20">
              </el-input>
            </el-form-item>
            <el-form-item label="建物名" prop="house">
              <el-input v-model="user.enterprise.house" placeholder="建物名" maxlength="70">
              </el-input>
            </el-form-item>
            <el-form-item label="電話番号" prop="tel">
              <el-input v-model="user.enterprise.tel" placeholder="電話番号" maxlength="15">
              </el-input>
            </el-form-item>
          </section>
        </el-form>
        <div class="clearfix">
          <div class="button-lower " style="margin:10px 30px 10px 250px;">
            <router-link to="/enterprise">戻る</router-link>
          </div>
          <div class="button-lower remark">
            <a href="javascript:void(0)" @click.prevent="edit">変更</a>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!--/container-->
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import validation from '../../validations/user'
  import reportValidation from '../../validations/report'
  import Messages from "../../mixins/Messages";

  export default {
    name: "EnterpriseDetail",
    components: {
      UserProfile,
    },
    mixins: [
      validation,
      Messages,
      reportValidation],
    data: function () {
      return {
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
        user: {
          named: '',
          enterprise: {
            name: '',
            zip: '',
            pref: '',
            town: '',
            street: '',
            tel: '',
          },
        },
      }
    },
    mounted() {
      this.isMounted = true;
    },
    methods: {
      handleChange(file, fileList) {
        this.validateMessage = this.imageValidate(file);
        if (!this.validateMessage) {
          this.rawFile = file.raw;
          this.imageUrl = URL.createObjectURL(file.raw);
        }
      },
      fetch: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getEnterprisesList').then((res) => {
          this.user = res.data.user[0];
          this.imageUrl = this.user.file;
          // this.user.named=this.user.name;
          this.$set(this.user.enterprise, 'named', this.user.name);
        })
        loading.close();
      },
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
            let errMsg = this.commonMessage.error.update;
            axios.post(url, data, headers).then(res => {
              loading.close();
              if (res.data.result === 0) {
                this.$router.push({path: '/enterprise'});
              } else {
                this.$alert(res.data.errors, {showClose: false});
              }
            }).catch(e => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });

          } else {
            loading.close();
          }
        })
        this.isSubmit = false;
      },
    },
    created() {
      this.fetch();
    },
  }
</script>
<style scoped>

  .mig {
    margin: 0 250px;
    width: 300px;
  }

  .avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }

  .avatar-uploader .el-upload:hover {
    border-color: #409EFF;
  }

  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 178px;
    height: 178px;
    line-height: 178px;
    text-align: center;
  }

  img.avatar {
    width: 178px;
    height: 178px;
    display: block;
    margin: 0;
  }
</style>

