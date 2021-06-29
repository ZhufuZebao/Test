<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show customerofficeadd">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="back">×</div>
        <div class="modalBodycontent customer-add">
          <h3 class="modal-content-head">請求先追加</h3>

          <div class="report-deteil-wrap clearfix customer-create-form">
            <el-form :model="billing" :rules="customerOfficeBillingRules" ref="form"  class="model-err">
              <el-form-item :label="billingsName('checkBoxMsg')">
                <el-checkbox v-model="isChecked" @change="changeEqual"></el-checkbox>
              </el-form-item>
              <el-form-item prop="name" class="billingname">
                <el-input :placeholder="billingsName('name')" v-if="equal && office.name" v-model="office.name"
                          :disabled="true"></el-input>
                <el-input :placeholder="billingsName('name')" v-else-if="equal && !office.name && named" v-model="named"
                          :disabled="true"></el-input>
                <el-input :placeholder="billingsName('name')" v-else v-model="billing.name" maxlength="30"></el-input>
              </el-form-item>
                <el-row style="padding-left:0; width: 75%;margin: 0 auto;">
                    <el-col :span="12">
                        <el-form-item prop="zip" style="margin-left: 0">
                            <el-input  style="width: 90%;margin-right: 10%;" placeholder="郵便番号 ※半角数字「-」なし" v-if="equal" v-model="office.zip"
                                      :disabled="true"></el-input>
                            <el-input  style="width: 90%;margin-right: 10%;" placeholder="郵便番号 ※半角数字「-」なし" v-else v-model="billing.zip" maxlength="7"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="pref" style="margin-left: 0">
                            <el-input  style="width: 90%;margin-left: 10%;" :placeholder="billingsName('pref')" v-if="equal" v-model="office.pref"
                                      :disabled="true"></el-input>
                            <el-input  style="width: 90%;margin-left: 10%;" :placeholder="billingsName('pref')" v-else v-model="billing.pref" maxlength="20"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row style="padding-left:0; width: 75%;margin: 0 auto;">
                    <el-col :span="12">
                        <el-form-item prop="town" style="margin-left: 0" >
                            <el-input style="width: 90%;margin-right: 10%;" :placeholder="billingsName('town')" v-if="equal" v-model="office.town"
                                      :disabled="true"></el-input>
                            <el-input style="width: 90%;margin-right: 10%;" :placeholder="billingsName('town')" v-else v-model="billing.town" maxlength="30"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="street" style="margin-left: 0">
                            <el-input style="width: 90%;margin-left: 10%;"  :placeholder="billingsName('street')" v-if="equal" v-model="office.street"
                                      :disabled="true"></el-input>
                            <el-input style="width: 90%;margin-left: 10%;" :placeholder="billingsName('street')" v-else v-model="billing.street"
                                      maxlength="30"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
              <el-form-item prop="house">
                <el-input :placeholder="billingsName('house')" v-if="equal" v-model="office.house"
                          :disabled="true"></el-input>
                <el-input :placeholder="billingsName('house')" v-else v-model="billing.house" maxlength="30"></el-input>
              </el-form-item>
              <el-form-item prop="tel">
                <el-input :placeholder="billingsName('tel')" v-if="equal" v-model="office.telOut"
                          :disabled="true"></el-input>
                <el-input :placeholder="billingsName('tel')" v-else v-model="billing.tel" maxlength="15"></el-input>
              </el-form-item>

                <el-row style="padding-left:0; width: 75%;margin: 0 auto;">
                    <el-col :span="12">
                        <el-form-item prop="dept" style="margin-left: 0" >
                            <el-input style="width: 90%;margin-right: 10%;"  class="billingsin-err" :placeholder="billingsName('dept')" v-model="billing.dept" maxlength="20"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="people_name" style="margin-left: 0">
                            <el-input  style="width: 90%;margin-left: 10%;"  class="billingsin-err" :placeholder="billingsName('people_name')" v-model="billing.people_name"
                                      maxlength="30"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <div class="clearfix"></div>
            <div class="button-wrap clearfix">

              <div class="button-lower remark" v-if="isCreate"><a href="javascript:void(0)" @click="addBilling">登録</a>
              </div>
              <div class="button-lower remark" v-if="!isCreate"><a href="javascript:void(0)" @click="addBilling">登録</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import CustomerCols from '../../mixins/CustomerColsNew';
  import validation from '../../validations/customer';

  export default {
    mixins: [CustomerCols,validation],  //項目名定数
    props: {
      billingObj: Object,
      isCreate: Boolean,
      office: Object,
      named: String,
    },
    data: function () {
      return {
        bilZipFlag:0,
        equal: false,
        isChecked: false,
        isMounted: false,
        billing: {
          name: '',
          zip: '',
          pref: '',
          town: '',
          street: '',
          house: '',
          tel: '',
          fax: '',
          people_name: '',
          dept: '',
        },
      }
    },
    methods: {
      //仮値を修正する
      changeEqual: function () {
        this.equal = !this.equal;
        if (this.equal) {
            if(this.office.name){
                this.$set(this.billing,'name',this.office.name);
            }else{
                this.$set(this.billing,'name',this.named);
            }
          this.$set(this.billing,'zip',this.office.zip);
          this.bilZipFlag=this.office.zip;
          this.$set(this.billing,'pref',this.office.pref);
          this.$set(this.billing,'town',this.office.town);
          this.$set(this.billing,'street',this.office.street);
          this.$set(this.billing,'house',this.office.house);
          this.$set(this.billing,'tel',this.office.telOut);
          this.$set(this.billing,'telIn',this.office.telIn);
          this.$set(this.billing,'fax',this.office.fax);
          this.$set(this.billing,'people_name',"");
          this.$set(this.billing,'dept',"");
        } else {
          this.billing.name = "";
          this.billing.zip = "";
          this.bilZipFlag='';
          this.billing.pref = "";
          this.billing.town = "";
          this.billing.street = "";
          this.billing.house = "";
          this.billing.tel = "";
          this.billing.fax = "";
          this.billing.people_name = "";
          this.billing.dept = "";
        }
      },
      //ビリングが増加する
      addBilling: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.isCreate) {
              this.$emit("createBilling", this.billing, "create");
            } else {
              this.$emit("createBilling", this.billing, "update");
            }
          } else {
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        });
      },
      //回調
      back: function () {
        this.$emit("back");
      },
      //弾窓を閉じる
      closeDelModal() {
        this.delCheck = false;
      },
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      }
    },
    mounted() {
      this.isMounted = true;
      if (this.isCreate) {
      } else {
        this.billing.name = this.named;
        this.billing.zip = this.billingObj.zip;
        this.bilZipFlag=this.billing.zip;
        this.billing.pref = this.billingObj.pref;
        this.billing.town = this.billingObj.town;
        this.billing.street = this.billingObj.street;
        this.billing.house = this.billingObj.house;
        this.billing.tel = this.billingObj.tel;
        this.billing.fax = this.billingObj.fax;
        this.billing.people_name = this.billingObj.people_name;
        this.billing.dept = this.billingObj.dept;
      }
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>
