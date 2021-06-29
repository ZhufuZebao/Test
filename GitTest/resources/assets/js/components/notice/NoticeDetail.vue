<template>
  <transition name="fade">
    <div class="common-view account-list noticedetail-model">
            <div class="content delet-img" v-if="ListShow">
              <h3 class="modal-account-head" style="text-align:center;">{{notice.title}}</h3>
              <section class="list-del customer-wrapper invite-form">
                  <div class="customertable">
                    <div class="table-scroll form-group">{{notice.content}}</div>
                  </div>
                <div class="pro-button">
                  <el-button class="modoru" @click="$emit('closeNotice',1)">OK</el-button>
                 
                </div>
              </section>
            </div>
          </div>
  </transition>
</template>


<script>
  import Messages from "../../mixins/Messages";
  import NoticeList from "./NoticeList";

  export default {
    name: "NoticeDetail",
    components: {
      NoticeList,
    },
    mixins: [Messages],
    props: {
      noticeId: 0,
    },
    data: function () {
      return {
        notice: {},
        isMounted: false,
        ListShow: true,
      }
    },
    methods: {
      fetchDetail() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        axios.post('/api/getNoticeDetail', {
          id: this.$props.noticeId,
        }).then((res) => {
          this.notice = res.data;
          loading.close();
        }).catch(error => {
          loading.close();
          console.warn(error);
          this.$alert(errMessage, {showClose: false});
        });
      },
      closeNotice: function () {
        this.$emit('closeNotice',0);
      },
    },
    created() {
      this.fetchDetail();
    },
    mounted() {
      this.isMounted = true;
    },
    watch: {},
    //窓際の位置を計算する
    computed: {
      modalLeft: function () {
        if (this.isMounted) {
          return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
        } else {
          return;
        }
      },
      modalTop: function () {
        return '0px';
      },
    },
  }
</script>
