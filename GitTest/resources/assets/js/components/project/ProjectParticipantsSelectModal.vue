<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show schedule-modal projectparticipantsSelect-model">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="close">×</div>
        <div class="modalBodycontent">
          <h3>共有者を追加</h3>
          <div class="schedule-modalBodycontent clearfix">
            <dl class="clearfix" style="background-color: #FFFFFF;">
              <el-tabs type="border-card" class="popoverto-tabs">
                <el-tab-pane label="社内">
                  <el-checkbox-group v-model="enterpriseArr">
                    <li v-for="user in friendArr.enterpriseArr" :key="'enterpriseArr-'+user.id" v-if="user.id">
                      <el-checkbox :label="user.id">
                        <span v-if="user.enterprise && user.enterprise.name">{{user.enterprise.name}}</span>
                        <span v-if="user.name">{{user.name}}さん</span>
                        <span v-if="user.email">({{user.email}})</span>
                      </el-checkbox>
                    </li>
                  </el-checkbox-group>
                </el-tab-pane>
                <el-tab-pane label="協力会社">
                  <el-checkbox-group v-model="parterArr">
                    <ul>
                      <li v-for="user in friendArr.parterArr" :key="'parterArr-'+user.id" v-if="user.id">
                        <el-checkbox :label="user.id">
                          <span v-if="user.enterprise && user.enterprise.name">{{user.enterprise.name}}</span>
                          <span v-if="user.name">{{user.name}}さん</span>
                          <span v-if="user.email">({{user.email}})</span>
                        </el-checkbox>
                      </li>
                    </ul>
                  </el-checkbox-group>
                </el-tab-pane>
                <el-tab-pane label="職人">
                  <el-checkbox-group v-model="contactArr">
                    <ul>
                      <li v-for="user in friendArr.contactArr" :key="'contactArr-'+user.id" v-if="user.id">
                        <el-checkbox :label="user.id">
                          <span v-if="user.enterprise && user.enterprise.name">{{user.enterprise.name}}</span>
                          <span v-if="user.name">{{user.name}}さん</span>
                          <span v-if="user.email">({{user.email}})</span>
                        </el-checkbox>
                      </li>
                    </ul>
                  </el-checkbox-group>
                </el-tab-pane>
              </el-tabs>

              <el-input class="popoverto-input" placeholder="名前で検索" v-model="searchName"
                        suffix-icon="searchForm-submit" @change="fetchParticipants()">
              </el-input>

            </dl>
            <div class="clearfix"></div>
            <div class="button-wrap clearfix">
              <div class="button-lower remark"><a href="javascript:void(0)" @click="add">登録</a></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import ScheduleLists from '../../mixins/ScheduleLists'
  import validation from '../../validations/schedule.js'
  import Messages from "../../mixins/Messages";

  export default {
    name: "ProjectParticipantsSelectModal",
    mixins: [
      validation,
      ScheduleLists,
      Messages,
    ],
    props: {
      projectId: null,
      projectGroupId: null
    },
    data: function () {
      return {
        searchName: '',
        isMounted: false,
        words: '',
        checkArr: [],
        projectParticipants: [],
        friendArr: {
          enterpriseArr: [],
          contactArr: [],
          parterArr: []
        },
        enterpriseArr: [],
        contactArr: [],
        parterArr: []
      }
    },
    methods: {
      fetchParticipants: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getParticipants', {
            words: this.searchName,
            projectId: this.projectId
        }).then((res) => {
          this.friendArr.enterpriseArr = res.data.enterpriseArr;
          this.friendArr.contactArr = res.data.contactArr;
          this.friendArr.parterArr = res.data.participantsArr;
        }).catch(err => {
        });

        loading.close();
      },
      choose(index) {
        let checkArrIndex = -1;
        checkArrIndex = this.getCheckArrIndex(this.projectParticipants[index]);
        if (checkArrIndex !== -1) {
          this.projectParticipants[index].isChecked = false;
          this.checkArr.splice(checkArrIndex, 1);
          return;
        }
        this.projectParticipants[index].isChecked = true;
        this.checkArr.push(this.projectParticipants[index]);
      },
      close() {
        this.$emit("closeParticipantsModal");
      },
      add() {
        let errMsg = this.commonMessage.error.participantsAdd;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let resArr = this.parterArr.concat(this.enterpriseArr,this.contactArr);
        let participants = JSON.stringify(resArr);
        axios.post('/api/insertProjectParticipants', {
          participants: participants,
          projectId: this.projectId,
          projectGroupId: this.projectGroupId
        }).then((res) => {
            this.$emit('participantSelectBack');
        }).catch(error => {
          this.$alert(errMsg, {showClose: false});
          loading.close();
        });
        loading.close();
      },
      getCheckArrIndex(item) {
        let checkArrLen = this.checkArr.length;
        if (checkArrLen !== 0) {
          for (let i = 0; i < checkArrLen; i++) {
            if (item.id === this.checkArr[i].id) {
              return i;
            }
          }
        }
        return -1;
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
      this.fetchParticipants();
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }

  .selectedColor {
    color: #219894;
  }
</style>
