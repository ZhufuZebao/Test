<template>
  <transition name="fade">
    <div class="modal wd1 modal-show account commonAll customerlist project-enterprise">
      <div class="modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="closeModel">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-view account-list">
            <div class="content" v-if="ListShow">
              <h3 class="modal-account-head" style="text-align:center;">簡易レポートを作成する</h3>
              <br/>
              <div class="mod-account">
                <a class="title-del" v-if="delAccountItem.length !== 0" @click.prevent="delAccount">
                  <img class="img-delete" src="images/icon-dust2.png" alt="">
                </a>
                <div class="flo">
                  <el-input class="popoverto-input" placeholder="案件名検索"
                            @change="searchProject" v-model="searchWord" suffix-icon="searchForm-submit">
                  </el-input>
                </div>
              </div>
              <br/>
              <section class="list-del customer-wrapper invite-form">
                <div class="customertable">
                  <ul class="list-del-header report-serch clearfix">
                    <li class="customer-li"></li>
                    <li class="customer-li">
                      案件名
                      <span @click="sort('construction_name')"><i class="el-icon-d-caret"></i></span>
                    </li>
                  </ul>
                  <ul class="table-scroll">
                    <el-checkbox-group v-model="selectProjectItem">
                      <li :id="'s'+account.id" class="clearfix btns" date-tgt="wd1" v-for="account in accounts"
                          :key="account.id">
                        <span class="customer-li">
                          <el-checkbox disabled :label="account.id"
                                       v-if="account.id === account.auth_id || account.id === account.union_id || account.enterprise_admin === '2'">&nbsp;</el-checkbox>
                          <el-checkbox :label="account.id" v-else>&nbsp;</el-checkbox>
                        </span>
                        <span class="customer-li">
                          <a>{{account.name}}</a>
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
                        <el-tooltip :content=account.email placement="right" effect="light">
                          <span class="customer-li">{{ account.email }}</span>
                        </el-tooltip>
                      </li>
                    </el-checkbox-group>
                  </ul>
                </div>
              </section>
            </div>
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

  export default {
    name: "ReportCreate",
    components: {},
    mixins: [Messages],
    data: function () {
      return {
        projects: {},
        searchWord: '',
        sortCol: "selectProjectItem",
        order: "desc",
        selectProjectItem: [],
        position: {},
        isMounted: false,
        ListShow: true,
        AccountCreate: false,
        isDelete: false,
      }
    },
    methods: {
      fetchProjects: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getProjectList').then((res) => {
          this.projects = res.data;
          this.sort('construction_name')
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.system, {showClose: false});
        });
        loading.close();
      },
      searchProjects() {
        if (!this.searchWord) {
          this.fetchProjects();
          return true;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getProjectListSearch?q=' + this.searchWord).then((res) => {
          this.projects = res.data;
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.system, {showClose: false});
        });
        loading.close();
      },
      //並べ替え
      sort(sortCol) {
        if (this.projects.length !== 0) {
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
        if (col === 'construction_name') {
          if (order === 'asc') {
            this.projects.sort(function (a, b) {
              return a.construction_name.localeCompare(b.construction_name)
            });
          } else {
            this.projects.sort(function (a, b) {
              return b.construction_name.localeCompare(a.construction_name)
            });
          }
        }
      },
      closeModel: function () {
        this.$emit('closeProject');
      },
    },
    created() {
      this.fetchProjects();
    },
    mounted() {
      this.isMounted = true;
    },
    watch: {
      selectProjectItem: function () {
        $(".is-checkedbox").removeClass('is-checkedbox');
        let num = this.selectProjectItem.length;
        for (let i = 0; i < num; i++) {
          let str = '#s' + this.selectProjectItem[i];
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
