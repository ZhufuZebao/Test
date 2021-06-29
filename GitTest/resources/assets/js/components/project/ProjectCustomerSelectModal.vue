<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="back" v-if="openCloseBtn">×</div>
        <div class="modalBodycontent commonMol customer-add" v-if="customerSelectModal">
          <div class="common-deteil-wrap customerSel clearfix">
            <div class="commonModelAdd"><span>施主追加</span></div>
            <div class="customerName">
              <img class="searchImg" src="images/search.png"/>
              <el-input placeholder="名前で検索" v-model="words" @change="search" maxlength="30">
              </el-input>
            </div>
            <div class="customer_list">
              <dl class="clearfix sel">
                <div v-for="(office, officeIndex) in customerOffices" :key="officeIndex" v-if="office">
                  <div class="selectCustomer">
                    <div class="naiyou" @click.self="choose(office, officeIndex)"
                         :class="{'selectedColor': officeIndex===selectedIndex }">
                      <span @click.self="choose(office, officeIndex)">{{office.customer.name}}</span>
                      <span @click.self="choose(office, officeIndex)">{{office.name}}</span>
                    </div>
                  </div>
                </div>
              </dl>
            </div>
            <div class="pro-button customer_list-button">
              <a class="nextPage" @click.prevent="openCustomerCreateModal">施主新規</a>
              <a class="nextPage" @click.prevent="addCustomer">登録する</a>
            </div>
          </div>
        </div>
        <ProjectCustomerCreateModal :isProject="isProject" @showSelectBtn="showSelectBtn" @hiddenSelectBtn="hiddenSelectBtn" @closeCreate="closeCreate" @openSelect="openSelect" v-else></ProjectCustomerCreateModal>
      </div>
      <div class="modalBK"></div>

    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import ProjectCustomerCreateModal from '../../components/project/ProjectCustomerCreateModal'
  export default {
    name: "ProjectCustomerSelectModal",
    components: {
      ProjectCustomerCreateModal
    },
    data: function () {
      return {
        officeIds:[],
        words: "",
        isMounted: false,
        selectedIndex: null,
        customerOffices: [],
        chooseCustomer: {
          id: '',
          officeId: '',
          name: '',
        },
        customerSelectModal: true,
        openCloseBtn: true,
        isProject: true,
      }
    },
    methods: {
      fetchOffice: function(officeIds) {
        this.officeIds = officeIds;
        axios.post("/api/getCustomerOffice", {
            ids: officeIds,
        }).then((res) => {
          this.customerOffices = res.data;
        })
      },
      //施主を検索
      search: function () {
        axios.post("/api/getCustomerOffice", {
            keyword: this.words,
            ids:this.officeIds,
        }).then((res) => {
          this.customerOffices = res.data;
        });
      },
      //選択の場合
      choose: function (office, index) {
        this.chooseCustomer.id = office.customer.id;
        this.chooseCustomer.officeId = office.id;
        this.chooseCustomer.tel = office.tel;
        this.chooseCustomer.name = office.customer.name;
        this.chooseCustomer.people = office.people;
        if (office.name) {
          this.chooseCustomer.name = this.chooseCustomer.name + "  " + office.name;
        }
        this.selectedIndex = index;
      },
      //施主を登録
      addCustomer: function () {
        if (!this.chooseCustomer.id){
          this.$emit("closeModal", "");
        } else{
          this.$emit("closeModal", this.chooseCustomer);
        }
      },
      //施主を閉め
      back: function () {
        this.openSelect();
        this.$emit("closeModal", "");
      },

      openCustomerCreateModal: function () {
        this.customerSelectModal = false;
      },
      openSelect: function () {
        this.customerSelectModal = true;
      },
      closeCreate: function (status) {
        this.openSelect();
        this.showSelectBtn = true;
        if (status) {
          this.chooseCustomer.id = status.customer.id;
          this.chooseCustomer.officeId = status.office.id;
          this.chooseCustomer.name = status.customer.name;
          this.chooseCustomer.tel = status.office.tel;
          if (status.office.name) {
            this.chooseCustomer.name = this.chooseCustomer.name + "  " + status.office.name;
          }
          this.chooseCustomer.people = status.people;
          this.$emit("closeModal", this.chooseCustomer);
        } else {
          this.$emit("closeModal");
        }
      },
      showSelectBtn :function () {
        this.openCloseBtn = true;
      },
      hiddenSelectBtn : function () {
        this.openCloseBtn = false;
      }
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        // return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
        return '-350px';
      },
      modalTop: function () {
        return '0px';
      }
    },
    mounted() {
      this.isMounted = true;
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
