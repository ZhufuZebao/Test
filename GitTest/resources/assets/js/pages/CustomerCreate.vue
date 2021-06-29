<template>
  <section class="customer-wrapper">
    <header>
      <h1>
        <router-link to="/customer">施主</router-link>
      </h1>

      <div class="title-wrap">
        <h2 v-if="$route.name === 'edit'">施主編集</h2>
        <h2 v-else>施主登録</h2>
      </div>
    </header>
    <div class="customer-deteil-wrap clearfix">
      <div class="l-wrapper">
        <div class="modal-window">
          <CommonModal v-if="saved">
            <template>
              <p>
                登録が完了しました
                <br>続けて、案件登録を行いますか？
              </p>
              <button @click="doYes">はい</button>
              <button @click="doNo">いいえ</button>
            </template>
          </CommonModal>
        </div>
        <form>
          <div class="form-group">
            <label for="customer-name">{{ customersCols.find(key => key.col === 'name').name }}</label>
            <input type="text" class="form-control" id="customer-name" v-model="customer.name">
          </div>
          <div class="form-group">
            <label for="customer-phonetic">{{ customersCols.find(key => key.col === 'phonetic').name }}</label>
            <input type="text" class="form-control" id="customer-phonetic" v-model="customer.phonetic">
          </div>
          <div>------------------------------------------------------------------------</div>
          <ul>
            <FormOffice v-for="(office,index) in customer.offices" v-bind:office="office" v-bind:officeIndex="index" :key="index"
              @addPerson="addPerson(index)"
              @addBilling="addBilling(index)"
              @delOffice="delOffice(index)"
              @delPerson="delPerson"
              @delBilling="delBilling"
            />
          </ul>
          <button type="button" class="btn btn-addoffice" @click.prevent="addOffice">事業所追加</button>
          <button v-if="$route.name=='edit'" type="submit" class="btn btn-primary" @click.prevent="edit">更新</button>
          <button v-else type="submit" class="btn btn-primary" @click.prevent="create">登録</button>
          <button
            type="button"
            class="btn btn-cancelButtonClick"
            @click.prevent="cancelButtonClick"
          >戻る</button>
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
import FormOffice from '../components/customer/CustomerCreateFormOffice.vue'
export default {
    name: 'CustomerCreate',
    mixins: [ CustomerCols ], //項目名定数
    components: {
      FormOffice
    },
    data: function() {
    return {
      customer:{
        offices:[]
      },
      name: "", 
      saved: false,
    };
  },
  methods: {
    create: function() {
      this.billingEqual();
      axios
        .post("/shokunin/api/setCustomer", {
          name: this.customer.name,
          phonetic: this.customer.phonetic,
          offices: this.customer.offices
        })
        .then(res => {
          this.saved = true;
          this.$emit("customerCreated");
          this.$emit("crateClose");
        });
    },
    edit: function() {
      this.billingEqual();
      axios
        .post("/shokunin/api/editCustomer", {
          id: this.customer.id,
          name: this.customer.name,
          phonetic: this.customer.phonetic,
          offices: this.customer.offices
        })
        .then(res => {
          this.saved = true;
          this.$emit("customerCreated");
          this.$emit("crateClose");
        });
    },
    addOffice: function() {
      this.customer.offices.push({
          name: "",
          zip: "",
          pref: "",
          town: "",
          street: "",
          house: "",
          tel: "",
          fax: "",
          people:[],
          billings:[],
      });
    },
    delOffice: function($index) {
      if(this.customer.offices.length > 1){
        this.customer.offices.splice($index,1);
      }else{
        //事業所が一つしかない場合の削除エラー
      };
    },
    addPerson: function($index) {
      this.customer.offices[$index].people.push({
            name: "",
            position: "",
            dept: "",
            role: "",
            email: "",
        });
    },
    delPerson: function($index) {
      var officesIndex = $index[0];
      var peopleIndex = $index[1];
      if(this.customer.offices[officesIndex].people.length > 1){
        this.customer.offices[officesIndex].people.splice(peopleIndex,1);
      }else{
        //一つしかない場合の削除エラー
      };
    },
    addBilling: function($index) {
      this.customer.offices[$index].billings.push({
            name: "",
            zip: "",
            pref: "",
            town: "",
            street: "",
            house: "",
            tel: "",
            fax: "",
            people_name: "",
            dept: "",
            equal: false
        });
    },
    delBilling: function($index) {
      var officesIndex = $index[0];
      var billingsIndex = $index[1];
      if(this.customer.offices[officesIndex].billings.length > 1){
        this.customer.offices[officesIndex].billings.splice(billingsIndex,1);
      }else{
        //一つしかない場合の削除エラー
      };
    },
    cancelButtonClick: function() {
      this.$router.go(-1); 
    },
    fetchCustomerDetail: function() {
      axios
        .post("/shokunin/api/getCustomerDetail", {
          id: this.$route.params.id
        })
        .then(res => {
          this.customer = res.data;
        });
    },
    billingEqual: function(){
      this.customer.offices.forEach(office => {
        office.billings.forEach(billing => {
          if(billing.equal){
            billing.name = office.name;
            billing.zip = office.zip;
            billing.pref = office.pref;
            billing.town = office.town;
            billing.street = office.street;
            billing.house = office.house;
            billing.tel = office.tel;
            billing.fax = office.fax;
            billing.people_name = office.people[0].name;
            billing.dept = office.people[0].dept;
          }
        });
      });
    },
    doYes() {
      this.saved = false;
      this.$router.push({ path: "/project" });
    },
    doNo() {
      this.saved = false;
      this.$router.push({ path: "/customer" });
    },
  },
  created() {
    if(this.$route.name=='edit'){
      this.fetchCustomerDetail();
    }else{
      this.addOffice();
    }
  }
  }
</script>