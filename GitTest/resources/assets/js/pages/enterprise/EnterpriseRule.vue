<template>
  <div class="enterprise-common enterprise-rule">
    <div class="image">
      <div class="logo"></div>
    </div>
    <div class="enterprise-rule-form">
      <h2 class="word1">利用規約・プライバシーポリシー</h2>
      <div class="enterprise-rule-remind">以下の内容をよくお読みいただき、十分ご理解の上、ご同意して次に進んでください。</div>
      <div class="ruleBorder">
        <div class="ruleBorder-head">
          <p class="ruleBorder-title">利用規約</p>
          <p class="ruleBorder-content"> 第一条概要</p>
        </div>
        <p class="ruleBorder-text">利用規約内容........</p>
      </div>
      <div class="agree">
        <label>
          <el-checkbox type="checkbox" v-model="isCheckedFrist">利用規約に同意する</el-checkbox>
        </label>
      </div>
      <div class="ruleBorder">
        <div class="ruleBorder-head">
          <p class="ruleBorder-title">プライバシーポリシー</p>
        </div>
        <p class="ruleBorder-text">プライバシーポリシーの内容........</p>
      </div>
      <div class="agree">
        <label>
          <el-checkbox type="checkbox" v-model="isCheckedTwo">ブライバシーボりシーに同意する</el-checkbox>
        </label>
      </div>
    </div>
      <el-button @click="buttonStatus" :class="[isCheckedFrist && isCheckedTwo? 'checkd-color' :'noCheckd-color','rule-btn']">同意して次に進む</el-button>

  </div>
</template>

<script>
  import Messages from "../../mixins/Messages";
  export default {
    mixins: [ Messages],
    name: "EnterpriseRule",
    data() {
      return {
        isCheckedFrist: false,
        isCheckedTwo: false
      }
    },
    methods: {
      //checkboxの選択状態
      buttonStatus: function () {
        let errMsg = this.commonMessage.warning.agree;
        if (!this.isCheckedFrist || !this.isCheckedTwo){
          this.$alert(errMsg,{showClose: false});
        }else{
          this.$router.push({path: '/enterprise/register'});
        }
      }
    },
    created() {
      try {
        sessionStorage.from_user_id = this.$route.params.fromUserId;
        sessionStorage.token = this.$route.params.token;
      } catch (error) {
      }
    }
  }
</script>
