<template>
  <div class="container clearfix  partner customerlist companylists commonAll">
    <header>
      <h1>
        <router-link to="/company">
          <div class="commonLogo">
            <ul>
              <li class="bold">COMPANY</li>
              <li>会社情報</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>会社情報一覧</h2>
        <a class="title-add">
          <img src="images/add@2x.png" @click="create()"/>
        </a>

        <div class="flo title-form">
          <el-input placeholder="名前"
                    @change="search" v-model="keyword" suffix-icon="searchForm-submit">
          </el-input>
        </div>
      </div>
      <UserProfile/>
    </header>
    <section class="customer-wrapper delet-img"  style="margin-top: 60px;">

      <div class="companylist">
        <a class="title-del" v-if="delCompanyItem.length !== 0" @click.prevent="delCompanyItems">
          <img class="img-delete" src="images/icon-dust2.png" alt="">
        </a>
      </div>

      <div class="documentable">
        <div class="list-del-common">
          <ul class="list-del-header report-serch clearfix">
            <li class="customer-li">{{ companyName('name') }}<span @click="sort('name')"><i class="el-icon-d-caret"></i></span></li>
            <li class="customer-li">{{ companyName('tel')}}</li>
            <li class="customer-li">{{ companyName('address') }}</li>
            <li class="customer-li">{{ companyName('updated_at') }}<span @click="sort('updated_at')"><i class="el-icon-d-caret"></i></span></li>
          </ul>
        </div>
        <ul class="customertablelist tablelisthover">
          <el-checkbox-group v-model="delCompanyItem">
            <li :id="'s'+company.id" class="clearfix btns" date-tgt="wd1" v-for="company in companys"
                :key="'company+'+company.id">
              <span class="customer-li" style="cursor:pointer"><el-checkbox :label="company.id">&nbsp;</el-checkbox><span @click="companyDetail(company.id)">{{ company.name }}</span></span>
              <span class="customer-li" style="cursor:pointer" @click="companyDetail(company.id)">{{ company.tel }}</span>
              <span class="customer-li" style="cursor:pointer" @click="companyDetail(company.id)">{{ company.pref}}{{ company.town}}{{ company.street}}{{ company.house}}</span>
              <span class="customer-li" style="cursor:pointer" @click="companyDetail(company.id)">{{ company.updated_at }}</span>
            </li>
          </el-checkbox-group>
        </ul>
      </div>
      <div class="pagination-center">
        <el-pagination
                prev-text="◀"
                next-text="▶"
                background
                layout="prev, pager, next"
                @current-change="changePage" :current-page="currentPage" :page-size="perPage"
                :total="total">
        </el-pagination>
      </div>
    </section>


  </div>
</template>

<script>
    import CompanyLists from "../../mixins/CompanyLists";
    import Pagination from "../../components/common/Pagination";
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../mixins/Messages";

    export default {
        components: {
            Pagination,
            UserProfile
        },
        mixins: [CompanyLists, Messages],
        data() {
            return {
                companys: null,
                total: 1,
                from: 0,
                to: 0,
                perPage: 10,
                currentPage: 1,
                current: 1,
                deleted: false,
                sortCol: "id",
                order: "asc",
                keyword: '',
                KeyName:'',
                createFlag: false,
                delCompanyItem: []
            }
        },
        methods: {
            //ドナーリストを取得する
            fetchCompanys: function () {
                let data = {params: {page: this.currentPage,sortCol: this.sortCol, order: this.order,KeyName:this.KeyName}};
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.get('/api/company', data).then((res) => {
                    this.companys = res.data.companys;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    loading.close();
                }).catch(action => {
                    loading.close();
                });
            },
            //序列
            sort(sortCol) {
                if (this.sortCol === sortCol) {
                    this.changeOrder();
                } else {
                    this.sortCol = sortCol;
                    this.order = 'asc'
                }
                let data = {params: {page: this.currentPage, sortCol: this.sortCol, order: this.order, KeyName:this.KeyName}};
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.get('/api/company', data).then((res) => {
                    this.companys = res.data.companys;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    loading.close();
                }).catch(action => {
                    loading.close();
                });
            },
            //ソート条件の変更
            changeOrder() {
                if (this.order === 'asc') {
                    this.order = 'desc';
                } else {
                    this.order = 'asc';
                }
            },
            //ページを切り替える
            changePage: function (page) {
                this.currentPage = page;
                if (this.KeyName) {
                    this.searchPage();
                } else {
                    this.fetchCompanys();
                }
            },
            //トランジスター詳細インターフェース
            companyDetail(id) {
                this.$router.push({path: '/company/detail/' + id});
            },
            create(){
                this.$router.push({path: '/company/create'});
            },
            //キーワード検索
            search() {
                this.KeyName=this.keyword;
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let data = {params: {page: 1, sortCol: this.sortCol, order: this.order, KeyName:this.KeyName}};
                axios.get('/api/company', data).then((res) => {
                    this.companys = res.data.companys;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    loading.close();
                }).catch(error => {
                    loading.close();
                });
            },
            searchPage(){
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let data = {params: {page: this.currentPage, sortCol: this.sortCol, order: this.order, KeyName:this.keyword}};
                axios.get('/api/company', data).then((res) => {
                    this.companys = res.data.companys;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    loading.close();
                }).catch(error => {
                    loading.close();
                });
            },
            openCreate() {
                this.createFlag = true;
            },
            closeProject() {
                this.createFlag = false;
            },
            delCompanyItems() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.delete;
                this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
                    axios.post("/api/deleteCompany", {
                        id: this.delCompanyItem,
                    }).then(res => {
                        this.delCompanyItem = [];
                        this.fetchCompanys();
                    }).catch(error => {
                        this.$alert(errMessage, {showClose: false});
                        this.delCompanyItem = [];
                        loading.close();
                    });
                }).catch(action => {
                    this.delCompanyItem = [];
                    loading.close();
                });
            },
        },
        created() {
            this.fetchCompanys();
        },
    }
</script>
<style scoped>
  .flo {
    float: left;
  }
  .customer-li {
    width: 25%;
  }
  .title-form {
    width: auto;
  }

</style>
