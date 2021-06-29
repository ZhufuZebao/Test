<template>
  <!--container-->
  <div class="container clearfix customer customerlist commonAll">
    <header>
      <h1>
        <router-link :to="{ path: '/customer', query: {flag:'success'}}">
          <div class="commonLogo">
            <ul>
              <li class="bold">CUSTOMER</li>
              <li>施主</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/customer', query: {flag:'success'}}"><span>施主一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item>施主情報</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <div class="container clearfix commonAll">
      <section class="common-wrapper">
        <div class="common-view">

          <!-- 施主削除のモーダル -->
          <CommonModal @close="closeDelModal" v-if="delCheck">
            <template>
              <p>
                この施主を削除しますか？
              </p>
              <button @click="delYes">はい</button>
              <button @click="delNo">いいえ</button>
            </template>
          </CommonModal>
          <div class="content customerdetail-nametop" v-if="showForm">
            <h3><div>{{ customer.name }}</div><div>({{ customer.phonetic }} </div><div>)施主情報</div> </h3>
            <el-form ref="form" :rules="customerRules" :model="customer" label-width="200px" @submit.native.prevent
                     class="formStyle updateFormStyle" onkeypress="if (event.keyCode === 13) return false;">
              <div class="form-group">
                <el-form-item :label="customersName('name')" prop="name" class="nameupdata fristinput">
                  <el-input v-model="customerName" maxlength="50"></el-input>
                  <button class="del_btn" @click.prevent="deleteCustomer">削除</button>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="customersName('phonetic')" prop="phonetic">
                  <el-input v-model="customerPhonetic" maxlength="50"></el-input>
                </el-form-item>
              </div>
              <div class="updateButton">
                <a class="updateBack" @click="closeUpdateModal">キャンセル</a>
                <a class="updateSave" @click="save">保存</a>
              </div>
            </el-form>

          </div>
          <div class="modal-container ">
            <div class="customerdetail clearfix">
              <div class="content" v-if="showInfo">

                <h3><div>{{ customer.name }}</div><div>({{ customer.phonetic }} </div><div>)施主情報</div> </h3>

                <table class="customer-collapse">
                  <a @click.prevent="openUpdateModal" class="edit"><img src="images/edit@2x.png"/></a>
                  <tr>
                    <td class="tdLeft">{{ customersName('name') }}</td>
                    <td class="tdRight">{{ customer.name }}</td>
                  </tr>
                  <tr>
                    <td class="tdLeft">{{ customersName('phonetic') }}</td>
                    <td class="tdRight">{{ customer.phonetic }}</td>
                  </tr>
                </table>
              </div>

              <div class="content">
                <h3>事業所情報{{customer.name}}</h3>
                <div class="customerdetailoffice">

                  <CustomerDetailOffice v-for="office in customer.offices" v-bind:office="office" ref="detailOffice"
                                        v-bind:key="office.id" :id="customer.id" :named="customer.name"/>

                </div>
              </div>
            </div>
            <div class="pro-button">
              <a class="modoru" @click="backToPrev">
                戻る
              </a>
              <a class="nextPage" @click.prevent="officeAdd">事業所追加 ＋</a>
            </div>
          </div>

        </div>
      </section>

    </div>
  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import CustomerCols from '../../mixins/CustomerColsNew';
  import CustomerDetailOffice from '../../components/customer/CustomerDetailOffice';
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/customer';

  export default {
    name: "CustomerDetail",
    mixins: [CustomerCols, validation, Messages], //定数
    components: {
      UserProfile,
      CustomerDetailOffice
    },
    data: function () {
      return {
        customerName: '',
        customerPhonetic: '',
        customer: {},
        office: {},
        delCheck: false,   //削除確認フラグ
        showInfo: true,
        showForm: false,
        name: "",
        arr: [],
        showMsg: '',
        msgShowFlg: false,
        confirmObj: {},
        confirmShowFlg: false,
      };
    },
    methods: {
      //ドナー情報を取得する
      fetchCustomerDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getCustomerDetail", {
          params: {
            id: this.$route.params.id
          }
        }).then((res) => {
          loading.close();
          this.customer = res.data;
          this.office = res.data.offices;
          let num = this.office.length;
          for (let i = 0; i < num; i++) {
            this.office[i]['name_pre'] = this.office[i]['name'];
            this.office[i]['tel_s'] = this.change_tel(this.office[i]['tel'],this.office[i]['tel_in']);
          }
          this.customerName = this.customer.name;
          this.customerPhonetic = this.customer.phonetic;
        }).catch(err => {
          let errMsg = this.commonMessage.error.customerDetail;
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      //情報を提示する
      delYes() {
        this.delCheck = false;
        this.deleteCustomer();
      },
      //情報を提示する
      delNo() {
        this.delCheck = false;
      },
      //情報を提示する
      openDelModal() {
        this.delCheck = true;
      },
      //情報を提示する
      closeDelModal() {
        this.delCheck = false;
      },
      //インタフェースアップ
      openUpdateModal() {
        this.showForm = true;
        this.showInfo = false;
      },
      //インタフェースのシャットダウン
      closeUpdateModal() {
        this.showForm = false;
        this.showInfo = true;
      },
      //データの更新
      save: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.customerEdit;
        let customer_name_tmp = this.customer.name;
        let customer_phonetic_tmp = this.customer.phonetic;
        this.customer.name = this.customerName;
        this.customer.phonetic = this.customerPhonetic;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            axios.post("/api/updateCustomerName", {
              customer: this.customer
            }).then(res => {
              loading.close();
              this.closeUpdateModal();
            }).catch(err => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            this.customer.name = customer_name_tmp;
            this.customer.phonetic = customer_phonetic_tmp;
            this.customerName = this.customer.name;
            this.customerPhonetic = this.customer.phonetic;
            loading.close();
            this.$alert(errMsg, {showClose: false});
          }
        })
      },
      //ルーティング・ダイビング
      back() {
        this.$router.go(-1);
      },
      //リストにジャンプして戻る
      backToPrev() {
        this.$router.push({path: '/customer', query: {flag:'success'}});
      },
      //事業所を追加する
      officeAdd() {
        this.$router.push({path: '/customer/create', query: {id: this.customer.id}});
      },
      //ドナーを削除する
      deleteCustomer: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.delete;
        let errMsgBind = this.commonMessage.error.officeBind;
        this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
          axios.post("/api/deleteCustomer", {
            id: this.customer.id
          }).then(res => {
            if (res.data.result > 0) {
              if (res.data.errors[0] === 'messages.error.customerBind') {
                loading.close();
                this.$alert(errMsgBind, {showClose: false});
              } else {
                loading.close();
                this.$alert(errMsg, {showClose: false});
              }
            } else {
              this.$alert(this.commonMessage.success.delete, {showClose: false}).then(() => {
                this.$router.push({path: "/customer", query: {flag:'success'}});
                loading.close();
              });
            }
          }).catch(err => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          });
        }).catch(action => {
          loading.close();
        });
      },
      change_tel(tel,tel_in) {
        if (tel_in) {
          return tel + " 内線" +tel_in;
        } else {
          return tel;
        }
      }
    },
    created() {
      this.fetchCustomerDetail();
    },
    beforeRouteLeave (to, from, next) {
      let detailOfficeFlag = false;
      if(this.$refs['detailOffice']){
          for (let i=0;i<this.$refs['detailOffice'].length;i++) {
              detailOfficeFlag = detailOfficeFlag || this.$refs['detailOffice'][i].mod;
          }
      }
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
          (this.showForm
              || detailOfficeFlag)){
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
<style scoped>
  .customer-collapse {
    border-collapse: collapse;
  }
</style>
