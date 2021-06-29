<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="report-deteil-wrap clearfix">
          <img src="images/friend-000001.png" alt="〇〇〇工務店">
          <dl class="clearfix">
            <dt class="schedule icon-s">作成</dt>
            <dd>{{report.log_date}}</dd>

            <dt class="no icon-s">no</dt>
            <dd>123456789</dd>

            <dt class="name icon-s">案件名</dt>
            <dd>{{report.title}}</dd>

            <dt class="user icon-s">作成者</dt>
            <dd>{{report.user_id}}</dd>

            <dt class="construction icon-s">作業内容</dt>
            <dd>{{report.title}}</dd>

            <dt class="place icon-s">作業場所</dt>
            <dd>{{report.location}}</dd>

            <dt class="note icon-s">備考</dt>
            <dd>
              {{report.note}}
            </dd>
          </dl>
        </div>

        <div class="modal-button-wrap clearfix">
          <div class="button-lower">
            <router-link :to="{name:'reportEdit',params:{id:report.id}}">この日報を編集する</router-link>
          </div>
          <div class="button-lower"><a href="friend-to_offer.html">この担当者にオファーする</a></div>
          <div class="button-lower"><a href="report-new.html">新しい日報を作成する</a></div>
          <div class="button-lower"><a href="report.html">日報一覧をみる</a></div>
        </div>
      </div>

      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  export default {
    name: 'ReportDetailModal',
    props: {
      report: Object,
    },
    data: function () {
      return {
        isMounted: false,
      }
    },
    methods: {
      deleteCustomer: function () {
        axios.post("/api/deleteCustomer", {
          id: this.customer.id,
        }).then(res => {
          this.$emit("customerDeleted");
          this.$emit("detailClose");
          console.log("deleted");
        });
      },
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
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>