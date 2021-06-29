<template>
  <div class="container clearfix document customerlist commonAll">
    <header>
      <h1>
        <router-link to="/document">
          <div class="commonLogo">
            <ul>
              <li class="bold">DOCUMENT</li>
              <li>ドキュメント</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>ドキュメント一覧</h2>
      </div>
      <UserProfile/>
    </header>
    <section class="customer-wrapper document">
      <!-- ページネーション -->
      <div>
        <span>{{enterprise.name}}のドキュメント</span>
          <a :href="createDocUrl()" target="_blank">
          <span class="document-creat">
            <img src="images/document-file.png" alt="">
            <span>会社ドキュメントを開く</span>
          </span>
          </a>
      </div>

      <div class="documentable">
        <ul class="report-serch clearfix">
          <li class="customer-li">
            案件名
          </li>
          <li class="customer-li" style="width: 20%">
            ドキュメント管理
          </li>
        </ul>
        <ul class="table-scroll">
          <li class="clearfix btns" date-tgt="wd1" v-for="d in doc" :key="'doc-'+d.id" v-if="d">
            <span class="customer-li" v-if="d.place_name">{{d.place_name}}</span>
            <span class="customer-li document-creat">
              <a :href="webDocUrl(d.id)" target="_blank">
                <img src="images/document-file.png" alt="">
                案件ドキュメントを開く
              </a>
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
    </section>

  </div>
</template>

<script>
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";

  export default {
    components: {
      Pagination,
      UserProfile,
    },
    data() {
      return {
        current: 1,
        perPage: 10,           //一ページの件数
        currentPage: 1,        //現在ページ
        total: 1,               //全件数
        from: 0,                //表示件数FROM
        to: 0,                  //表示件数TO
        doc: [],
        enterprise: {},
      }
    },
    methods: {
      //ページを切り替える
      changePage: function (page) {
        this.currentPage = page;
        document.body.scrollTop =  0;
        document.documentElement.scrollTop = 0;
        window.pageYOffset = 0;
        this.fetch();
      },
      createDocUrl:function(){
        return process.env.MIX_DOC_URL + 'enterprise/internal'
      },
      webDocUrl: function (pId) {
        return process.env.MIX_DOC_URL + pId;
      },
      fetch: function () {
        let errMessage = this.commonMessage.error.system;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getDocumentList',{
          page:this.currentPage
        }).then((res) => {
          this.doc = res.data.pro.data;
          this.enterprise = res.data.enterprise[0];
          this.currentPage = res.data.pro.current_page;
          this.total = res.data.pro.total;
          this.from = res.data.pro.from ? res.data.from : 0;
          this.to = res.data.pro.to ? res.data.to : 0
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
        loading.close();
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj === ""
      },
    },
    created() {
      alert('asdf22222')
      this.fetch();
    },
    watch: {}
  }
</script>
