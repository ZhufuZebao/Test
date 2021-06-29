<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="report-deteil-wrap confirm-content clearfix">
          <dl class="clearfix">
            <dd><div>「施主：{{msgObject.message}}」を登録しました</div></dd>
          </dl>
          <div class="clearfix"></div>
          <div class="button-wrap  clearfix">
            <div class="button-lower"><a href="javascript:void(0)"
                                         @click="confirmCancel"><div>{{msgObject.btnCancelText}}</div><div>を連続追加＋</div></a></div>
            <div class="button-lower"><a href="javascript:void(0)"
                                         @click="confirmSubmit">{{msgObject.btnSubmitText}}</a></div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  export default {
    props: ['msgObject'],
    data: function () {
      return {
        isMounted: false,
      }
    },
    methods: {
      back: function () {
        this.$emit("msgShowOver");
      },
      confirmCancel() {
        if (typeof (this.msgObject.btnCancelFunction) === 'function') {
          this.msgObject.btnCancelFunction()
        }
      },
      confirmSubmit() {
        if (typeof (this.msgObject.btnSubmitFunction) === 'function') {
          this.msgObject.btnSubmitFunction()
        }
      }
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
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>
