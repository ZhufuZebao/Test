<template>
  <div class="container clearfix  projectlist projectshow commonAll">
    <header>
      <h1>
        <router-link to="/project/">
          <div class="commonLogo">
            <ul>
              <li class="bold">PROJECT</li>
              <li>案件</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap project-info-top">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/project' }"><span>案件一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>案件情報：{{project.place_name}}</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <div class="showAll">
      <div class="top">
        <div class="projectImformation">
          <div class="projectimg">
            <img class="imgPaly" v-if="project.subject_image&&!project.subject_image.match('images')"
                 v-bind:src="'file/projects/'+project.subject_image">
            <img class="imgPaly" v-else src="images/no-image.png" @click="projectDetail(project)">
          </div>
          <section class="common-wrapper project_top_section">
            <!-- 案件一覧テーブル表示 -->
            <ul class="project-list clearfix">
              <div class="project-show project-list-item clearfix btns" date-tgt="wd1">
                <div class="address">
                  {{project.pref}}{{project.town}}{{project.street}}{{project.house}}
                  <!--<span class='map' v-if="project.pref || project.town || project.street || project.house"><a-->
                  <!--@click="openMapModal(project)">MAP</a></span>-->
                </div>
                <div class="customer-data-status">
                  <div class="customer">
                    <span>施主 :</span>
                    <span class="colorChange" v-if="project.customer_office" v-for="projCustomer in project.customer_office">{{projCustomer.name}}</span>
                  </div>
                  <div class="data">
                    <span>期間 :</span>
                    <span class="colorChange">
                        <span v-if="project.st_date && project.ed_date">{{project.st_date}}～{{project.ed_date}}</span>
                      </span>
                  </div>
                  <div class="progress">
                     <span><el-progress :percentage="project.progress" status="exception" :stroke-width="10"
                                        :class="borderColor(project.progress_status)"
                                        v-if="project.progress_status"></el-progress>
                    </span>
                  </div>
                  <div class="status">
                    <span>進捗状況 :</span>
                    <select class="project-status" v-model="project.progress_status" @change="saveProjectProgressStatus"
                            :class="borderColor(project.progress_status)" :disabled="!showAddMember">
                      <option v-for="progress in progressStatus" :key="progress.id"
                              :value="progress.id" :class="borderColor(progress.id)" v-if="progress">{{progress.name}}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </ul>

            <ul class="project-list">
              <button class="but" @click="projectDetail(project)">案件詳細</button>
              <span style="display:block;width:285px;height:28px;overflow:hidden;position:relative;margin-top: 20px;" class="_PPPARK_SEARCH_BUTTON_1"
                    :data-lat="dataLat" :data-lng="dataLng" v-if="dataLat && dataLng && project.pref && project.town &&project.street" :data-pref="project.pref" :data-city="project.town" :data-spot="project.street+project.house">
                <a :href="aTagUrl"
                   target=_blank style="display:block;white-space:nowrap;font-size:11px;position:absolute;bottom:0;">
                  「{{project.street}}」の周辺駐車場を検索する
                </a>
              </span>
            </ul>
          </section>
        </div>
      </div>
      <div class="bottom">
        <div class="bottom-bottom">
          <ul class="document" style="height: 48px">
            <div class="title">
              <span class="left">ドキュメント</span>
              <span class="right"><a :href="webDocUrl" target="_blank">ドキュメントを開く</a></span>
            </div>
          </ul>
          <ul class="project-managemen right" style="display: none">
            <div class="title">
              <span class="left">工程管理</span>
              <span class="right"><a>工程管理を開く</a></span>
            </div>
          </ul>
        </div>
        <div class="bottom-top">
          <ul class="groupchat">
            <div class="title">
              <span class="left">グループチャット</span>
              <router-link :to="{path:'/chat',query:{proId:projectGroupId,userId:0}}">
                <span class="right"><a>グループチャットを開く</a></span>
              </router-link>
            </div>
            <div class="groupchat-content">
              <li class="groupchat-li" v-for="groupMessage in groupMessages">
                <img class='chatperson ' :src="'file/users/'+groupMessage.user.file" style="border-radius:100%" v-if="groupMessage.user && groupMessage.user.file">
                <img class='chatperson' src="images/icon-chatperson.png" v-else>
                <div class="chatrecord">
                  <span class="chatrecord-name">
                    <span class="user-name" v-if="groupMessage.user">{{groupMessage.user.name}}&nbsp;&nbsp;</span>
                    <span class="user-enterprise" v-if="groupMessage.user && groupMessage.user.enterprise">
                     {{groupMessage.user.enterprise.name}}&nbsp;&nbsp;
                    </span>
                    <span class="user-enterprise" v-else-if="groupMessage.user && groupMessage.user.coop_enterprise">
                      {{groupMessage.user.coop_enterprise.name}}&nbsp;&nbsp;
                    </span>
                    <span class="user-enterprise" v-else></span>
                     <span class="user-enterprise-data" v-if="groupMessage.user && groupMessage.user.enterprise">
                      {{groupMessage.created_at}}
                    </span>
                    <span  class="user-enterprise-data" v-else-if="groupMessage.user && groupMessage.user.coop_enterprise">
                      {{groupMessage.created_at}}
                    </span>
                    <span class="user-enterprise-data" v-else>
                      &nbsp;&nbsp;
                      {{groupMessage.created_at}}
                    </span>
                  </span>
                  <p class="user-message" style="white-space: break-spaces;" v-html="showMessage(groupMessage)"></p>
                  <p class="text-wrapper"  v-html="showImg(groupMessage)" v-if="groupMessage.file_name"></p>

                </div>
              </li>
            </div>
          </ul>
          <ul class="peoplelist right">
            <router-link to="/project/create" class="right peoplelistimg" v-if="showAddMember">
              <img @click.prevent="openParticipantsModal" src="images/add@2x.png"/>
            </router-link>
            <div class="title">
              <span class="left">共有者リスト</span>
            </div>
            <ul class="peoplelist-contentlist">
              <li v-for="user in friendArr.enterpriseArr" :key="'enterpriseArr-'+user.id" v-if="user.id">
                <div v-if="user.enterprise">{{user.enterprise.name}}</div>
                <div v-if="user">{{user.name}}({{user.email}})</div>
                <div v-if="user.id===project.created_by || user.id===userId"></div>
                <div v-else @click.prevent="delCheckItem(user.id)">
                  <img class="img-delete" src="images/icon-del.png" alt="" v-if="">
                </div>
              </li>
              <li v-for="user in friendArr.parterArr" :key="'parterArr-'+user.id" v-if="user.id">
                <div v-if="user.enterprise">{{user.enterprise.name}}</div>
                <div v-else-if="user.enterprise_coop">{{user.enterprise_coop.name}}</div>
                <div v-else></div>
                <div v-if="user">{{user.name}}({{user.email}})</div>
                <div v-if="parterArr" @click.prevent="delCheckItem(user.id)">
                  <img class="img-delete" src="images/icon-del.png" alt="">
                </div>

              </li>
              <li v-for="user in friendArr.contactArr" :key="'contactArr-'+user.id" v-if="user.id">
                <div>職人</div>
                <div v-if="user">{{user.name}}({{user.email}})</div>
                <div v-if="contactArr" @click.prevent="delCheckItem(user.id)">
                  <img class="img-delete" src="images/icon-del.png" alt="">
                </div>

              </li>
              <li v-for="user in friendArr.otherPerson" :key="'otherPerson-'+user.id" v-if="user.id">
                <div v-if="user.enterprise">{{user.enterprise.name}}</div>
                <div v-else-if="user.enterprise_coop">{{user.enterprise_coop.name}}</div>
                <div v-else></div>
                <div v-if="user">{{user.name}}({{user.email}})</div>
                <div>&nbsp;</div>
              </li>
              <!--<li v-for="user in friendArr.otherArr" :key="'otherPerson-'+user.id" v-if="user.id">-->
                <!--<div>その他</div>-->
                <!--<div v-if="user">{{user.name}}({{user.email}})</div>-->
                <!--<div v-if="contactArr" @click.prevent="delCheckItem(user.id)">-->
                  <!--<img class="img-delete" src="images/icon-del.png" alt="">-->
                <!--</div>-->

              <!--</li>-->
            </ul>

          </ul>
        </div>
        <!-- #1802 -->
        <div class="pro-button" style="text-align:center">
          <a class="modoru" @click="backToProjectList">
            戻る
          </a>
        </div>

      </div>
    </div>
    <ProjectMapModal :project="postMap" @closeModal="closeMapModal" v-if="showMapModal"/>
    <ProjectParticipantsSelectModal :projectId="projectId" :projectGroupId="projectGroupId"
                                    @closeParticipantsModal="closeParticipantsModal"
                                    @participantSelectBack="participantSelectBack"
                                    v-if="showParticipantsModal"/>
  </div>
</template>
<script>
  import ProjectParticipantsSelectModal from '../../components/project/ProjectParticipantsSelectModal.vue'
  import ProjectDetailModal from '../../components/project/ProjectDetailModal';
  import UserProfile from "../../components/common/UserProfile";
  import ProjectList from "../../mixins/ProjectLists";
  import ProjectMapModal from '../../components/project/ProjectMapModal';
  import messages from "../../mixins/Messages";
  import { writeSearchButton } from "../../../utils/pppark";
  import formatChatMsg from '../../mixins/FormatChatMsg.js';
  import Calendar from "../../mixins/Calendar";
  importScript("https://maps.googleapis.com/maps/api/js?key=" + process.env.MIX_GOOGLE_MAP_KEY);

  export function importScript (path, success, error) {
    var oS = document.createElement('script')
    oS.src = path
    document.getElementsByTagName('head')[0].appendChild(oS)
    oS.onload = function () {
      success && success()
    }

    oS.onerror = function () {
      error && error()
    }
  }

  export default {
    name: "ProjectShow",
    mixins: [
      ProjectList,
      messages,
    ],
    components: {
      ProjectDetailModal,
      UserProfile,
      formatChatMsg,
      ProjectParticipantsSelectModal,
      ProjectMapModal
    },
    data() {
      return {
        userId:'',
        showAddMember: false,
        friendArr: {
          enterpriseArr: [],
          contactArr: [],
          parterArr: [],
          otherPerson:[],
          // otherArr:[]
        },
        enterpriseArr: [],
        contactArr: [],
        parterArr: [],
        fileType: '',
        chatFileImgSrc: 'images/no-image.png',
        projectId: null,
        showMapModal: false,//詳細モーダル表示フラグ
        projectGroupId: null,
        groupMessages: [],
        participantsArr: [],
        participantsTmp: null,
        showParticipantsModal: false,
        src: '',
        showManageModal: false,//詳細モーダル表示フラグ
        project: {
          customer: {},
          customer_office: {},
          place_name: '',
          progress_status: '',
          list: [],
        },
        dataLat:'',
        dataLng:'',
        aTagUrl:'',
      }
    },
    computed: {
      webDocUrl: function () {
        return process.env.MIX_DOC_URL + this.project.id;
      }
    },
    methods: {
      backToProjectList(){
        this.$router.push({
          path: '/project'
        });
      },
      showImg(chatItem){
        let msg = '';
        if (chatItem.file_name){
          let file_name = chatItem.file_name.substring(chatItem.file_name.lastIndexOf('/') + 1, chatItem.file_name.length);
          msg= formatChatMsg.formatImg(file_name,chatItem.group_id);
        }
        return this.emoji(msg);
      },
      showMessage(chatItem) {
        if (chatItem.message) {
          chatItem.message = chatItem.message.replace(/\[icon:101\]/g, `<img src="./images/chat/101.png" width="16px" height="16px">`);
          chatItem.message = chatItem.message.replace(/\[icon:102\]/g, `<img src="./images/chat/102.png" width="16px" height="16px">`);
          if (chatItem.message.indexOf('[time:') !== -1) {
            let msg = formatChatMsg.quote(chatItem.message,chatItem.file_name,chatItem.group_id);
            return this.emoji(msg);
          } else {
            let msg = null;
            if (chatItem.message.indexOf('[To:') !== -1) {
              chatItem.message = formatChatMsg.formatToMsg(chatItem.message,chatItem.group_id);
            }
            if (chatItem.message.indexOf('[mid:') !== -1) {
              msg = formatChatMsg.formatReMsg(chatItem.message,chatItem.group_id);
              return this.emoji(msg);
            } else {
              return this.emoji(chatItem.message);
            }
          }
        }
      },
      //案件にの進捗状況変更
      saveProjectProgressStatus: function () {
        let errMsg = this.commonMessage.error.projectEdit;
        axios.post('/api/updateProjectProgressStatus', {
          projectId: this.$route.params.id,
          progress_status: this.project.progress_status
        }).then((res) => {
        }).catch(error => {
          this.$alert(errMsg);
        });
      },
      //場所モデルを開ける
      openMapModal(project) {
        this.postMap = project;
        this.showMapModal = true;
      },
      //場所モデルを閉め
      closeMapModal() {
        this.showMapModal = false;
      },

      addZero(obj) {
        if(obj<10) {
          return "0" +""+ obj;
        } else {
          return obj;
        }
      },

      //グループチャットにチャットメッセージを取得
      getChatMessage(groupId) {
        let errMsg = this.commonMessage.error.projectChatMessage;
        axios.post("/api/getChatMassage", {
          groupId: this.project.group_id,
        }).then(res => {
          //時間の時分を取得
          let groupMesData = [];
          if (res.data) {
            for (let i = 0; i < res.data.length; i++) {
              let groupMes = res.data[i];
              if (groupMes){
                // let myDate = new Date(groupMes.created_at.replace(/-/g, "/"));
                // let hours = myDate.getHours();
                // let minutes = this.addZero(myDate.getMinutes());
                groupMes.created_at = Calendar.dateFormat(res.data[i].created_at,'yyyy-MM-dd hh:mm');
                if (groupMes.file_name){
                  this.fileType = groupMes.file_name.split('.')[1];
                  groupMes.file_name_play = groupMes.file_name.substr(14, groupMes.file_name.length - 1);
                } else{
                  this.fileType = '';
                  groupMes.file_name_play = '';
                }
                if (this.fileType === "png" || this.fileType === "jpg") {
                  groupMes.fileSrc = "file/" + groupMes.group_id + '/' + groupMes.file_name;
                } else {
                  groupMes.fileSrc = '';
                }
              }
              groupMesData.push(groupMes);
            }
            this.groupMessages = groupMesData;
            // スクロール
            //＃2559【案件】チャットの位置が最新の場所を表示
            // $(document).ready(function(){
            //   let elTabs = document.getElementsByClassName("groupchat-content");
            //   document.getElementsByClassName("groupchat-content")[0].scrollTop = document.getElementsByClassName("groupchat-content")[0].scrollHeight;
            // });
          }
        }).catch(error => {
          this.$alert(errMsg, {showClose: false});
        });
      },
      //共有者リストを削除
      delCheckItem(id) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/delProjectParticipant", {
          projectId: this.projectId,
          projectGroupId: this.projectGroupId,
          userId: id
        }).then(res => {
          for (let i = 0; i < this.friendArr.enterpriseArr.length; i++) {
            if (this.friendArr.enterpriseArr[i].id === id) {
              this.friendArr.enterpriseArr.splice(i, 1);
            }
          }
          for (let i = 0; i < this.friendArr.contactArr.length; i++) {
            if (this.friendArr.contactArr[i].id === id) {
              this.friendArr.contactArr.splice(i, 1);
            }
          }
          // for (let i = 0; i < this.friendArr.otherArr.length; i++) {
          //     if (this.friendArr.otherArr[i].id === id) {
          //         this.friendArr.otherArr.splice(i, 1);
          //     }
          // }
          for (let i = 0; i < this.friendArr.parterArr.length; i++) {
            if (this.friendArr.parterArr[i].id === id) {
              this.friendArr.parterArr.splice(i, 1);
            }
          }
        }).catch(error => {
          loading.close();
        });
        loading.close();
      },
      //共有者リストを表示する
      participantSelect: function () {
        let errMsg = this.commonMessage.error.participantsList;
        axios.post('/api/getProjectParticipants', {
          projectId: this.projectId,
          groupId: this.projectGroupId
        }).then((res) => {
          this.friendArr.enterpriseArr = res.data.enterpriseArr;
          this.friendArr.parterArr = res.data.participantsArr;
          this.friendArr.contactArr = res.data.contactArr;
          this.friendArr.otherPerson = res.data.otherPerson;
          // this.friendArr.otherArr = res.data.otherArr;
          this.userId=res.data.userId;
            if (res.data.addMember){
              this.showAddMember = true;
            }
        }).catch(error => {
          this.$alert(errMsg, {showClose: false});
        });
      },
      //「関係者を追加」モデルを登録後
      participantSelectBack: function () {
        this.showParticipantsModal = false;
        this.showProjects();
      },
      //「関係者を追加」モデルを開ける
      openParticipantsModal: function () {
        this.participantsTmp = null;
        this.showParticipantsModal = true;
      },
      //「関係者を追加」モデルを閉め
      closeParticipantsModal: function () {
        this.showParticipantsModal = false;
      },
      //進度文字の色をセット
      color: function (projectProgressStatus) {
        return {
          progressStatus1: projectProgressStatus === '1' ||
              projectProgressStatus === '2' ||
              projectProgressStatus === '3' ||
              projectProgressStatus === '4' ||
              projectProgressStatus === '7',
          progressStatus2: projectProgressStatus === '5' ||
              projectProgressStatus === '6',
          progressStatus3: projectProgressStatus === '9'
        }
      },
      //進度条の色をセット
      borderColor: function (projectProgressStatus) {
        return {
          progressBorderColor1: projectProgressStatus === '1' ||
              projectProgressStatus === '2' ||
              projectProgressStatus === '3' ||
              projectProgressStatus === '4' ||
              projectProgressStatus === '7' ||
              projectProgressStatus === '8',
          progressBorderColor2: projectProgressStatus === '5' ||
              projectProgressStatus === '6',
          progressBorderColor3: projectProgressStatus === '9'
        }
      },
      //案件の情報を表示
      showProjects: function () {
        let errMessage = this.commonMessage.error.projectShow;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getProjectAndCustomer", {
          params: {
            id: this.$route.params.id
          }
        }).then(res => {
          this.formatArray(res.data);
          this.getChatMessage(res.data.group_id);
          this.participantSelect();
          this.getLatLon(this.project.pref + this.project.town + this.project.street + this.project.house);
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
        loading.close();
      },
      //案件データを処理
      formatArray(res) {
        this.project = res;
        this.projectId = res.id;
        this.projectGroupId = res.group_id;
        //施工期間の日付格式を修正
        if (res.st_date) {
          this.project.st_date = (new Date(this.project.st_date)).getFullYear() + "/" +
              ((new Date(this.project.st_date)).getMonth() + 1) + "/" +
              (new Date(this.project.st_date)).getDate();
        }
        if (res.ed_date) {
          this.project.ed_date = (new Date(this.project.ed_date)).getFullYear() + "/" +
              ((new Date(this.project.ed_date)).getMonth() + 1) + "/" +
              (new Date(this.project.ed_date)).getDate();
        }
        //施工期間によって、現時点がどのくらい位置を表示する
        let $pro_course = ((new Date(this.project.ed_date)) - (new Date(this.project.st_date)));
        let $now_course = ((new Date()) - (new Date(this.project.st_date)));
        this.project.progress = $now_course / $pro_course;
        if ($now_course > $pro_course) {
          this.project.progress = 100
        } else if ($now_course <= 0) {
          this.project.progress = 0
        } else if ($pro_course <= 0) {
          this.project.progress = 100
        } else {
          this.project.progress = $now_course / $pro_course * 100
        }
        if (!this.project.customer) {
          this.project.customer = {};
        }

        if (!this.project.customer_office) {
          this.project.customer_office = {};
        }
      },
      //案件詳細ページを移動
      projectDetail(project) {
        this.$router.push({path: '/project/details/' + project.id});
      },
      //pppark
      getButtonContent:function(){
        writeSearchButton();
      },
      sleep(time) {
        return new Promise((resolve) => setTimeout(resolve, time));
      },
      //経緯度を取得
      getLatLon(location) {
        if (!location) {
          //住所なしの場合
          return;
        }
        this.sleep(2000).then(() => {
          let geocoder = new google.maps.Geocoder();
          let $this = this;
          geocoder.geocode({'address': location}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              //駐車場 lat lng を取得
              $this.dataLat = results[0].geometry.location.lat();
              $this.dataLng = results[0].geometry.location.lng();
            } else {
              console.warn("Geocode was not successful for the following reason: " + status);
            }
          });
        });
      },
    },
    created() {
      this.showProjects();
    },
    updated() {
      this.getButtonContent();
    }
  }
</script>
