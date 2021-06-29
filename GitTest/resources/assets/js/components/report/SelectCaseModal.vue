<template>
  <div class="container">
    <header>
      <div class="title-wrap">
        <h2 style="width:20%;">簡易レポート一覧</h2>
        <tr style="width:80%;float: right">
          <td class="button-s" style="float: left">
            <router-link to="/account/create">
              追加
            </router-link>
          </td>
        </tr>
      </div>
    </header>
    <section class="report-wrapper" style="margin-top: 20px">
      <ul class="report-serch clearfix">
        <li class="account-li">

        </li>
        <li class="account-li">
          案件
        </li>
        <li class="account-li">
          アカウントメールアドレス
        </li>
      </ul>
      <ul v-for="account in accounts" v-bind:key="account.id">
        <li class="clearfix btns">
          <span class="account-li">{{account.name}}</span>
          <span class="account-li" v-if="account.worker==='1'">職人</span>
          <span class="account-li" v-if="account.enterprise_admin==='0'">一般アカウント</span>
          <span class="account-li" v-if="account.enterprise_admin==='1'">管理アカウント</span>
          <span class="account-li">{{account.email}}</span>
          <span class="account-li">******</span>
          <span class="account-li" v-if="account.auth_id === account.id"></span>
          <span class="account-li" v-else-if="account.union_id === account.id"></span>
          <span class="account-li" v-else href="javascript:void(0)" @click.prevent="deletes(account.id)">
            削除
          </span>
        </li>
      </ul>
      <tr>
        <td>
          <Pagination :page-current="currentPage" :page-size="perPage" :total="total"
                      @change="changePage">
          </Pagination>
        </td>
        <td>
          <div class="countDiv">{{total}} 件中 {{from}} 〜 {{to}} 件表示</div>
        </td>
      </tr>
    </section>
  </div>
</template>
<script>
  import AccountSearchModal from '../../components/account/AccountSearchModal';
  import UserProfile from "../../components/common/UserProfile";
  import Pagination from "../../components/common/Pagination";
  import Messages from "../../mixins/Messages";

  export default {
    components: {
      Pagination,
      UserProfile,
      AccountSearchModal
    },
    mixins: [Messages],
    data() {
      return {
        accounts: {},          // アカウントデータ
        currentPage: 1,       // 現在ページ
        perPage: 5,            // 一ページの件数
        total: 1,              // 全件数
        searchWord: "",       // 検索文字列
        from: 0,
        to: 0,
      }
    },
    methods: {
      changePage: function (page) {
        this.currentPage = page;
        // アカウントデータ
        this.fetchAccounts();
      },
      // アカウント一覧
      fetchAccounts: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.searchWord = "";
        let data = {params: {page: this.currentPage}};
        axios.get('/api/getAccountList', data).then((res) => {
          this.accounts = res.data.data;
          this.currentPage = res.data.current_page;
          this.perPage = res.data.data.per_page;
          this.total = res.data.total;
          this.from = res.data.from;
          this.to = res.data.to;
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.system, {showClose: false});
        });
        loading.close();
      },
      // アカウント検索
      searchAccounts(searchWord) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getAccountSearch?q=' + searchWord).then((res) => {
          this.accounts = res.data.data;
          this.current_page = res.data.current_page;
          this.last_page = res.data.last_page;
          this.total = res.data.total;
          this.from = res.data.from;
          this.to = res.data.to
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.system, {showClose: false});
        });
        loading.close();
      },
      // アカウント削除
      deletes: function (id) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
          let message = this.commonMessage.success.delete;
          let errMessage = this.commonMessage.error.delete;
          axios.post("/api/deleteAccount", {
            id: id,
          }).then(res => {
            loading.close();
            location.reload();
          }).catch(error => {
            loading.close();
            this.$alert(errMessage, {showClose: false});
          });
          this.$alert(message, {showClose: false, showConfirmButton: false});
        }).catch(action => {
          loading.close();
        });
      },
      change(page, searchWord) {
        if (page >= 1 && page <= this.last_page) this.load(page, searchWord)
      },
    },
    created() {
      this.fetchAccounts();
    },
  }
</script>
