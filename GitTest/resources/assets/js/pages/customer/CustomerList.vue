<template>
  <div class="container clearfix customer customerlist commonAll">
    <header>
      <h1>
        <router-link to="/customer">
          <div class="commonLogo">
            <ul>
              <li class="bold">CUSTOMER</li>
              <li>施主</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>施主一覧</h2>
        <div class="title-add">
          <router-link to="/customer/create"><img src="images/add@2x.png"/></router-link>
        </div>

        <div class="flo title-form">
          <el-input placeholder="施主名・場所など"
                    @change="search" v-model="keyword"  suffix-icon="searchForm-submit" >
          </el-input>
        </div>
        <div v-if="false" class="button-s" @click.prevent="openSearchModal">詳細検索</div>
      </div>
      <UserProfile/>
    </header>
    <div class="modal-wrap">
      <!-- 施主削除後のモーダル -->
      <CommonModal @close="closeDeletedModal" v-if="deleted">
        <template>
          <p>
            削除が完了しました
          </p>
          <button @click="closeDeletedModal">OK</button>
        </template>
      </CommonModal>
    </div>
    <section class="customer-wrapper">
      <!-- ページネーション -->
      <div class="countDiv">{{total}} 件中 {{from}} 〜 {{to}} 件表示
        <a @click="fetchCustomers">全部显示</a>
      </div>

      <div class="customertable">
        <ul class="report-serch clearfix">
          <li class="customer-li">
            {{ customersName('name') }}
            <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
          </li>
          <li class="customer-li">
            {{ officesName('name')}}
          </li>
          <li class="customer-li">
            {{ officesName('tel') }}
          </li>
          <li class="customer-li">
            {{ officesName('fax') }}
          </li>
          <li class="customer-li">
          </li>
        </ul>
        <ul class="customertablelist tablelisthover" v-for="customer in customers" :key="'customers+'+customer.id+customer.name">
          <li class="clearfix btns" date-tgt="wd1" v-for="(office,index) in customer.offices" v-bind:key="office.id">
            <span class="customer-li" v-if="index === 0 && index === customer.offices.length - 1" @click="customerDetail(customer)">{{ customer.name }}</span>
            <span class="customer-li noborder" v-else-if="index ===0 && index < customer.offices.length - 1" @click="customerDetail(customer)">{{ customer.name }}</span>
            <span class="customer-li noborder" v-else-if="index >=0 && index < customer.offices.length - 1"></span>
            <span class="customer-li" v-else></span>
            <span class="customer-li" @click="officeDetail(office)">{{ office.name }}</span>
            <span class="customer-li">{{ office.tel }}</span>
            <span class="customer-li">{{ office.fax }}</span>
            <span class="customer-li"></span>
          </li>
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
    <!-- 検索モーダル -->
    <CustomerSearchModal :searchArray="searchArray" v-if="showSearchModal" @searchSubmit="searchSubmit"
                         @searchClose="closeSearchModal"></CustomerSearchModal>

  </div>
</template>

<script>
  import CustomerSearchModal from '../../components/customer/CustomerSearchModal';
  import CustomerCols from '../../mixins/CustomerColsNew';
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";

  export default {
    components: {
      CustomerSearchModal,
      Pagination,
      UserProfile
    },
    mixins: [CustomerCols,Messages],
    data() {
      return {
        customers: null,         //施主データ
        postDetail: null,       //施主詳細モーダルへ渡すデータ
        total: 1,               //全件数
        from: 0,                //表示件数FROM
        to: 0,                  //表示件数TO
        searchArray: null,      //詳細検索対象
        showSearchModal: false, //検索モーダル表示フラグ
        deleted: false,         //削除完了フラグ
        sortCol: "id",          //ソートカラム
        order: "asc",            //昇順、降順
        keyword: '',
        perPage: 10,           //一ページの件数
        currentPage: 1,        //現在ページ
        current: 1,
      }
    },
    methods: {
      //ドナーリストを取得する
      fetchCustomers: function () {
        let data = {params: {
          page: this.currentPage,
          sort: this.sortCol,
          order: this.order,
        }};
        let errMsg = this.commonMessage.error.customerList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getCustomerList', data).then((res) => {

          loading.close();
          this.keyword = '';
          this.customers = res.data.data;
          console.log(this.customers);
          console.log(res);
          for(let i=0;i<this.customers.length;i++){
            for(let l=0;l<this.customers[i]['offices'].length;l++){
              this.customers[i]['offices'][l]['tel'] = this.change_tel(this.customers[i]['offices'][l]['tel'],this.customers[i]['offices'][l]['tel_in']);
            }
          }
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0
        }).catch(action => {
          this.$alert(errMsg);
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
        let errMsg = this.commonMessage.error.customerList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/searchCustomers', {
          page: this.currentPage,
          keyword: this.keyword,
          sort: this.sortCol,
          order: this.order,
        }).then((res) => {
          this.customers = res.data.data;
          for(let i=0;i<this.customers.length;i++){
            for(let l=0;l<this.customers[i]['offices'].length;l++){
              this.customers[i]['offices'][l]['tel'] = this.change_tel(this.customers[i]['offices'][l]['tel']);
            }
          }
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
          loading.close();
        }).catch(action => {
          this.$alert(errMsg);
          loading.close();
        });
      },
      //窓を弾くヒント
      openDeletedModal() {
        this.deleted = true;
      },
      //弾窓を閉じる
      closeDeletedModal() {
        this.deleted = false;
      },
      //検索窓からポップアップする
      openSearchModal() {
        this.showSearchModal = true;
      },
      //検索窓が閉まる
      closeSearchModal() {
        this.showSearchModal = false;
      },
      //帳票を検索して提出する
      searchSubmit($searchForm) {
        this.searchArray = $searchForm;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/customerDetailedSearch', {
          searchArray: this.searchArray,
          sort: this.sortCol,
          order: this.order,
        }).then((res) => {
          loading.close();
          this.customers = res.data.data;
          for(let i=0;i<this.customers.length;i++){
            for(let l=0;l<this.customers[i]['offices'].length;l++){
              this.customers[i]['offices'][l]['tel'] = this.change_tel(this.customers[i]['offices'][l]['tel']);
            }
          }
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0
        }).catch(action => {
          loading.close();
        });
        this.showSearchModal = false;
      },
      //ソート条件の変更
      creaSearch() {
        this.searchArray = null;
        this.sortCol = 'id';
        this.order = 'asc';
        this.fetchCustomers();
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
        document.body.scrollTop =  0;
        document.documentElement.scrollTop = 0;
        window.pageYOffset = 0;
        if (this.keyword) {
          this.searchPage();
        } else {
          this.fetchCustomers();
        }
      },
      //トランジスター詳細インターフェース
      customerDetail(customer) {
        this.$router.push({path: '/customer/detail/' + customer.id});
      },
      //トランジスター事業所詳細インターフェース
      officeDetail(office) {
        this.$router.push({path: '/customer/office/' + office.id});
      },
      //キーワード検索
      search() {
        // 検索キーワードが空の場合
        if (this.keyword.length <= 0) {
          this.fetchCustomers();
          return;
        }
        let errMsg = this.commonMessage.error.customerList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let data = {
          page: 1,
          keyword: this.keyword,
          sort: this.sortCol,
          order: this.order,
        };
        axios.post('/api/searchCustomers', data).then((res) => {
          loading.close();
          this.customers = res.data.data;
          for(let i=0;i<this.customers.length;i++){
            for(let l=0;l<this.customers[i]['offices'].length;l++){
              this.customers[i]['offices'][l]['tel'] = this.change_tel(this.customers[i]['offices'][l]['tel']);
            }
          }
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
        }).catch(error => {
          this.$alert(errMsg);
          loading.close();
        });
      },

      searchPage() {
        let errMsg = this.commonMessage.error.customerList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let data = {
          page: this.currentPage,
          keyword: this.keyword,
          sort: this.sortCol,
          order: this.order,
        };
        axios.post('/api/searchCustomers', data).then((res) => {
          loading.close();
          this.customers = res.data.data;
          for(let i=0;i<this.customers.length;i++){
            for(let l=0;l<this.customers[i]['offices'].length;l++){
              this.customers[i]['offices'][l]['tel'] = this.change_tel(this.customers[i]['offices'][l]['tel']);
            }
          }
          this.currentPage = res.data.current_page;
          this.total = res.data.total;
          this.from = res.data.from ? res.data.from : 0;
          this.to = res.data.to ? res.data.to : 0;
        }).catch(error => {
          this.$alert(errMsg);
          loading.close();
        });
      },
        change_tel(tel,tel_in) {
            if(tel_in){
                return tel + " 内線" +tel_in;
            }else{
                return tel;
            }
      }
    },
    created() {
      this.fetchCustomers();
    },
  }
</script>
<style scoped>
  .flo {
    float: left;
  }

  .customer-li {
    width: 20%;
  }

  .noborder {
    border-bottom: 0 !important;
  }

  .title-form {
    width: auto;
  }

</style>
