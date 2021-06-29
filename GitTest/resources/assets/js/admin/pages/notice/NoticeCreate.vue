<template>
  <el-container class="noticeCreat">
    <el-header style="height: 122px;" class="header">
      <el-row type="flex" class="row-bg" justify="space-between">
        <el-col :span="6" class="headLeft">
          <router-link to="/notice">
            <li><img src="images/admin-img/notice_on@2x.png"></li>
            <li class="title">
              <p>お知らせ</p>
              <p>News</p>
            </li>
          </router-link>
        </el-col>
        <el-col :span="8" class="headRight">
          <UserProfile/>
        </el-col>
      </el-row>
    </el-header>

    <el-main class="container">
      <div class="part1">
        <h2 v-if="createFlag">お知らせ新規</h2>
        <h2 v-else>お知らせ編集</h2>
      </div>
      <div>
        <div>
          <el-form :model="notice" label-width="150px" ref="form" :rules="rules">
            <div class="form-group">
              <el-form-item label="タイトル" prop="title">
                <el-input v-model="notice.title" maxlength="51"></el-input>
              </el-form-item>
            </div>
            <div class="form-group lineinput">
              <el-form-item label="期間" prop="st_date">
                  <el-date-picker
                          v-model="notice.st_date"
                          :clearable="clearable"
                          type="date"
                          :picker-options="stPickerOptions"
                          value-format="yyyy-MM-dd"
                          format="yyyy / M / d">
                  </el-date-picker>
              </el-form-item>
              <p>〜</p>
              <el-form-item prop="ed_date">
                  <el-date-picker
                          v-model="notice.ed_date"
                          :clearable="clearable"
                          type="date"
                          :picker-options="edPickerOptions"
                          value-format="yyyy-MM-dd"
                          format="yyyy / M / d">
                  </el-date-picker>
              </el-form-item>
            </div>
            <div class="form-group">
              <el-form-item label="内容">
                <el-input type="textarea" :rows="4" placeholder="メッセージ" v-model="notice.content">
                </el-input>
              </el-form-item>
            </div>
          </el-form>
        </div>
        <div class="pro-button">
          <a class="back">
            <el-button @click="backToList()">戻る</el-button>
          </a>
          <a class="edit">
              <el-button v-if="createFlag" @click="create()">登録する</el-button>
              <el-button v-else @click="edit()">編集する</el-button>
            </a>
        </div>
      </div>
    </el-main>
  </el-container>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/notice'

  export default {
    components: {
      UserProfile,
    },
    props: {
      //quillEditor value
      value: {
        type: String,
        default: ''
      },
    },
    mixins: [Messages,validation],
    name: "NoticeCreate",
    data() {
      return {
        pickerOptions:{},
        stPickerOptions: {
          disabledDate: this.stDisabledDate,
        },
        edPickerOptions: {
          disabledDate: this.edDisabledDate,
        },
        notice:{
          st_date:'',
          ed_date:'',
          title:'',
          content:this.value,
        },
        createFlag:true,
        clearable: false,
      }
    },
    methods: {

      //お知らせを編集
      edit(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.update;
        axios.post('/api/editNotice', {
          id: this.$router.currentRoute.query.id,
          notice: this.notice,
        }).then((res) => {
          loading.close();
          this.$router.push({path: '/notice'});
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },

      //お知らせを新規
      create(){
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let errMessage = this.commonMessage.error.insert;
            axios.post('/api/createNotice', {
              notice: this.notice,
            }).then((res) => {
              loading.close();
              if (!res.data.result) {
                this.$router.push({path: '/notice'});
              } else {
                this.$alert(errMessage, {showClose: false});
              }
            }).catch(error => {
              loading.close();
              this.$alert(errMessage, {showClose: false});
            });
          }
        })
      },

      //編集の場合 お知らせ詳細を取得
      fetchDetail(id){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        axios.post('/api/getNoticeDetail', {
          id: id,
        }).then((res) => {
          this.notice = res.data;
          loading.close();
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
      //作業期間 date-picker:disabledDate
      stDisabledDate(time) {
        return time.getTime() > new Date(this.notice.ed_date).getTime();
      },
      edDisabledDate(time) {
        return time.getTime() < new Date(this.notice.st_date).getTime() - 1000 * 60 * 60 * 24;
      },
      backToList(){
        this.$router.push({path: '/notice'});
      }
    },
    created() {
      if (this.$router.currentRoute.name === 'NoticeEdit') {
        this.createFlag = false;
        this.fetchDetail(this.$router.currentRoute.query.id);
      }
    }
  }
</script>

