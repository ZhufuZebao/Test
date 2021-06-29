<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show schedule-modal">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="close">×</div>
        <div class="modalBodycontent">
          <h3>登録先追加</h3>
          <div class="schedule-modalBodycontent clearfix">
            <dl class="clearfix" style="background-color: #FFFFFF;">

              <el-tabs type="border-card" class="popoverto-tabs">
                <el-tab-pane label="社内">
                  <div v-for="(user,index) in enterprise" :key="'enterprise'+index">
                    <label>
                      <input style="display: inline;vertical-align: middle;" type="checkbox"
                             @click.self="choose(1,index)" :checked="user.isChecked">
                      {{user.name}}
                    </label>
                  </div>
                </el-tab-pane>
                <el-tab-pane label="協力会社">
                  <div v-for="(user,index) in participants" :key="'participants'+index">
                    <label>
                      <input style="display: inline;vertical-align: middle;" type="checkbox"
                             @click.self="choose(2,index)" :checked="user.isChecked">
                      {{user.name}}
                    </label>
                  </div>
                </el-tab-pane>
                <el-tab-pane label="職人">
                  <div v-for="(user,index) in friends" :key="'friends'+index">
                    <label>
                      <input style="display: inline;vertical-align: middle;" type="checkbox"
                             @click.self="choose(3,index)" :checked="user.isChecked">
                      {{user.name}}
                    </label>
                  </div>
                </el-tab-pane>

              </el-tabs>

             <el-input class="popoverto-input" placeholder="名前で検索" v-model="words"
                        suffix-icon="searchForm-submit" @change="fetchSearchPerson()">
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
    name: "ScheduleParticipantsSelectModal",
    mixins: [
      validation,
      ScheduleLists,
      Messages,
    ],
    props: ['participantsCheckArr'],
    data: function () {
      return {
        isMounted: false,
        words: '',
        checkArr: [],
        enterprise: [],
        participants: [],
        friends: [],
      }
    },
    methods: {
      fetchSearchPerson: function () {
        let errMessage = this.commonMessage.error.scheduleSelectAccount;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/fetchChatUserList', {
          params: {
            words: this.words,
          }
        }).then((res) => {
          this.enterprise = res.data.enterprise;
          for (let i = 0; i < this.enterprise.length; i++) {
            let checkArrIndex = this.getCheckArrIndex(this.enterprise[i]);
            if (checkArrIndex !== -1) {
              this.$set(this.enterprise[i], 'isChecked', true);
            } else {
              this.$set(this.enterprise[i], 'isChecked', false);
            }
          }
          this.participants = res.data.participants;
          for (let i = 0; i < this.participants.length; i++) {
            let checkArrIndex = this.getCheckArrIndex(this.participants[i]);
            if (checkArrIndex !== -1) {
              this.$set(this.participants[i], 'isChecked', true);
            } else {
              this.$set(this.participants[i], 'isChecked', false);
            }
          }
          this.friends = res.data.friends;
          for (let i = 0; i < this.friends.length; i++) {
            let checkArrIndex = this.getCheckArrIndex(this.friends[i]);
            if (checkArrIndex !== -1) {
              this.$set(this.friends[i], 'isChecked', true);
            } else {
              this.$set(this.friends[i], 'isChecked', false);
            }
          }
          loading.close();
        }).catch(err => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        })
      },
      choose(type,index) {
        let checkArrIndex = -1;
        if(type === 1){
          checkArrIndex = this.getCheckArrIndex(this.enterprise[index]);
        }else if(type === 2){
          checkArrIndex = this.getCheckArrIndex(this.participants[index]);
        }else if(type === 3){
          checkArrIndex = this.getCheckArrIndex(this.friends[index]);
        }
        if (checkArrIndex !== -1) {
          if(type === 1){
            this.enterprise[index].isChecked = false;
          }else if(type === 2){
            this.participants[index].isChecked = false;
          }else if(type === 3){
            this.friends[index].isChecked = false;
          }
          this.checkArr.splice(checkArrIndex, 1);
          return;
        }
        if(type === 1){
          this.enterprise[index].isChecked = true;
          this.checkArr.push(this.enterprise[index]);
        }else if(type === 2){
          this.participants[index].isChecked = true;
          this.checkArr.push(this.participants[index]);
        }else if(type === 3){
          this.friends[index].isChecked = true;
          this.checkArr.push(this.friends[index]);
        }
      },
      close() {
        this.$emit('participantSelectBack');
      },
      add() {
        this.$emit('participantSelectBack', this.checkArr);
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
      this.checkArr = JSON.parse(JSON.stringify(this.participantsCheckArr));
      this.fetchSearchPerson();
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>