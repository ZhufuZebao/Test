<template>
  <transition name="fade">
    <div class="modal wd1 modal-show account commonAll customerlist project-enterprise">
      <div class="modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="closeModel">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-view account-list">
            <div class="content delet-img" v-if="ListShow">
              <h3 class="modal-account-head" style="text-align:center;">アカウント一覧</h3>
              <br/>
              <div class="mod-account">
                <a class="title-add" @click="openCreate">
                  <img src="images/add@2x.png">
                </a>
                <a class="title-del" v-if="delAccountItem.length !== 0" @click.prevent="delAccount">
                  <img class="img-delete" src="images/icon-dust2.png" alt="">
                </a>
                <div class="flo">
                  <li class="flo-amountCount"><span>アカウント数 </span><span>{{accounts.length}}/{{amountCount}}</span></li>
                  <el-input class="popoverto-input" placeholder="名前/メールアドレスで検索"
                            @change="searchAccounts" v-model="searchWord" suffix-icon="searchForm-submit">
                  </el-input>
                </div>
              </div>
              <br/>
              <section class="list-del customer-wrapper invite-form">
                <div class="customertable">
                  <ul class="list-del-header report-serch clearfix">
                    <li class="customer-li"></li>
                    <li class="customer-li">
                      名前
                      <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
                    </li>
                    <li class="customer-li">
                      アカウント区分
                      <span @click="sort('type')"><i class="el-icon-d-caret"></i></span>
                    </li>
                    <li class="customer-li">
                      メールアドレス
                      <span @click="sort('email')"><i class="el-icon-d-caret"></i></span>
                    </li>
                  </ul>
                  <ul class="table-scroll">
                    <el-checkbox-group v-model="delAccountItem">
                      <li :id="'s'+account.id" class="clearfix btns" date-tgt="wd1" v-for="account in accounts"
                          :key="account.id">
                        <span class="customer-li">
                          <el-checkbox disabled :label="account.id"
                                       v-if="account.id === account.auth_id || account.id === account.union_id || account.enterprise_admin === '2'">&nbsp;</el-checkbox>
                          <el-checkbox :label="account.id" v-else>&nbsp;</el-checkbox>
                        </span>
                        <span class="customer-li" @click="openUpdate(account.id,account.enterprise_admin)" style="cursor:pointer;">
                          {{account.name}}
                        </span>
                        <span class="customer-li" v-if="account.enterprise_admin === '1'" style="cursor:pointer;"
                              @click="selectAuthority(account.id,account.enterprise_admin)">
                           <img src="images/swich_02.png" alt="">管理
                        </span>
                        <span class="customer-li" v-if="account.enterprise_admin === '0'" style="cursor:pointer;"
                              @click="selectAuthority(account.id,account.enterprise_admin)">
                          <img src="images/swich_02.png" alt="">一般
                        </span>
                        <span class="customer-li left-padding" v-if="account.enterprise_admin === '2'" >
                          協力会社
                        </span>
                        <span class="customer-li left-padding" v-if="!account.enterprise_admin" >

                        </span>
                        <el-tooltip :content=account.email placement="right" effect="light">
                          <span class="customer-li">{{ account.email }}</span>
                        </el-tooltip>
                      </li>
                    </el-checkbox-group>
                  </ul>
                </div>
              </section>
            </div>
            <AccountCreate @closeCreate="closeCreate" v-if="AccountCreate" :enterpriseId="enterpriseId" :accountId="accountId"/>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>


<script>
  import Messages from "../../mixins/Messages";
  import AccountCreate from "./AccountCreate";

  export default {
    name: "AccountList",
    components: {AccountCreate},
    mixins: [Messages],
    props:{
      enterpriseId:0,
    },
    data: function () {
      return {
        accountId:0,
        isCreate:false,
        accounts: [],
        searchWord: '',
        sortCol: "name",
        order: "desc",
        delAccountItem: [],
        position: {},
        isMounted: false,
        ListShow: true,
        AccountCreate: false,
        isDelete: false,
        amountCount:0,
      }
    },
    methods: {
      selectAuthority:function(id,admin){
        if(admin === '2'){
          return false;
        }
        for (let i=0;i<this.accounts.length;i++){
          let account = this.accounts[i];
          if (id === account.auth_id ||
              id === account.union_id ||
              (account.id === account.auth_id && account.enterprise_admin === '0')) {
            return false;
          }
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/switchIdentity',{
          'id':id,
          'admin':admin,
          'enterpriseId':this.enterpriseId
        }).then((res) => {
          loading.close();
          if(!res.data.result){
            this.accounts.filter(item=>{
              if (item.id === id){
                if (item.enterprise_admin === admin && admin === '1'){
                  item.enterprise_admin = '0';
                }else if(item.enterprise_admin === admin && admin === '0'){
                  item.enterprise_admin = '1';
                }
                return item;
              }
            });
            this.$alert(this.commonMessage.success.update, {showClose: false});
          }else{
            this.$alert(this.commonMessage.error.switchIdentity, {showClose: false});
          }
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.switchIdentity, {showClose: false});
        });
      },
      openUpdate: function(id,admin){
        if (admin === '2'){
          return false;
        }
        if (id === this.accounts[0].auth_id ||
            id === this.accounts[0].union_id ) {
          return false;
        }
        let status = this.accounts.filter(item=>{
          if (id === item.auth_id ||
              id === item.union_id ) {
            return false;
          } else if(id ===item.id && item.enterprise_admin === '0'){
            return false;
          }else{
            return true;
          }
        });
        if (status){
          this.accountId = id;
          this.ListShow = false;
          this.AccountCreate = true;
        }

      },
      //帳票をひらく
      openCreate: function () {
        this.ListShow = false;
        this.AccountCreate = true;
      },
      //帳票を閉じる
      closeCreate: function (status) {
        this.accountId = 0;
        this.AccountCreate = false;
        this.ListShow = true;
        if (status === 1) {
          this.searchWord = "";
          this.fetchAccounts(status);
        }
      },
      // Account一覧
      fetchAccounts: function (status = 0) {
        if (status){
          this.isCreate = true;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getAccountList', {
          params: {
            enterpriseId: this.enterpriseId
          }
        }).then((res) => {
          this.accounts = res.data;
          this.order = 'asc';
          this.sort('name')
          this.setAmountCount();
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.accountList, {showClose: false});
        });
        loading.close();
      },
      //会社のamountを取得
      setAmountCount() {
        axios.get('/api/getEnterpriseAmount', {
          params: {
            enterpriseId: this.enterpriseId
          }
        }).then((res) => {
          this.amountCount = res.data;
        }).catch(error => {
          this.$alert(this.commonMessage.error.accountList, {showClose: false});
        });
      },
      // Account検索
      searchAccounts() {
        if (!this.searchWord) {
          this.fetchAccounts();
          return true;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getAccountSearch', {
          params: {
            q: this.searchWord,
            enterpriseId: this.enterpriseId
          }
        }).then((res) => {
          this.accounts = res.data;
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.system, {showClose: false});
        });
        loading.close();
      },
      // Account削除
      delAccount: function () {
        let message = this.commonMessage.success.delete;
        let errMessage = this.commonMessage.error.accountDelete;
        if (!this.delAccountItem.length) {
          this.$alert(errMessage, {showClose: false});
          return false;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
          axios.post("/api/deleteAccount", {
            ids: this.delAccountItem,
            enterpriseId: this.enterpriseId,
          }).then(res => {
            loading.close();
            let delIndex = [];
            for (let i = 0; i < this.accounts.length; i++) {
              if (!$.inArray(this.accounts[i]['id'], this.delAccountItem)) {
                delIndex.push(i);
              }
            }
            for (let l = 0; l < delIndex.length; l++) {
              this.accounts.splice(delIndex[l], 1);
            }
            this.delAccountItem = [];
            this.isDelete = true;
            this.$alert(message, {showClose: false});
          }).catch(error => {
            loading.close();
            this.$alert(errMessage, {showClose: false});
          });
        }).catch(action => {
          loading.close();
        });
      },
      //並べ替え
      sort(sortCol) {
        if (this.accounts.length !== 0) {
          if (this.sortCol === sortCol) {
            this.changeOrder();
          } else {
            this.sortCol = sortCol;
            this.order = 'desc'
          }
          //序列
          this.sortOrder();
        }
      },
      //順序付け規則を変更する
      changeOrder() {
        if (this.order === 'asc') {
          this.order = 'desc';
        } else {
          this.order = 'asc';
        }
      },
      //ソート方法
      sortOrder: function () {
        let col = this.sortCol;
        let order = this.order;
        if (col === 'name') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.name.localeCompare(b.name)
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.name.localeCompare(a.name)
            });
          }
        }
        if (col === 'type') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.enterprise_admin - b.enterprise_admin
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.enterprise_admin - a.enterprise_admin
            });
          }
        }
        if (col === 'email') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.email.localeCompare(b.email)
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.email.localeCompare(a.email)
            });
          }
        }
      },
      closeModel: function () {
        if (this.isDelete){
          this.$emit('closeAccount','delete');
        }else if (this.isCreate){
          this.$emit('closeAccount','create');
        } else {
          this.$emit('closeAccount');
        }
      },
    },
    created() {
      this.fetchAccounts();
    },
    mounted() {
      this.isMounted = true;
    },
    watch: {
      delAccountItem: function () {
        $(".is-checkedbox").removeClass('is-checkedbox');
        let num = this.delAccountItem.length;
        for (let i = 0; i < num; i++) {
          let str = '#s' + this.delAccountItem[i];
          $(str).addClass('is-checkedbox');
        }
      }
    },
    //窓際の位置を計算する
    computed: {
      modalLeft: function () {
        if (this.isMounted) {
          return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
        } else {
          return;
        }
      },
      modalTop: function () {
        return '0px';
      },
    },
  }
</script>
