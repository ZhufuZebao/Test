<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modalBodycontent">
          <div class="dashboardurl pro-button">
            <el-button class="button-lower remark" @click="openUrl" >ドキュメント管理画面に移行します</el-button>
            <el-button class="modoru" @click="close">キャンセル</el-button>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
    import messages from "../../mixins/Messages";

    export default {
        mixins: [
            messages,
        ],
        name: 'DashboardUrl',
        props: ['id'],
        data: function () {
            return {
              isMounted: false,
            }
        },
        methods: {
            close() {
                this.$emit('DashboardUrlBack');
            },
            openUrl(){
                let id=this.id;
                if(id > 0) {
                    window.open(process.env.MIX_DOC_URL + id);
                } else{
                    window.open(process.env.MIX_DOC_URL + 'enterprise/internal');
                }
                this.$emit('DashboardUrlBack');
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
      },
    }
</script>
<style scoped>
  .modal-show {
    display: block;
  }
</style>