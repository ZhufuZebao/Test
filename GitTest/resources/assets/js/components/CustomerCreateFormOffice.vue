<template>
  <div>
        <div>------------------------------------------------------------------------</div>
          <div class="form-group">
            <label for="office-name">{{ officesCols.find(key => key.col === 'name').name }}</label>
            <input type="text" class="form-control" id="office-name" v-model="$props.office.name">
          </div>
          <div class="form-group">
            <label for="office-zip">{{ officesCols.find(key => key.col === 'zip').name }}</label>
            <input type="text" class="form-control" id="office-zip" v-model="$props.office.zip" @input="zipSearch">
          </div>
          <div class="form-group">
            <label for="office-pref">{{ officesCols.find(key => key.col === 'pref').name }}</label>
            <input type="text" class="form-control" id="office-pref" v-model="$props.office.pref">
          </div>
          <div class="form-group">
            <label for="office-town">{{ officesCols.find(key => key.col === 'town').name }}</label>
            <input type="text" class="form-control" id="office-town" v-model="$props.office.town">
          </div>
          <div class="form-group">
            <label for="office-street">{{ officesCols.find(key => key.col === 'street').name }}</label>
            <input type="text" class="form-control" id="office-street" v-model="$props.office.street">
          </div>
          <div class="form-group">
            <label for="office-house">{{ officesCols.find(key => key.col === 'house').name }}</label>
            <input type="text" class="form-control" id="office-house" v-model="$props.office.house">
          </div>
          <div class="form-group">
            <label for="office-tel">{{ officesCols.find(key => key.col === 'tel').name }}</label>
            <input type="text" class="form-control" id="office-tel" v-model="$props.office.tel">
          </div>
          <div class="form-group">
            <label for="office-fax">{{ officesCols.find(key => key.col === 'fax').name }}</label>
            <input type="text" class="form-control" id="office-fax" v-model="$props.office.fax">
          </div>
          <div>------------------------------------------------------------------------</div>
          <ul>
            <FormOfficePerson v-for="(person,index) in $props.office.people" v-bind:person="person" :key="index" @delPerson="delPerson(index)"/>
          </ul>
          <button type="button" class="btn btn-addPerson" @click.prevent="$emit('addPerson')">担当者追加</button>
          <div>------------------------------------------------------------------------</div>
          <ul>
            <FormOfficeBilling v-for="(billing,index) in $props.office.billings" v-bind:billing="billing" :key="index" @delBilling="delBilling(index)"/>
          </ul>
          <button type="button" class="btn btn-addBilling" @click.prevent="$emit('addBilling')">請求先追加</button>
          <button type="button" class="btn btn-delOffice" @click.prevent="$emit('delOffice')">事業所削除</button>
  </div>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
import FormOfficePerson from './CustomerCreateFormOfficePerson.vue'
import FormOfficeBilling from './CustomerCreateFormOfficeBilling.vue'
export default {
  components: {
    FormOfficePerson,
    FormOfficeBilling,
  },
  mixins: [ CustomerCols ],  //項目名定数
  props:['office','officeIndex'],
  methods: {
    delPerson: function($index){
      this.$emit('delPerson',[this.officeIndex,$index]);
    },
    delBilling: function($index){
      this.$emit('delBilling',[this.officeIndex,$index]);
    },
    zipSearch: function(){
      axios.get('/shokunin/api/getZip?zipcode=' + this.office.zip,).then((res)=>{
        if(res.data.status==200){
          this.office.pref = res.data.results[0].address1;
          this.office.town = res.data.results[0].address2 + res.data.results[0].address3;
        }
      });
    },
  },
  created() {
    if(this.office.id){
    }else{
      this.$emit('addPerson');
      this.$emit('addBilling');
    }
  }
};
</script>