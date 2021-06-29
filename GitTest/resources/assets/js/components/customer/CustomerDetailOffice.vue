<template>

  <div class="accordion-target officeaccordion" v-if='accordion'>

    <h3>
      <button type="button" class="accordion--trigger" @click.prevent="closeAccordion"><i class="el-icon-minus"></i>
      </button>
      {{ office.name_pre }}
    </h3>
    <ul v-if="mod">
      <el-form :model="office" ref="form" label-width="200px" class="formStyle customercreateform">
        <FormOfficeEdit ref="child" v-model="office" :office="office" :createFlg="createFlg" :named="named"
                        @createBack="createBack" @checkBack="checkBack"/>
        <div class="updateButton">
          <a class="updateBack" @click="closeEdit">キャンセル</a>
          <a class="updateSave" @click="checkOffice">保存</a>
        </div>
      </el-form>
    </ul>
    <ul v-else>
      <div class="modal-container">
        <div class="clearfix">

          <table class="table-mod">
            <a @click.prevent="editCustomerOffice" class="edit"><img src="images/edit@2x.png"/></a>
            <tr>
              <td class="tdLeft">{{ officesName('name') }}</td>
              <td class="tdRight">{{ office.name }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('zip') }}</td>
              <td class="tdRight">〒{{ office.zip }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('pref') }}</td>
              <td class="tdRight">{{ office.pref }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('town') }}</td>
              <td class="tdRight">{{ office.town }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('street') }}</td>
              <td class="tdRight">{{ office.street }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('house') }}</td>
              <td class="tdRight">{{ office.house }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('tel') }}</td>
              <td class="tdRight">{{ office.tel_s }}</td>
            </tr>
            <tr>
              <td class="tdLeft">{{ officesName('fax') }}</td>
              <td class="tdRight">{{ office.fax }}</td>
            </tr>

            <tr>
              <td class="tdLeft">{{ peopleName('name') }}</td>
              <td class="tdRight">
                <div v-for="(person,index) in office.people" v-bind:key="'person' +person.id">
                  <div class="item">
                    <div class="leftDiv"><span>担当者</span></div>
                    <div class="rightDiv"><span>{{index+1}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('name') }}</span></div>
                    <div class="rightDiv"><span>{{person.name}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('position') }}</span></div>
                    <div class="rightDiv"><span>{{person.position}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('dept') }}</span></div>
                    <div class="rightDiv"><span>{{person.dept}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('role') }}</span></div>
                    <div class="rightDiv"><span>{{person.role}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('email') }}</span></div>
                    <div class="rightDiv"><span>{{person.email}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ peopleName('tel') }}</span></div>
                    <div class="rightDiv"><span>{{person.tel}}</span></div>
                  </div>
                  <hr v-if="index < office.people.length - 1"/>
                </div>
              </td>
            </tr>
            <tr>
              <td class="tdLeft">請求先</td>
              <td class="tdRight">
                <div v-for="(billing,index) in office.billings" v-bind:key="'billing' +billing.id">
                  <div class="item">
                    <div class="leftDiv"><span>請求先</span></div>
                    <div class="rightDiv"><span>{{index+1}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ billingsName('name') }}</span></div>
                    <div class="rightDiv"><span>{{billing.name}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ billingsName('zip') }}</span></div>
                    <div class="rightDiv"><span>〒{{ billing.zip }}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>住所</span></div>
                    <div class="rightDiv"><span>{{ billing.pref }}{{ billing.town }}{{ billing.street }}{{ billing.house }}</span>
                    </div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ billingsName('tel') }}</span></div>
                    <div class="rightDiv"><span>{{billing.tel}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ billingsName('dept') }}</span></div>
                    <div class="rightDiv"><span>{{billing.dept}}</span></div>
                  </div>
                  <div class="item">
                    <div class="leftDiv"><span>{{ billingsName('people_name') }}</span></div>
                    <div class="rightDiv"><span>{{billing.people_name}}</span></div>
                  </div>
                  <hr v-if="index < office.billings.length - 1"/>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </ul>
  </div>

  <div v-else class="accordion-target">
    <ul>
      <div class="modal-container">
        <div class="closeaccordion clearfix">
          <table style="border-collapse:collapse;width:800px;table-layout:fixed;">
            <tr>
              <td style="width:40px">
                <button type="button" class="accordion--trigger" @click.prevent="openAccordion"><i
                        class="el-icon-plus"></i></button>
              </td>
              <td style="width:15%">{{ office.name }}</td>
              <td>{{ office.pref }}{{ office.town }}{{ office.street }}{{ office.house }}</td>
              <td>{{ office.tel_s }}</td>
              <td></td>
              <td></td>
            </tr>
            <tr v-for="people in office.people" >
              <td></td>
              <td></td>
              <td></td>
              <td >{{people.name}}</td>
              <td>{{people.email}}</td>
              <td>{{people.tel}}</td>
            </tr>
          </table>
        </div>
      </div>
    </ul>
  </div>

</template>

<script>
  import CustomerCols from '../../mixins/CustomerColsNew';
  import Messages from "../../mixins/Messages";
  import FormOfficeEdit from "./CustomerCreateFormOfficeEdit";

  export default {
    name: 'CustomerDetailOffice',
    components: {
      FormOfficeEdit
    },
    mixins: [CustomerCols, Messages], //項目名定数
    props: ['id', 'office','named'],
    data: function () {
      return {
        accordion: false,  //アコーディオン展開フラグ
        mod: false,
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
            tel_s: "",
            fax: "",
            telIn: '',
            telOut: '',
            people: [],
            billings: [],
          },
        },
        createFlg: true,
        name: "",
        arr: [],
        showMsg: '',
        msgShowFlg: false,
        confirmObj: {},
        confirmShowFlg: false,
        isSubmit: false
      }
    },
    methods: {
      //事業所インタフェースを表示する
      editCustomerOffice: function () {
        this.mod = true;
      },
      //事業所情報を展開する
      openAccordion: function () {
        this.accordion = true;
      },
      //事業所情報を閉鎖する
      closeAccordion: function () {
        this.accordion = false;
      },
      //事業所のインターフェースを閉鎖する
      closeEdit: function () {
        this.mod = false;
      },
      //事業所を削除する
      deleteOffice: function () {
        this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
          this.$alert(this.commonMessage.success.delete, {showClose: false});
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post("/api/deleteCustomerOffice", {
            id: this.office.id,
            office: this.office
          }).then(res => {
            loading.close();
            this.$router.go(0);
          }).catch(action => {
            loading.close();
          });
        }).catch(action => {
        });
      },
      //事業所情報はリカレントを作成する
      createBack: function (data) {
          this.$set(data, 'telOut', data.tel);
          this.$set(data, 'telIn', data.tel_in);
        this.customer = data;
      },
      //検証をサブフォームに提出する
      checkOffice() {
        this.isSubmit = true;
        this.$refs['child'].checkOffice();
      },
      //サブフォームを振り替えます
      checkBack(data) {
        if (data) {
          this.isSubmit = true;
          this.submitForm();
        } else {
          this.isSubmit = false;
          setTimeout(() => {
            let isError = document.getElementsByClassName("is-error");
            isError[0].querySelector('input').focus();
          }, 1);
          return false;
        }
      },
      //FORMの提出
      submitForm: function () {
        this.isSubmit = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let url;
            let msg = this.commonMessage.success.update;
            let errMsg = this.commonMessage.error.customerEdit;
            url = '/api/updateOffice';
            axios.post(url, {
              customer: this.customer,
            }).then(res => {
              if (res.data.result === 0) {    //ok
                this.$alert(msg, {showClose: false}).then(() => {
                  this.office.name_pre = this.office.name;
                  this.office.tel_s = this.change_tel(this.office.tel,this.office.tel_in);
                  this.mod = false;
                  loading.close();
                });
              } else if (res.data.result === 1) {
                this.isSubmit = false;
                this.$alert(res.data.errors, {showClose: false});
                loading.close();
              } else {
                this.isSubmit = false;
                loading.close();
              }
            }).catch(err => {
              this.isSubmit = false;
              this.$alert(errMsg);
              loading.close();
            });
          } else {
            this.isSubmit = false;
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
      change_tel(tel,tel_in) {
        if (tel_in) {
          return tel + " 内線" +tel_in;
        } else {
          return tel;
        }
      }
    },
  }
</script>
<style scoped>
  hr {
    padding-right: 20px;
    color: #FFF;
  }

  .table-mod {
    border-collapse: collapse;
  }
</style>
