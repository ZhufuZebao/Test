<template>
  <div class="customercreate">
    <el-form :model="$props.office" :rules="customerOfficeRules" ref="form" label-width="200px"
             @submit.native.prevent onkeypress="if (event.keyCode === 13) return false;">
      <div class="form-group">
        <el-form-item :label="officesName('name')" prop="name" class="nameupdata fristinput">
          <el-input :placeholder="officesName('name')" v-model="$props.office.name" maxlength="50"></el-input>
          <button @click.prevent="deleteCustomer">削除</button>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label="officesName('zip')" prop="zip" class="officeszip">
          <span style="padding-left:30px">〒</span>
          <el-input style="padding-left:0" v-model="$props.office.zip" maxlength="7"></el-input>
          <span  style="font-size: 12px">※（半角数字）ハイフン（-）なしで入力してください。</span>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label="officesName('pref')" prop="pref">
          <el-input v-model="$props.office.pref" maxlength="30"></el-input>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label="officesName('town')" prop="town">
          <el-input v-model="$props.office.town" maxlength="30"></el-input>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label="officesName('street')" prop="street">
          <el-input v-model="$props.office.street" maxlength="30"></el-input>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label="officesName('house')">
          <el-input v-model="$props.office.house" maxlength="20"></el-input>
        </el-form-item>
      </div>
      <div class="form-group offices_tel">
        <el-form-item :label="officesName('tel')" prop="telOut" class="officestel">
          <el-input v-model="$props.office.telOut" :placeholder="officesName('tel')" maxlength="15"></el-input>
        </el-form-item>
        <el-form-item label="内線" prop="telIn" class="officestelIn">
          <el-input v-model="$props.office.telIn" placeholder="内線" maxlength="15"></el-input>
        </el-form-item>
      </div>
      <div class="form-group officesfax">
        <el-form-item :label="officesName('fax')">
          <el-input v-model="$props.office.fax" maxlength="30"></el-input>
        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item :label=" billingsName('people_name')">
          <div class="form-group formadd" v-for="(people,index) in peopleTmp" :key="index">
            <el-form-item :label="peopleName('name')" class="nameupdata" :prop="'people.' + index + '.name'"
                          :rules="customerOfficePersonRules.name">
              <el-input v-model="people.name"></el-input>
              <button @click="showPeopleDelMsg(index)">削除</button>
            </el-form-item>
            <el-form-item :label="peopleName('position')" prop="position" :rules="customerOfficePersonRules.position">
              <el-input v-model="people.position"></el-input>
            </el-form-item>
            <el-form-item :label="peopleName('dept')" prop="dept" :rules="customerOfficePersonRules.dept">
              <el-input v-model="people.dept"></el-input>
            </el-form-item>
            <el-form-item :label="peopleName('role')" prop="role" :rules="customerOfficePersonRules.role">
              <el-input v-model="people.role"></el-input>
            </el-form-item>
            <el-form-item :label="peopleName('email')" :prop="'people.' + index + '.email'"
                          :rules="customerOfficePersonRules.email">
              <el-input v-model="people.email" maxlength="254"></el-input>
            </el-form-item>
            <el-form-item :label="peopleName('tel')" :prop="'people.' + index + '.tel'"
                          :rules="customerOfficePersonRules.tel">
              <el-input v-model="people.tel"  maxlength="15"></el-input>
            </el-form-item>
          </div>
          <el-button type="primary" round @click.prevent="showPeopleDetail">
            <!--施主情報登録-->
            担当者追加
          </el-button>

        </el-form-item>
      </div>
      <div class="form-group">
        <el-form-item label="請求先">
          <div class="form-group formadd" v-for="(billings,index) in billingsTmp" :key="index+'-label'">
            <el-form-item :label="billingsName('name')" class="nameupdata" :prop="'billings.' + index + '.name'"
                          :rules="customerOfficeBillingRules.name">
              <el-input v-model="billings.name"></el-input>
              <button @click="showBillingDelMsg(index)">削除</button>
            </el-form-item>
            <el-form-item :label=" billingsName('zip')" :prop="'billings.' + index + '.zip'"
                          :rules="customerOfficeBillingRules.zipEdit">
              <el-input v-model="billings.zip"></el-input>
            </el-form-item>
            <el-form-item :label=" billingsName('pref')" :prop="'billings.' + index + '.pref'"
                          :rules="customerOfficeBillingRules.pref">
              <el-input v-model="billings.pref"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('town')" :prop="'billings.' + index + '.town'"
                          :rules="customerOfficeBillingRules.town">
              <el-input v-model="billings.town"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('street')" :prop="'billings.' + index + '.street'"
                          :rules="customerOfficeBillingRules.street">
              <el-input v-model="billings.street"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('house')">
              <el-input v-model="billings.house"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('tel')" :prop="'billings.' + index + '.tel'"
                          :rules="customerOfficeBillingRules.tel">
              <el-input v-model="billings.tel"  maxlength="15"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('dept')">
              <el-input v-model="billings.dept"></el-input>
            </el-form-item>
            <el-form-item :label="billingsName('people_name')">
              <el-input v-model="billings.people_name"></el-input>
            </el-form-item>
          </div>
          <el-button type="primary" round @click.prevent="showBillingDetail">
            請求先追加
          </el-button>
        </el-form-item>
      </div>
      <ul>
        <FormOfficePerson v-if="showPeople" @back="closePeopleShow()" @createPerson="createPeople"
                          :peopleObj="office.people[peopleSelIndex]" :isCreate="isCreate"/>
      </ul>

      <ul>
        <FormOfficeBilling v-if="showBilling" @back="closeBillingShow()" @createBilling="createBilling" :named="named"
                           :billingObj="office.billings[billingSelIndex]" :isCreate="isCreate" :office="office"/>
      </ul>

    </el-form>
  </div>
</template>
<script>
  import CustomerCols from '../../mixins/CustomerColsNew';
  import FormOfficePerson from './CustomerCreateFormOfficePerson.vue';
  import FormOfficeBilling from './CustomerCreateFormOfficeBilling.vue';
  import validation from '../../validations/customer';
  import Messages from "../../mixins/Messages";

  export default {
    name: 'FormOfficeEdit',
    components: {
      FormOfficePerson,
      FormOfficeBilling,
    },
    mixins: [CustomerCols, validation, Messages,],  //項目名定数
    props: {
      named:'',
      office: {
        pref: '',
        town: '',
        street: '',
        telIn: '',
        telOut: '',
        billings: [],
        people: [],
        tel: ''
      },
      createFlg: Boolean,
    },
    data: function () {
      return {
        zipFlag: 0,
        zipFlagEdit: [],
        showPeople: false,
        showBilling: false,
        peopleSelIndex: 0,
        billingSelIndex: 0,
        isCreate: true,
        billingsTmp: [],
        peopleTmp: [],
        isPeople: false,
        msg: '',
        telIn: '',
        telOut: ''
      };
    },
    methods: {
      //子票検証
      checkOffice() {
        this.isSubmit = true;
        this.$refs['form'].validate((valid) => {
          this.$emit("checkBack", valid);
        });
      },
      //ピープル削除ヒント
      showPeopleDelMsg: function (index) {
        this.isPeople = true;
        this.msg = this.peopleTmp[index].name;
        this.peopleSelIndex = index;
        this.showConfirm();
      },
      //ビリング削除案内
      showBillingDelMsg: function (index) {
        this.isPeople = false;
        this.msg = this.billingsTmp[index].name;
        this.billingSelIndex = index;
        this.showConfirm();
      },
      //ピープル修正
      changePeople: function (index) {
        this.isCreate = false;
        this.peopleSelIndex = index;
        this.showPeople = true;
      },
      //ピープル削除
      delPeople: function () {
        this.peopleTmp.splice(this.peopleSelIndex, 1);
        this.office.people = this.peopleTmp;
        this.dataBack();
      },
      //ビリング修正
      changeBilling: function (index) {
        this.isCreate = false;
        this.billingSelIndex = index;
        this.showBilling = true;
      },
      //ビリング削除
      delBilling: function (index) {
        this.billingsTmp.splice(this.billingSelIndex, 1);
        this.office.billings = this.billingsTmp;
        this.dataBack();
      },
      //ビリング作成
      createBilling: function (data, status) {
        this.closeBillingShow();
        if (status === "create") {
          this.office.billings.push(data);
        } else if (status === "update") {
          this.office.billings[this.billingSelIndex] = data;
        }
        this.dataBack();
      },
      //ビリングオフ表示
      closeBillingShow: function () {
        this.showBilling = false;
      },
      //ピープルが作った
      createPeople: function (data, status) {
        this.closePeopleShow();
        if (status === "create") {
          this.office.people.push(data);
        } else if (status === "update") {
          this.office.people[this.peopleSelIndex] = data;
        }
        this.dataBack();
      },
      //ピープルは表示をキャンセルする
      closePeopleShow: function () {
        this.showPeople = false;
      },
      //ピープルが詳しく示している
      showPeopleDetail: function () {
        this.isCreate = true;
        this.showPeople = true;
      },
      //ビリングが詳しく示されている
      showBillingDetail: function () {
        this.isCreate = true;
        this.showBilling = true;
      },
      //officeが再調整を作成する
      dataBack: function () {
        this.$emit("createBack", this.office);
      },
      //窓を弾くよう促す
      showConfirm() {
        this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
          if (this.isPeople) {
            this.delPeople();
          } else {
            this.delBilling();
          }
          this.$alert(this.commonMessage.success.delete, {showClose: false});
        }).catch(action => {
        });
      },
      //事業所を削除する
      deleteCustomer: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.office;
        let errMsgBind = this.commonMessage.error.officeBind;
        this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
          axios.post("/api/deleteCustomerOffice", {
            id: this.office.id
          }).then(res => {
            if (res.data.result > 0) {
              loading.close();
              if (res.data.errors[0] === 'messages.error.officeBind') {
                this.$alert(errMsgBind, {showClose: false});
              } else {
                this.$alert(errMsg, {showClose: false});
              }
            } else {
              loading.close();
              this.$alert(this.commonMessage.success.delete, {showClose: false});
              this.$router.push({path: "/customer/"});
            }
          }).catch(action => {
            loading.close();
          });
        }).catch(action => {
          loading.close();
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
    },
    updated() {
      this.peopleTmp = this.office.people;
      this.billingsTmp = this.office.billings;
      // if (this.office.telIn !== "" && this.office.telOut !== "") {
      //   this.office.tel = this.office.telOut + '-' + this.office.telIn;
      // } else if (this.office.telIn === "" && this.office.telOut !== "") {
      //   this.office.tel = this.office.telOut;
      // } else if (this.office.telOut === "" && this.office.telIn !== "") {
      //   this.office.tel = this.office.telIn;
      // } else {
      //   this.office.tel = "";
      // }
       this.office.tel_in=this.office.telIn;
    },
    mounted() {
      if (!this.zipFlagEdit.length && this.office.billings.length > 0) {
        for (let i=0;i<this.office.billings.length;i++){
          this.zipFlagEdit.push(this.office.billings[i].zip);
        }
        this.zipFlagEdit=JSON.parse(JSON.stringify(this.zipFlagEdit));
      }
      this.zipFlag = this.office.zip;
      this.peopleTmp = this.office.people;
      this.billingsTmp = this.office.billings;
      this.zipTmp = {'address1':this.office.pref,'address2':this.office.town,'address3':this.office.street,prefcode: '1',zipcode: this.office.zip};
      this.$emit("createBack", this.office);
    }
  };
</script>
<style scoped>
  .div-inline {
    display: inline
  }
</style>
