<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="report-deteil-wrap clearfix">
          <dl class="clearfix">
            <dt></dt>
            <dd>{{showMsg}}</dd>
          </dl>
          <div class="clearfix"></div>
          <div class="button-wrap clearfix">
            <div class="button-lower"><a href="javascript:void(0)" @click="back">確認</a></div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import CustomerCols from '../../mixins/CustomerColsNew'

  export default {
    mixins: [CustomerCols],  //項目名定数
    props: {
      showMsg: String,
    },
    data: function () {
      return {
        isMounted: false,
      }
    },
    methods: {
      back: function () {
        this.$emit("msgShowOver");
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
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>
