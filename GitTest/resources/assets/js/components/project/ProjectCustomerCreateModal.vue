<template>
  <transition name="fade">
    <section>
      <div class="modalBodycontent commonMol customer-add">
      <h3 class="modal-content-head">施主新規登録</h3>
      <div class="clearfix  project-customer-create">
        <el-form :model="customer" :rules="customerRules" ref="form" label-width="200px"
                 class="customercreateform">
          <div class="form-group">
            <el-form-item :label="customersName('name')" prop="name" class="fristinput">
              <el-input v-if="$route.name==='edit' || $route.query.id > 0" :disabled="true" v-model="customer.name"
                        maxlength="50"></el-input>
              <el-input v-else v-model="customer.name" maxlength="50"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item :label="customersName('phonetic')" prop="phonetic">
              <el-input v-if="$route.name==='edit' || $route.query.id > 0" :disabled="true"
                        v-model="customer.phonetic" maxlength="50"></el-input>
              <el-input v-else v-model="customer.phonetic" maxlength="50"></el-input>
            </el-form-item>
          </div>
          <ul>
            <FormOffice ref="child" v-model="customer.office" :office="customer.office" :createFlg="createFlg" :isProject="isProject"
                        @createBack="createBack" @checkBack="checkBack" @hiddenSelectBtn="hiddenSelectBtn" :named="customer.name"
                        @showSelectBtn="showSelectBtn"/>
          </ul>
        </el-form>
        <div class="pro-button customer_list-button">
          <a class="modoru" @click.prevent="openSelect">戻る</a>
          <a class="nextPage" @click.prevent="submitForm">登録する</a>
        </div>
      </div>
      </div>
    </section>
  </transition>
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
    name: "ProjectCustomerCreateModal",
    mixins: [CustomerCols, validation, Messages], //項目名定数
    components: {
      UserProfile,
      MsgDialog,
      Confirm,
      FormOffice
    },
    props:{
      isProject:Boolean,
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
          },
        },
        createFlg: true,
        isMounted: false,
        isSubmit: false,
      }
    },
    methods: {
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
        if (!data) {
          this.isSubmit = false;
          setTimeout(() => {
            let isError = document.getElementsByClassName("is-error");
            isError[0].querySelector('input').focus();
          }, 1);
          return false;
        }
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
      //帳票の提出
      submitForm: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          let officeValid = true;
          if(!this.checkOffice()){
            officeValid = this.checkOffice();
          }
          if (valid && officeValid) {
            let msg = this.commonMessage.success.insert;
            let errMsg = this.commonMessage.error.insert;
            this.setTel();
            axios.post('/api/createCustomer', {
              customer: this.customer,
            }).then(res => {
              if (res.data.result === 0) {
                this.customer.id = res.data.params.id;
                this.$emit("closeCreate", res.data.params);
                loading.close();
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
              document.body.scrollTop = 0;
              document.documentElement.scrollTop = 0;
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        });
        this.isSubmit = false;
      },
      openSelect: function () {
        this.$emit("openSelect");
      },
      hiddenSelectBtn: function () {
        this.$emit("hiddenSelectBtn")
      },
      showSelectBtn :function () {
        this.$emit("showSelectBtn");
      }
    },
    computed: {
      modalLeft: function () {
        if (this.isMounted) {
          return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
        } else {
          return;
        }
      },
      modalTop: function () {
        return '0px';
      },
    },
    mounted() {
      this.isMounted = true;
    },
  }
</script>

<style scoped>
  .customercreateform .report-deteil-wrap .el-input__inner {
    padding: 0 15px !important;
  }
</style>
