<template>
  <el-container class="noticeList">
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
        <li>
          <h2>お知らせ履歴一覧</h2>
          <router-link to="/notice/create"><el-button>新規お知らせ</el-button></router-link>
        </li>
        <li>
          <el-input placeholder="キーワード検索" v-model="noticeInput"   prefix-icon="searchForm-submit" ></el-input>
         <el-button @click="search()">検索</el-button>
        </li>
      </div>

      <div>
        <ul class="tableHeader">
          <li>
            お知らせ期間
          </li>
          <li>
            タイトル
          </li>
          <li>
            内容
          </li>
          <li>
            操作
          </li>
        </ul>
        <ul class="tablecontent">
          <li class="btns" date-tgt="wd1" v-for="item in notice" :key="'notice-'+item.id" v-if="item">
            <span  v-if="item.st_date && item.ed_date">{{ dateFormat(item.st_date) }} - {{ dateFormat(item.ed_date) }}</span>
            <span  v-else></span>
            <span>{{item.title}}</span>
            <span>{{item.content}}</span>
            <span>
              <el-button @click="openDetail(item.id)" target="_blank">
                詳細
            </el-button>
            </span>
          </li>
        </ul>
      </div>
      <div class="pagination-center">
        <el-pagination
                prev-text="◀"
                next-text="▶"
                background
                layout="prev, pager, next"
                @current-change="changePage" :page="currentPage" :page-size="perPage"
                :total="total">
        </el-pagination>
      </div>
    </el-main>
  </el-container>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import Calendar from '../../../mixins/Calendar'

  export default {
    components: {
      UserProfile,
    },
    mixins: [Messages,Calendar],
    name: "NoticeList",
    data() {
      return {
        current: 1,
        perPage: 10,           //一ページの件数
        currentPage: 1,        //現在ページ
        total: 1,               //全件数
        from: 0,                //表示件数FROM
        to: 0,                  //表示件数TO
        notice:{},
        noticeInput:''
      }
    },
    methods: {
      //ページを切り替える
      changePage: function (page) {
        this.currentPage = page;
        if (this.noticeInput){
          this.search();
        }else{
          this.fetchNotice();
        }
      },
      fetchNotice() {
        let errMessage = this.commonMessage.error.system;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getNoticeList',{
          page:this.currentPage,
          type:'1'
        }).then((res) => {
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
          this.notice = res.data.data;
          loading.close();
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },

      //edit
      openDetail(id){
        this.$router.push({path: '/notice/edit', query: {id: id}});
      },

      //date format
      dateFormat(date){
        let res = '';
        //dateをフォーマット
        let dateAfterFormat = Calendar.dateFormat(date, 'yyyy/M/d');
        //曜日を取得
        let week = Calendar.getDayWeekName(date);
        res = dateAfterFormat + "（" + week +"）";
        return res;
      },

      //キーワード検索
      search(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        axios.post('/api/searchNotice', {
          keyword: this.noticeInput,
          page:this.currentPage,
        }).then((res) => {
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
          this.notice = res.data.data;
          loading.close();
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
    },
    created(){
      this.fetchNotice();
    }
  }
</script>
