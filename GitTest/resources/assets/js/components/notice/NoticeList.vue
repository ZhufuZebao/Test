<template>
  <transition name="fade">
    <div class="modal wd1 modal-show account commonAll customerlist project-enterprise noticelist-model">
      <div class="modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="closeNotice(0)">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-view account-list">
            <div class="content" v-if="listShow">
              <section class="list-del customer-wrapper invite-form">
                <div class="customertable">
                  <ul class="noticelis-table">
                    <li class="customer-li">
                      件名
                    </li>
                    <li class="customer-li">
                      掲載開始日
                    </li>
                  </ul>
                  <ul class="table-scroll">
                    <li :id="'n'+notice.id" class="clearfix btns" date-tgt="wd1" v-for="notice in notices"
                        :key="notice.id" @click="showDetail(notice.id)">
                      <span class="customer-li left-padding" v-if="notice.title && !notice.alreadyRead" style="font-weight:bold">
                          {{notice.title}}
                        </span>
                      <span class="customer-li left-padding" v-if="notice.st_date && !notice.alreadyRead" style="font-weight:bold">
                        <span class="customer-li">{{ notice.st_date }}</span>
                      </span>
                      <span class="customer-li left-padding" v-if="notice.title && notice.alreadyRead">
                          {{notice.title}}
                        </span>
                      <span class="customer-li left-padding" v-if="notice.st_date && notice.alreadyRead" >
                        <span class="customer-li">{{ notice.st_date }}</span>
                      </span>
                    </li>
                  </ul>
                </div>
              </section>
            </div>
            <NoticeDetail v-if="noticeDetail" @closeNotice="closeNotice" :noticeId="noticeId"/>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>

<script>
  import Messages from "../../mixins/Messages";
  import NoticeDetail from "./NoticeDetail";

  export default {
    name: "NoticeList",
    components: {NoticeDetail},
    mixins: [Messages],
    props: {
      enterpriseId: 0,
    },
    data: function () {
      return {
        notices:[],
        listShow: true,
        isMounted: false,
        noticeDetail: false,
        noticeId:'',
      }
    },
    methods: {
      fetch(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getNoticeList').then((res) => {
          this.notices = res.data;
          let user = {};
          let _user_info = sessionStorage.getItem('_user_info');
          if (_user_info) {
            user = JSON.parse(_user_info);
          }
          this.$emit('getNoticeCount');
          sessionStorage.setItem('_user_info',JSON.stringify(user));
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.noticeList, {showClose: false});
        });
        loading.close();
      },
      closeNotice: function (type) {
        if (type){
          this.noticeDetail = false;
          this.listShow = true;
          this.fetch();
        }else{
          this.$emit('getNoticeCount');
          this.$emit('closeNotice');
        }
      },
      showDetail(noticeId){
        let errMsg = this.commonMessage.error.system;
        axios.post("/api/noticeAlreadyRead", {
          noticeId: noticeId
        }).then(res => {
          this.noticeId = noticeId;
          this.noticeDetail = true;
          this.listShow = false;
        }).catch(err => {
          this.$alert(errMsg, {showClose: false});
        });

      }
    },
    created() {
      this.fetch();
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
