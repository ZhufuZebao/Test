<template>
  <transition name="fade">
    <div class="modal wd1 modal-show projectlocalechief-model">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="back">×</div>
        <div class="modalBodycontent commonMol">
          <div class="project-localchief common-deteil-wrap report-deteil-wrap clearfix">
            <div class="commonModelAdd"><span>{{localeChiefName('localeChiefAdd')}}</span></div>

            <div class="customer_list">
              <dl class="clearfix sel">
                <div v-for="(chief, chiefIndex) in chiefs" :key="chiefIndex">
                  <div class="selectCustomer">
                    <div class="naiyou" @click.self="choose(chief, chiefIndex)"
                         :class="{'selectedColor': chiefIndex===selectedIndex }">
                      <span @click.self="choose(chief, chiefIndex)">{{chief.name}}</span>
                      <span @click.self="choose(chief, chiefIndex)">{{chief.email}}</span>
                    </div>
                  </div>
                </div>
              </dl>
            </div>

            <div class="modelButton">
              <a @click="addChief">追加</a>
            </div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>

<script>
  import ProjectLists from '../../mixins/ProjectLists'
  import validation from '../../validations/project.js'

  export default {
    name: "ProjectLocaleChiefModal",
    mixins: [
      validation,
      ProjectLists,
    ],
    props: {
      projectLocalChief: Array,
    },
    data: function () {
      return {
        isMounted: false,
        chiefs: {},
        emitChiefs: {},
        selectedIndex: null,
      }
    },
    methods: {
      //担当者を追加
      addChief: function () {
        this.$emit("addLocaleChief", this.emitChiefs);
      },
      //担当者モデルを閉め
      back: function () {
        this.$emit("addLocaleChief", "");
      },

      fetchAccount: function () {
        axios.get('/api/getAccountList').then((res) => {
          let data = res.data;
          if (data){
            for (let j = 0; j< data.length; j ++){
              for (let i = 0;i<this.projectLocalChief.length;i++){
                if (data[j].email === this.projectLocalChief[i].mail){
                  data.splice(j,1);
                }
              }
            }
          }
          this.chiefs = data;
        });
      },
      choose: function (office, index) {
        this.emitChiefs.name = office.name;
        this.emitChiefs.mail = office.email;

        this.selectedIndex = index;
      },
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      }
    },
    mounted() {
      this.isMounted = true;
    },
    created() {
      this.fetchAccount();
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }

  .selectedColor {
    background: #9DC815;
  }
</style>