<template>
  <div>
          <div class="form-group">
            <label for="billing-name">請求先は上記施主情報と同じ</label>
            <input type="checkbox" class="form-control" id="billing-check" v-model="$props.billing.equal" @change="changeEqual">
          </div>
          <div class="form-group">
            <label for="billing-name">{{ billingsCols.find(key => key.col === 'name').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-name" v-model="$parent.office.name" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-name" v-model="$props.billing.name">
          </div>
          <div class="form-group">
            <label for="billing-zip">{{ billingsCols.find(key => key.col === 'zip').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-zip" v-model="$parent.office.zip" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-zip" v-model="$props.billing.zip" @input="zipSearch">
          </div>
          <div class="form-group">
            <label for="billing-pref">{{ billingsCols.find(key => key.col === 'pref').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-pref" v-model="$parent.office.pref" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-pref" v-model="$props.billing.pref">
          </div>
          <div class="form-group">
            <label for="billing-town">{{ billingsCols.find(key => key.col === 'town').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-town" v-model="$parent.office.town" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-town" v-model="$props.billing.town">
          </div>
          <div class="form-group">
            <label for="billing-street">{{ billingsCols.find(key => key.col === 'street').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-street" v-model="$parent.office.street" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-street" v-model="$props.billing.street">
          </div>
          <div class="form-group">
            <label for="billing-house">{{ billingsCols.find(key => key.col === 'house').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-house" v-model="$parent.office.house" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-house" v-model="$props.billing.house">
          </div>
          <div class="form-group">
            <label for="billing-tel">{{ billingsCols.find(key => key.col === 'tel').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-tel" v-model="$parent.office.tel" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-tel" v-model="$props.billing.tel">
          </div>
          <div class="form-group">
            <label for="billing-fax">{{ billingsCols.find(key => key.col === 'fax').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-fax" v-model="$parent.office.fax" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-fax" v-model="$props.billing.fax">
          </div>
          <div class="form-group">
            <label for="billing-name">{{ billingsCols.find(key => key.col === 'people_name').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-name" v-model="$parent.office.people[0].name" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-name" v-model="$props.billing.people_name">
          </div>
          <div class="form-group">
            <label for="billing-dept">{{ billingsCols.find(key => key.col === 'dept').name }}</label>
            <input v-if="$props.billing.equal" type="text" class="form-control" id="billing-dept" v-model="$parent.office.people[0].dept" :disabled="true">
            <input v-else type="text" class="form-control" id="billing-dept" v-model="$props.billing.dept">
          </div>
          <button type="button" class="btn btn-delBilling" @click.prevent="$emit('delBilling')">請求先削除</button>
  </div>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
export default {
  mixins: [ CustomerCols ],  //項目名定数
  props:['billing'],
  methods: {
    changeEqual: function(){
      if(this.billing.equal){
        this.billing.name = this.$parent.office.name;
        this.billing.zip = this.$parent.office.zip;
        this.billing.pref = this.$parent.office.pref;
        this.billing.town = this.$parent.office.town;
        this.billing.street = this.$parent.office.street;
        this.billing.house = this.$parent.office.house;
        this.billing.tel = this.$parent.office.tel;
        this.billing.fax = this.$parent.office.fax;
        this.billing.people_name = this.$parent.office.people[0].name;
        this.billing.dept = this.$parent.office.people[0].dept;
      }
    },
    zipSearch: function(){
      axios.get('/shokunin/api/getZip?zipcode=' + this.billing.zip,).then((res)=>{
        if(res.data.status==200){
          this.billing.pref = res.data.results[0].address1;
          this.billing.town = res.data.results[0].address2 + res.data.results[0].address3;
        }
      });
    },
  },
};
</script>