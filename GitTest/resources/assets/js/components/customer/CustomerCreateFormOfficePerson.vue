<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show customerofficeadd">

      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close"  @click="back">×</div>
        <div class="modalBodycontent customer-add">
          <h3 class="modal-content-head">担当者追加</h3>
          <div class="report-deteil-wrap clearfix">
            <el-form :model="person" :rules="customerOfficePersonRules" ref="form" class="model-err">
              <el-form-item prop="name" class="billingname">
                <el-input :placeholder="peopleName('name')" v-model="person.name" maxlength="30"></el-input>
              </el-form-item>
              <el-form-item prop="position">
                <el-input :placeholder="peopleName('position')" v-model="person.position" maxlength="20"></el-input>
              </el-form-item>
              <el-form-item prop="dept">
                <el-input :placeholder="peopleName('dept')" v-model="person.dept" maxlength="20"></el-input>
              </el-form-item>
              <el-form-item prop="role">
                <el-input :placeholder="peopleName('role')" v-model="person.role" maxlength="20"></el-input>
              </el-form-item>
              <el-form-item prop="email">
                <el-input :placeholder="peopleName('email')" v-model="person.email" maxlength="254"></el-input>
              </el-form-item>

              <el-form-item prop="tel">
                <el-input :placeholder="peopleName('tel')" v-model="person.tel" maxlength="15"></el-input>
              </el-form-item>
            </el-form>
            <div class="clearfix"></div>
            <div class="button-wrap clearfix">
              <div class="button-lower remark" v-if="isCreate"><a href="javascript:void(0)" @click="addPerson">登録</a>
              </div>
              <div class="button-lower remark" v-if="!isCreate"><a href="javascript:void(0)" @click="addPerson">登録</a>
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
      peopleObj: Object,
      isCreate: Boolean,
    },
    data: function () {
      return {
        isMounted: false,
        person: {
          dept: "",
          email: "",
          name: "",
          position: "",
          role: "",
          tel:"",
        },
        billing_equal: false,
      }
    },
    methods: {
      //ピープルを加えます
      addPerson: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.isCreate) {
              this.$emit("createPerson", this.person, "create");
            } else {
              this.$emit("createPerson", this.person, "update");
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
        this.person.dept = this.peopleObj.dept;
        this.person.email = this.peopleObj.email;
        this.person.name = this.peopleObj.name;
        this.person.position = this.peopleObj.position;
        this.person.role = this.peopleObj.role;
        this.person.tel = this.peopleObj.tel;

      }
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>
