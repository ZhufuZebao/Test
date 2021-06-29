<template>
  <transition name="fade">
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
          <iframe :src="src" frameborder="0" allowfullscreen
                  class="common-deteil-wrap report-deteil-wrap clearfix map-iframe"></iframe>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>
<script>
  export default {
    name: "ProjectMapModal",
    props: {
      project: Object,
    },
    data: function () {
      return {
        src: '',
        isMounted: false,
        show: false,
        position: {},
      }
    },
    mounted() {
      this.isMounted = true;
      this.fetch();
    },
    methods: {
      fetch: function () {
        this.src = "https://maps.google.co.jp/maps?&output=embed&q=" +
            this.project.address + "&z=17";
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
    }
  }
</script>
<style scoped>
  .modal-show {
    display: block;
  }
</style>
