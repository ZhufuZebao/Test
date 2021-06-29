<template>
  <transition name="fade">
    <div class="modal wd1 modal-show project-enterprise reportcreat">
      <div class="modalBodyCustomer modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="closeModel">×</div>
        <div class="modalBodycontent commonMol customer-add">
          <div class="common-deteil-wrap customerSel clearfix">
            <div class="commonModelAdd" v-if="ListShow"><span>簡易レポートを作成する</span></div>
            <div class="customerName">
              <img class="searchImg" src="images/search.png"/>
              <el-input class="popoverto-input" placeholder="案件名検索"
                        @change="searchProject" v-model="searchWord">
              </el-input>
            </div>
            <section>
              <div class="reportcreat">
                <ul class="list-del-header clearfix">
                  <li class="customer-li">
                    案件名
                    <span @click="sort('place_name')"><i class="el-icon-d-caret"></i></span>
                  </li>
                </ul>
                <ul class="table-scroll">
                  <el-checkbox-group v-model="selectProjectItem" :min="0" :max="1">
                    <li :id="'s'+project.id" class="clearfix btns" date-tgt="wd1" v-for="project in projects"
                        :key="project.id">
                        <span class="">
                          <el-checkbox :label="project.id">
                            <span class="customer-li"  style="margin-left: 16px;">
                          {{project.place_name}}
                        </span>
                          </el-checkbox>
                          
                        </span>

                    </li>
                  </el-checkbox-group>
                </ul>
              </div>
              <div class="modelBut">
                <el-button type="info" round v-if="this.selectProjectItem.length" @click="createReport">簡易レポートを作成する</el-button>
              </div>

            </section>

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
        isDelete: false,
      }
    },
    methods: {
      fetchProjects: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getReoprtProjectList').then((res) => {
          this.projects = res.data.model;
          this.sort('place_name')
        }).catch(error => {
          loading.close();
          this.$alert(error, {showClose: false});
        });
        loading.close();
      },
      searchProject() {
        if (!this.searchWord) {
          this.fetchProjects();
          return true;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getReoprtProjectList?searchWord=' + this.searchWord).then((res) => {
          this.projects = res.data.model;
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
        if (col === 'place_name') {
          if (order === 'asc') {
            this.projects.sort(function (a, b) {
              return a.place_name.localeCompare(b.place_name)
            });
          } else {
            this.projects.sort(function (a, b) {
              return b.place_name.localeCompare(a.place_name)
            });
          }
        }
      },
      closeModel: function () {
        this.$emit('closeProject');
      },
      createReport: function () {
        let id = this.selectProjectItem[0];
        this.$router.push({path: '/report/create/' + id});
      }
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
        if (num >= 1) {
          return false;
        }
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
