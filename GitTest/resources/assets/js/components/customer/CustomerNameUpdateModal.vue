<template>
  <div>
    <transition name="fade">
      <div class="customer-detail-modal-mask customernameupdate">
        <div class="modal-wrapper" @click.self="$emit('detailClose')">
          <div class="modal-container">
            <div class="report-deteil-wrap clearfix">
              <el-form :model="customer" :rules="customerRules" ref="form" label-width="150px" class="formStyle customercreateform">

                <div class="form-group">
              <el-form-item :label="customersName('name')" prop="name" class="nameupdata fristinput">
                <el-input  :placeholder="customersName('name')" v-model="customerUpdate.name" maxlength="50"></el-input>
                <button class="del_btn" @click.prevent="deleteCustomer">削除</button>
              </el-form-item>
            </div>
                <div class="form-group">
              <el-form-item :label="customersName('phonetic')" prop="phonetic">
                <el-input  :placeholder="customersName('phonetic')"  v-model="customer.phonetic" maxlength="50"></el-input>
              </el-form-item>
            </div>
              </el-form>
            </div>
            <div class="customernameupdatebuttons">
              <div class="button-lower"><a href="javascript:void(0)" @click.prevent="closeModal">キャンセル</a></div>
              <div class="button-lower remark remarkright"><a href="javascript:void(0)" @click.prevent="save">保存</a></div>
            </div>

          </div>
        </div>
      </div>
    </transition>
  </div>
</template>


<script>
  import CustomerCols from '../../mixins/CustomerColsNew';
  import validation from '../../validations/customer';
  import Messages from "../../mixins/Messages";

  export default {
    name: "CustomerNameUpdateModal",
    mixins: [CustomerCols,validation,Messages], //定数
    props: {
      customer: Object,
    },
    data: function () {
      return {
        isMounted: false,
        customerUpdate: {name:this.customer.name},
      }
    },

    methods: {
      //ふり
      back() {
        this.$router.go(0);
      },
      //戻り窓を閉めます
      closeModal() {
        this.$emit('closeUpdateModal');
      },
      //帳票の提出
      save: function () {
        let errMsg = this.commonMessage.error.update;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            this.customer.name = this.customerUpdate.name;
            axios.post("/api/updateCustomerName", {
              customer: this.customer
            }).then(res => {
              loading.close();
              this.closeModal();
            }).catch(err => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            this.$alert(errMsg, {showClose: false});
          }
        }).catch(err => {
        });
      },
      //ドナー情報を削除する
      deleteCustomer: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
          this.$alert(this.commonMessage.success.delete, {showClose: false});
          axios.post("/api/deleteCustomer", {
            id: this.customer.id,
            customer: this.customer
          }).then(res => {
            loading.close();
            this.$emit("customerDeleted");
            this.$emit("detailClose");
            this.$router.push({path: "/customer"});
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
      //ヒント弾窓を削除します
      openDelModal() {
        this.delCheck = true;
      },
      //提示窓を削除して閉じる
      closeDelModal() {
        this.delCheck = false;
      },
    },
  }
</script>

<style scoped>
  .customer-detail-modal-mask {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, .5);
    display: table;
    transition: opacity .3s ease;
  }
  .modal-wrapper {
    display: table-cell;
    vertical-align: middle;
  }
  .modal-container {
    width: 70%;
    height: 30%;
    overflow: scroll;
    margin: 0 auto;
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
    font-family: Helvetica, Arial, sans-serif;
  }
  .del_btn{
    margin-left:40px;
    padding:0 20px;
  }
</style>
