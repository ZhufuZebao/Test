<template>
  <!--container-->
  <div class="container clearfix customer customercreate commonAll">
    <header>
      <h1>
        <router-link :to="{ path: '/customer',query: {flag:'success'}}">
          <div class="commonLogo">
            <ul>
              <li class="bold">CUSTOMER</li>
              <li>施主22222222n</li>
            </ul>
          </div>
        </router-link>
      </h1>

      <div class="title-wrap" v-if="$route.name==='edit' || $route.query.id > 0">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/customer',query: {flag:'success'}}"><span>施主一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/customer/office/' + customer.office.id}" v-if="$route.query.officeFlag">事業所情報</el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/customer/detail/' + customer.id}" v-else>施主情報</el-breadcrumb-item>
          <el-breadcrumb-item v-if="$route.query.officeFlag">事業所情報編集</el-breadcrumb-item>
          <el-breadcrumb-item v-else>施主情報編集</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <div class="title-wrap" v-else>
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/customer',query: {flag:'success'} }"><span>施主一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item>施主新規登録</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>

    <!--customer-wrapper-->
    <section class="common-wrapper">
      <!--<form>-->
      <div class="common-view common-wrapper-container">
        <div class="content">
          <h3>施主情報</h3>
          <el-form :model="customer" :rules="customerRules" ref="form" label-width="200px"
                   class="formStyle customercreateform" @submit.native.prevent
                   onkeypress="if (event.keyCode === 13) return false;">
            <div class="form-group">
              <el-form-item :label="customersName('name')" prop="name" class="fristinput">
                <el-input v-if="$route.name==='edit' || $route.query.id > 0" :disabled="true" v-model="customer.name"
                          maxlength="50"></el-input>
                <el-input v-else v-model="customer.name" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-if="$route.name==='edit' || $route.query.id > 0">
              <el-form-item :label="customersName('phonetic')">
                <el-input :disabled="true" v-model="customer.phonetic" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <div class="form-group" v-else>
              <el-form-item :label="customersName('phonetic')" prop="phonetic">
                <el-input v-model="customer.phonetic" maxlength="50"></el-input>
              </el-form-item>
            </div>
            <ul>
              <FormOffice ref="child" v-model="customer.office" :office="customer.office" :createFlg="createFlg"
                          @createBack="createBack" @checkBack="checkBack" :named="customer.name" :zipFlag="zipFlag" :zipTmp="zipTmp"/>
            </ul>
          </el-form>
        </div>
        <div class="pro-button">
          <a class="modoru" @click="backToCustomer">
            キャンセル
          </a>
          <a class="nextPage" v-if="$route.name==='edit'" @click.prevent="submitForm">変更</a>
          <a class="nextPage" v-else @click.prevent="submitForm">登録</a>
        </div>
      </div>
      <!--</form>-->
    </section>
    <!--/customer-wrapper-->
    <MsgDialog :showMsg="showMsg" @msgShowOver="msgShowOver" v-if="msgShowFlg"/>
    <Confirm :msgObject="confirmObj" v-if="confirmShowFlg"/>
  </div>
  <!--/container-->

</template>
<script>
  import CustomerCols from '../../mixins/CustomerColsNew';
  import MsgDialog from '../../components/common/MsgDialog.vue';
  import Confirm from '../../components/common/Confirm.vue';
  import validation from '../../validations/customer';
  import Messages from "../../mixins/Messages";
  import UserProfile from "../../components/common/UserProfile";
  import FormOffice from '../../components/customer/CustomerCreateFormOffice.vue'

  export default {
    name: 'CustomerCreate',
    mixins: [CustomerCols, validation, Messages], //項目名定数
    components: {
      UserProfile,
      MsgDialog,
      Confirm,
      FormOffice
    },
    data: function () {
      return {
        customer: {
          id: '',
          name: "",
          phonetic: "",
          office: {
            name: "",
            zip: "",
            pref: "",
            town: "",
            street: "",
            house: "",
            tel: "",
            fax: "",
            telIn: '',
            telOut: '',
            people: [],
            billings: [],
            isActive:false,
          },
        },
        createFlg: true,
        name: "",
        arr: [],
        showMsg: '',
        msgShowFlg: false,
        confirmObj: {},
        confirmShowFlg: false,
        isSubmit: false,
        ranStr: ""
      };
    },
    methods: {
      //提示メッセージ表示をキャンセルする
      msgShowOver: function () {
        this.msgShowFlg = false;
      },
      //リストにジャンプして戻る
      backToCustomer: function () {
        this.$router.push({
          path: '/customer',query: {flag:'success'}
        });
      },
      //帳票の提出
      submitForm: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          let officeValid = true;
          if(!this.checkOffice()){
            officeValid = this.checkOffice();
          }
          if (valid && officeValid) {
            let url;
            let msg = this.commonMessage.success.insert;
            let errMsg = this.commonMessage.error.customerCreate;
            this.setTel();
            if (this.$route.name !== 'edit') {
              url = '/api/createCustomer';
            } else {
              url = '/api/editCustomerOffice';
              msg = this.commonMessage.success.update;
              errMsg = this.commonMessage.error.customerEdit;
            }
            axios.post(url, {
              customer: this.customer,
            }).then(res => {
              if (res.data.result === 0) {
                if (this.$route.name !== 'edit') {
                  this.customer.id = res.data.params.customer.id;
                  this.showConfirm();
                  loading.close();
                } else {
                  this.$alert(msg, {showClose: false}).then(() => {
                    loading.close();
                    this.backToCustomer();
                  });
                }
              } else if (res.data.result === 1) {
                this.$alert(res.data.errors, {showClose: false});
                loading.close();
              }
            }).catch(err => {
              loading.close();
              this.$alert(errMsg);
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
      //回調
      createBack: function (data) {
        this.customer.office = data;
      },
      //子票検証
      checkOffice() {
        this.isSubmit = true;
        return this.$refs['child'].checkOffice();
      },
      //サブフォームを振り替えます
      checkBack(data) {
        if (!data){
          this.isSubmit = false;
          setTimeout(() => {
            let isError = document.getElementsByClassName("is-error");
            isError[0].querySelector('input').focus();
          }, 1);
          return false;
        }
      },
      //ドナー情報取得
      fetchCustomerDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getEditCustomer", {
          params: {
            id: this.$route.query.id,
            officeId: this.$route.query.officeId,
          }
        }).then(res => {
          this.customer.id = res.data.customer.id;
          this.customer.name = res.data.customer.name;
          this.customer.phonetic = res.data.customer.phonetic;
          this.customer.office = res.data.office[0];
          this.customer.office.people = res.data.office[0].people;
          this.customer.office.billings = res.data.office[0].billings;
          this.$set(this.customer.office, 'telOut', this.customer.office.tel);
          this.$set(this.customer.office, 'telIn',this.customer.office.tel_in);
          this.zipFlag = this.customer.office.zip;
          this.zipTmp = {'address1':this.customer.office.pref,'address2':this.customer.office.town,'address3':this.customer.office.street,prefcode: '1',zipcode: this.customer.office.zip};
          loading.close();
        }).catch(err => {
          loading.close();
        });
      },
      //ドナーベース情報を取得する
      fetchCustomerName: function () {
        this.customer.id = this.$route.query.id;
        axios.get("/api/getCustomerName", {
          params: {
            id: this.$route.query.id,
          }
        }).then(res => {
          this.customer.name = res.data.name;
          this.customer.phonetic = res.data.phonetic;
        });
      },
      //情報を提示する
      doYes: function () {
        this.confirmShowFlg = false;
        this.isSubmit = false;
        this.$refs['child'].$refs['form'].resetFields();
        this.customer.office.fax = '';
        this.customer.office.zip = '';
        this.customer.office.pref = '';
        this.customer.office.town = '';
        this.customer.office.street = '';
        this.customer.office.house = '';
        delete this.customer.office.people;
        this.customer.office.people = [];
        delete this.customer.office.billings;
        this.customer.office.billings = [];
        let newDay = new Date();
        this.ranStr = newDay.getTime();
        this.customer.office.isActive=true;
        this.$router.push({path: '/customer/create', query: {id: this.customer.id,flag:'success'}});
      },
      //情報を提示する
      doNo() {
        this.confirmShowFlg = false;
        this.$router.push({path: "/customer",query: {flag:'success'}});
      },
      //電話番号のフォーマット
      setTel: function () {
        if (this.customer.office.telIn !== "" && this.customer.office.telOut !== "") {
          this.customer.office.tel = this.customer.office.telOut + '-' + this.customer.office.telIn;
        } else if (this.customer.office.telIn === "" && this.customer.office.telOut !== "") {
          this.customer.office.tel = this.customer.office.telOut;
        } else if (this.customer.office.telOut === "" && this.customer.office.telIn !== "") {
          this.customer.office.tel = this.customer.office.telIn;
        } else {
          this.customer.office.tel = "";
        }
      },
      //情報を提示する
      showConfirm() {
        let msgB = "";
        if (this.customer.office.name) {
          msgB = "(" + this.customer.office.name + ")";
        }
        // let msgA = "「" + "施主：" + this.customer.name + msgB +"」を登録しました。";
         let msgA =  this.customer.name + msgB ;
        this.confirmObj = {
          message:msgA,
          // btnCancelText: this.customer.phonetic+'を連続追加＋',
          btnCancelText: '他の事業所',
          btnSubmitText: '施主一覧画面へ',
          btnCancelFunction: this.doYes,
          btnSubmitFunction: this.doNo,
        };
        this.confirmShowFlg = true;
      }
    },
    mounted() {
      if (this.$route.name === 'edit') {
        this.fetchCustomerDetail();
        this.createFlg = false;
      } else {
        this.createFlg = true;
        if (this.$route.query.id !== undefined) {
          this.fetchCustomerName();
        }
      }
    },
    watch: {
      'ranStr': function (to, from) {
        document.body.scrollTop = 0
        document.documentElement.scrollTop = 0
      }
    }
  }
</script>
<style>

  .customercreateform .report-deteil-wrap .el-input__inner {
    padding: 0 15px !important;
  }
</style>
