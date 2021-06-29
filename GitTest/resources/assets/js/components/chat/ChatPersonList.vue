<template>
  <transition name="fade">
    <div class="modal wd1 modal-show commonAll project-customer project-enterprise chatpersonList">
      <div class="modalBody modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': '-375px','margin-top': modalTop}" >
        <div class="modal-close" @click="closeModel">×</div>
        <div class="modalBodycontent commonMol" style="overflow-y: auto">
          <div class="common-view">
            <div class="content">

              <table class="detail-table" v-if="member.kind">
                <tr>
                  <td class="tdLeft">名前</td>
                  <td class="tdRight">{{ member.name }}</td>
                </tr>
                <tr>
                  <td class="tdLeft">メールアドレス</td>
                  <td class="tdRight" v-if="groups.users && groups.users[0]">{{ groups.users[0].email}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">電話番号</td>
                  <td class="tdRight">
                    <span>
                      <span v-if="groups.users && groups.users[0]
                      && groups.users[0].enterprise">{{groups.users[0].enterprise.tel}}</span>
                      <span v-else-if="groups.users && groups.users[0]
                      && groups.users[0].enterprise_coop">{{groups.users[0].enterprise_coop.tel}}</span>
                      <span v-else-if="groups.users && groups.users[0]">{{groups.users[0].telno1}}</span>
                      <span v-else></span>
                    </span>
                  </td>
                </tr>
              </table>
              <table class="detail-table" style="min-width: 640px; margin: 0;    max-height: calc( 80vh - 100px);overflow-y: auto;" v-else>
                <tr>
                  <td class="tdLeft">グループ名</td>
                  <td class="tdRight">{{ member.name }}</td>
                </tr>
                <tr>
                  <td class="tdLeft">親グループ名</td>
                  <td class="tdRight" v-if="groupId !== member.parentId && member.parentId">{{ member.parentName }}</td>
                  <td class="tdRight" v-else></td>
                </tr>
                <tr>
                  <td class="tdLeft">管理者</td>
                  <td class="tdRight">
                    <span v-for="u in groups.users" v-if="u && u.pivot.admin === 1">{{u.name}}
                      <span v-if="u.enterprise">（{{u.enterprise.name}}）</span>
                      <span v-else-if="u.enterprise_coop">（{{u.enterprise_coop.name}}）</span>
                      <span v-else></span>
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">メンバー</td>
                  <td class="tdRight">
                    <span v-for="u in groups.users" v-if="u">{{u.name}}
                      <span v-if="u.enterprise">（{{u.enterprise.name}}）</span>
                      <span v-else-if="u.enterprise_coop">（{{u.enterprise_coop.name}}）</span>
                      <span v-else></span>
                    </span>
                  </td>
                </tr>
              </table >
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>

<script>
  export default {
    name: "ChatPersonList",
    props: ['member','groupId'],
    data: function () {
      return {
        tabPosition: 'left',
        groups: [],
      }
    },
    methods: {
      fetchDetail(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getGroupDetailUser', {
            id: this.groupId,
            kind:this.member.kind,
        }).then((res) => {
          if (res.data.result === 0) {
            this.groups = res.data.params.group;
          }
          loading.close()
        }).catch(err => {
          loading.close()
          console.warn(err);
        });
      },
      closeModel() {
        this.$emit('closePersonDetailModel');
      },
      handleOpen() {
        this.$emit('closePersonDetailModel');
      },
    },
    mounted() {
      this.isMounted = true;
      this.fetchDetail();
    },
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

