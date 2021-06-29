<template>
  <div>
    <transition name="fade">
      <div class="modal wd1 modal-show">
        <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
          <div class="clearfix">
            <div class="button-lower remark mig">
              <router-link to="/">認証失敗</router-link>
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
    name: "ErrorMail",
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

  .mig {
    margin: 30px 250px 30px 250px;
    width: 280px;
  }

  img {
    height: 62.38px;
  }
</style>

