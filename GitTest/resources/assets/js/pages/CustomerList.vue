<template>
  <div>
  <section class="customer-wrapper">
    <header>
      <h1><router-link to="/customer">施主</router-link></h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>施主一覧</h2>
        <router-link tag='button' to="/customer/create">新規登録</router-link>
        <button type="button" class="btn-serch" @click.prevent="openSearchModal">詳細検索</button>
      </div>
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

    <!-- ページネーション -->
    <div style="margin-top: 40px" class="col-sm-6 text-right">
      <template v-if='searchArray'>検索結果 </template>
      <template v-else>全 </template>
      {{total}} 件中 {{from}} 〜 {{to}} 件表示
    </div>
    <button type="button" class="btn-serch" @click.prevent="creaSearch">検索解除</button>
    <ul class="pagination">
          <li :class="{disabled: current_page <= 1}"><a href="#" @click="change(1)">&laquo;</a></li>
          <li :class="{disabled: current_page <= 1}"><a href="#" @click="change(current_page - 1)">&lt;</a></li>
          <li v-for="page in pages" :key="page" :class="{active: page === current_page}">
            <a href="#" @click="change(page)">{{page}}</a>
          </li>
          <li :class="{disabled: current_page >= last_page}"><a href="#" @click="change(current_page + 1)">&gt;</a></li>
          <li :class="{disabled: current_page >= last_page}"><a href="#" @click="change(last_page)">&raquo;</a></li>
    </ul>

    <!-- 施主詳細モーダル -->
    <CustomerDetailModal :customer="postDetail" v-if="showDetailModal" @editCustomer="openEditModal(postDetail)" @detailClose="closeDetailModal" @customerDeleted="openDeletedModal"></CustomerDetailModal>

    <!-- 検索モーダル -->
    <CustomerSearchModal :searchArray="searchArray" v-if="showSearchModal" @searchSubmit="searchSubmit" @searchClose="closeSearchModal"></CustomerSearchModal>

    <!-- 施主一覧テーブル -->
    <table class="table">
      <thead>
        <tr>
          <th>{{ customersCols.find(key => key.col === 'id').name }}<span class="button-s sort" @click="sort('id')"></span></th>
          <th>{{ customersCols.find(key => key.col === 'name').name }}<span class="button-s sort" @click="sort('phonetic')"></span></th>
          <th>{{ officesCols.find(key => key.col === 'name').name }}</th>
          <th>{{ officesCols.find(key => key.col === 'tel').name }}</th>
          <th>{{ officesCols.find(key => key.col === 'fax').name }}</th>
          <th>{{ peopleCols.find(key => key.col === 'name').name }}</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="customer in customers" >
          <tr v-for="(office,index) in customer.offices" v-bind:key="office.id">
            <td v-if="index===0" v-bind:rowspan="customer.offices.length">{{ customer.id }}</td>
            <td v-if="index===0" v-bind:rowspan="customer.offices.length" @click="openDetailModal(customer)"> {{ customer.name }}</td>
            <td >{{ office.name }}</td>
            <td >{{ office.tel }}</td>
            <td >{{ office.fax }}</td>
            <!-- 担当者いない場合の暫定v-if（デバッグ用）　本来は担当者必須なので不要 -->
            <td v-if="office.people[0]">{{ office.people[0].name }}</td>
          </tr>
        </template>
      </tbody>
    </table>
	</section>
</div>
</template>

<script>
import CustomerDetailModal from '../components/CustomerDetailModal.vue'
import CustomerSearchModal from '../components/CustomerSearchModal.vue'
import CustomerCols from '../mixins/CustomerCols'
export default {
  components: {
    CustomerDetailModal,CustomerSearchModal
  },
  mixins: [ CustomerCols ],
  data() { 
    return {
      customers:null,         //施主データ
      showDetailModal: false, //施主詳細モーダル表示フラグ
      postDetail: null,       //施主詳細モーダルへ渡すデータ
      current_page: 1,        //現在ページ
      last_page: 1,           //最終ページ
      total: 1,               //全件数
      from: 0,                //表示件数FROM
      to: 0,                  //表示件数TO
      searchArray: null,      //詳細検索対象
      showSearchModal: false, //検索モーダル表示フラグ
      deleted: false,         //削除完了フラグ
      sortCol: "id",          //ソートカラム
      order: "asc"            //昇順、降順
    }
  },
  mounted: function() {
    this.fetchCustomers();
  },
  methods: {
          fetchCustomers: function(){
            axios.get('/shokunin/api/getCustomerList').then((res)=>{
              this.customers = res.data.data 
              this.current_page = res.data.current_page
              this.last_page = res.data.last_page
              this.total = res.data.total
              this.from =res. data.from
              this.to = res.data.to

            })
          },
          load(page) {
            axios.post('/shokunin/api/getCustomerDetailedSearch', {
              searchArray: this.searchArray,
              sort: this.sortCol,
              order: this.order,
              page: page
            }).then((res)=>{
              this.customers = res.data.data 
              this.current_page = res.data.current_page
              this.last_page = res.data.last_page
              this.total = res.data.total
              this.from =res. data.from
              this.to = res.data.to
            })
          },
          change(page) {
            if (page >= 1 && page <= this.last_page) this.load(page)
          },
          sort(sortCol) {
            if(this.sortCol == sortCol){
              this.changeOrder();
            }else{
              this.sortCol = sortCol;
              this.order = 'asc'
            }
            axios.post('/shokunin/api/getCustomerDetailedSearch', {
              searchArray: this.searchArray,
              sort: this.sortCol,
              order: this.order,

            }).then((res)=>{
              console.log(11111111)
              this.customers = res.data.data 
              this.current_page = res.data.current_page
              this.last_page = res.data.last_page
              this.total = res.data.total
              this.from =res. data.from
              this.to = res.data.to
            })
          },
          openDetailModal(customer) {
            this.postDetail = customer;
            this.showDetailModal = true;
          },
          closeDetailModal() {
            this.showDetailModal = false;
          },
          openDeletedModal() {
            this.deleted = true;
          },
          closeDeletedModal() {
            this.deleted = false;
          },
          openSearchModal() {
            this.showSearchModal = true;
          },
          closeSearchModal() {
            this.showSearchModal = false;
          },
          searchSubmit($searchForm){
            this.searchArray = $searchForm;
            axios.post('/shokunin/api/getCustomerDetailedSearch', {
              searchArray: this.searchArray,
              sort: this.sortCol,
              order: this.order,
            }).then((res)=>{
              this.customers = res.data.data 
              this.current_page = res.data.current_page
              this.last_page = res.data.last_page
              this.total = res.data.total
              this.from =res. data.from
              this.to = res.data.to
            })
            this.showSearchModal = false;
          },
          creaSearch(){
            this.searchArray = null;
            this.sortCol = 'id';
            this.order = 'asc';
            this.fetchCustomers();
          },
          changeOrder(){
            if(this.order === 'asc'){
              this.order = 'desc';
            }else{
              this.order = 'asc';
            };
          },          
        },
  computed: {
          pages() {
            let start = _.max([this.current_page - 2, 1])
            let end = _.min([start + 5, this.last_page + 1])
            start = _.max([end - 5, 1])
            return _.range(start, end)
          },
        },
  created() {
          this.fetchCustomers()
        },
}
</script>
