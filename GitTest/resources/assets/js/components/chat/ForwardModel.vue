<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show chatlist-task chat-forward">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': '-200px','margin-top': modalTop}">
        <div class="modal-close" @click.prevent="closeForward">×</div>
        <div class="modalBodycontent">
          <h3>転送先を選択</h3>
          <div>
            <div class="ChatContactPage">
                <div>
                  <el-row>
                    <el-col :span="24">
                      <el-menu :unique-opened="true">
                        <el-menu-item v-if="g.id !== chatGroupNow"
                                      @click="handleOpen(g.id,g.name)"
                                      :index="g.id+''" v-for="(g,index) in group" :key="'group-'+g.id+index"  class="chat-group">
                          <img :src="'file/groups/'+g.file" alt="" class="pro-photo" v-if="g.file">
                          <img src="images/icon-chatgroup.png" alt="" class="pro-photo" v-else>
                          <span class="chat-group-name" v-if="g.name">{{g.name}}</span>
                        </el-menu-item>
                        <el-menu-item v-if="g.parent_id && g.id !== chatGroupNow"
                                      @click="handleOpen(g.id,g.name)"
                                      :index="g.id+''" v-for="(g,index) in child" :key="'group-'+g.id+index"  class="chat-group">
                          <img :src="'file/groups/'+g.file" alt="" class="pro-photo" v-if="g.file">
                          <img src="images/icon-chatgroup.png" alt="" class="pro-photo" v-else>
                          <span class="chat-group-name" v-if="g.name">{{g.name}} ({{g.mine.name}})</span>
                        </el-menu-item>
                        <el-menu-item v-for="(p,index) in person"
                                      @click="handleOpen(p.pivot.group_id,p.name)"
                                      :key="'person-'+p.name+index" :index="p.pivot.group_id+''"
                                      v-if="p.pivot.group_id !== chatGroupNow"  class="chat-group">
                          <img :src="'file/users/'+p.file" alt="" class="pro-photo" v-if="p.file" >
                          <img src="images/icon-chatperson.png" alt="" class="pro-photo" v-else>
                          <span class="chat-group-name" v-if="p.name">{{p.name}}</span>
                        </el-menu-item>
                      </el-menu>
                    </el-col>
                  </el-row>
                </div>
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

  export default {
    name: 'ForwardModel',
    props:['chatGroupNow'],
    data: function () {
      return {
        tabPosition: 'left',
        group: {},
        person: [],
        child: [],
        mineId: ''
      }
    },
    methods: {
      handleOpen(id,name) {
        this.$emit('closeForwardModel',id,name);
      },
      fetchPersonList: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getPersonList').then((res) => {
          this.mineId = res.data.mineId;
          this.person = res.data.person;
          this.group = res.data.group;

          let childModel = [];
          for (let i = 0; i < this.group.length; i++) {
            if (this.group[i].child){
              childModel.push(this.group[i].child);
            }
          }

          //案件childグループ dataを処理
          for (let i = 0; i < childModel.length; i++) {
            if (childModel[i]){
              for (let j = 0; j < childModel[i].length; j++) {
                this.child.push(childModel[i][j]);
              }
            }
          }
        }).catch(error => {
          loading.close();
        });
        loading.close();
      },
      closeForward() {
        this.$emit('closeForwardModel',0,'');
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
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
      this.fetchPersonList();
    },
    watch: {}
  }
</script>
