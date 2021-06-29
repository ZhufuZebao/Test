<template>
  <div>
    <transition name="fade">
      <div class="modal wd1 modal-show">
        <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">

          <div class="report-deteil-wrap clearfix">
            <dl class="clearfix ">
              <dd>　メールアドレス仮で変更されました。</dd>
              <dd>　入力したのメールアドレスに確認してください。</dd>
              <dd>　新しいメールアドレスを認証してない場合は、旧メールのまま使って下さい。</dd>
            </dl>
          </div>
          <div class="clearfix">
            <div class="button-lower remark" style="margin-left: 270px;">
              <router-link to="/enterprise">プロフィールへ戻る</router-link>
            </div>
          </div>
        </div>
        <div class="modalBK"></div>
      </div>
    </transition>
  </div>
</template>

<script>

  export default {
    name: "EnterpriseAfterMail",
    data: function () {
      return {
        isMounted: false,
      }
    },
    mounted() {
      this.isMounted = true;
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
  }
</script>
<style scoped>
  .modal-show {
    display: block;
  }
</style>

