<template>
  <transition name="customer-detail-modal">
    <div class="customer-detail-modal-mask">
      <div class="modal-wrapper" @click.self="$emit('detailClose')">
        <!-- 施主削除のモーダル -->
        <CommonModal @close="closeDelModal" v-if="delCheck">
          <template>
            <p>
              この施主を削除しますか？
            </p>
            <button @click="delYes">はい</button>
            <button @click="delNo">いいえ</button>
          </template>
        </CommonModal>

        <div class="modal-container">
          <label for="customer-name">{{ customersCols.find(key => key.col === 'name').name }}：</label>
          {{ customer.name }}
          <label for="customer-phonetic">{{ customersCols.find(key => key.col === 'phonetic').name }}：</label>
          {{ customer.phonetic }}
          <CustomerDetailOffice v-for="(office,index) in customer.offices" v-bind:office="office" v-bind:key="office.id"/>
          <router-link tag="button" :to="{name:'edit',params:{id:customer.id}}"> 編集</router-link>
          <button @click.prevent="openDelModal">
            削除
          </button>
          <button @click="$emit('detailClose')">
            閉じる
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
import CustomerDetailOffice from '../components/CustomerDetailOffice.vue'
export default {
    name: 'CustomerDetailModal',
    components: {
      CustomerDetailOffice
    },
    mixins: [ CustomerCols ], //項目名定数
    props: {
      customer: Object,
    },
    data: function() {
      return {
        delCheck: false,   //削除確認フラグ
      }
    },
    methods:{
      deleteCustomer: function() {
      axios.post("/shokunin/api/deleteCustomer", {
          id: this.customer.id,
        })
        .then(res => {
          this.$emit("customerDeleted");
          this.$emit("detailClose");
          console.log("deleted");
        });
      },
      delYes() {
        this.delCheck = false;
        this.deleteCustomer();
      },
      delNo() {
        this.delCheck = false;
      },
      openDelModal() {
        this.delCheck = true;
      },
      closeDelModal() {
        this.delCheck = false;
      },
    },
  }
</script>

<style scoped>
  .customer-detail-modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, .5);
    display: table;
    transition: opacity .3s ease;
  }
  .modal-wrapper {
    display: table-cell;
    vertical-align: middle;
  }
  .modal-container {
    width: 70%;
    height: 80%;
    overflow: scroll;
    margin: 0px auto;
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
    font-family: Helvetica, Arial, sans-serif;
  }
</style>