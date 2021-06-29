<template>
  <div class="accordion-target" v-if='accordion'>
    <button type="button" class="accordion--trigger" @click.prevent="closeAccordion">▼</button>
    <ul>
      <div >------------------------------------------------------------------------</div>
      <li>{{ officesCols.find(key => key.col === 'name').name }}：{{ office.name }}</li>
      <li>{{ officesCols.find(key => key.col === 'zip').name }}：{{ office.zip }}</li>
      <li>{{ officesCols.find(key => key.col === 'pref').name }}：{{ office.pref }}</li>
      <li>{{ officesCols.find(key => key.col === 'town').name }}：{{ office.town }}</li>
      <li>{{ officesCols.find(key => key.col === 'street').name }}：{{ office.street }}</li>
      <li>{{ officesCols.find(key => key.col === 'house').name }}：{{ office.house }}</li>
      <li>{{ officesCols.find(key => key.col === 'tel').name }}：{{ office.tel }}</li>
      <li>{{ officesCols.find(key => key.col === 'fax').name }}：{{ office.fax }}</li>
      <ul v-for="person in office.people" v-bind:key="person.id">
        <div >------------------------------------------------------------------------</div>
        <div class="modal-container">
        <div class="report-deteil-wrap clearfix">
        <dt class="no icon-s"></dt>
          <dd><li>{{ peopleCols.find(key => key.col === 'name').name }}：{{ person.name }}</li></dd>

        <dt class="no icon-s"></dt>
          <dd><li>{{ peopleCols.find(key => key.col === 'position').name }}：{{ person.position }}</li></dd>
        <dt class="no icon-s"></dt>
          <dd><li>{{ peopleCols.find(key => key.col === 'dept').name }}：{{ person.dept }}</li></dd>
        <dt class="no icon-s"></dt>
          <dd><li>{{ peopleCols.find(key => key.col === 'role').name }}：{{ person.role }}</li></dd>
        </div>
        </div>
      </ul>
      <ul v-for="billing in office.billings" v-bind:key="billing.id">
        <div >------------------------------------------------------------------------</div>
        <li>{{ billingsCols.find(key => key.col === 'name').name }}：{{ billing.name }}</li>
        <li>{{ billingsCols.find(key => key.col === 'zip').name }}：{{ billing.zip }}</li>
        <li>{{ billingsCols.find(key => key.col === 'pref').name }}：{{ billing.pref }}</li>
        <li>{{ billingsCols.find(key => key.col === 'town').name }}：{{ billing.town }}</li>
        <li>{{ billingsCols.find(key => key.col === 'street').name }}：{{ billing.street }}</li>
        <li>{{ billingsCols.find(key => key.col === 'house').name }}：{{ billing.house }}</li>
        <li>{{ billingsCols.find(key => key.col === 'tel').name }}：{{ billing.tel }}</li>
        <li>{{ billingsCols.find(key => key.col === 'fax').name }}：{{ billing.fax }}</li>
        <li>{{ billingsCols.find(key => key.col === 'people_name').name }}：{{ billing.people_name }}</li>
        <li>{{ billingsCols.find(key => key.col === 'dept').name }}：{{ billing.dept }}</li>
      </ul>
    </ul>
  </div>
  <div v-else class="accordion-target" >
    <button type="button" class="accordion--trigger" @click.prevent="openAccordion">▶</button>
    <li>{{ office.name }}</li>
    <li>{{ office.pref }}{{ office.town }}{{ office.street }}{{ office.house }}</li>
    <li>{{ office.tel }}</li>
  </div>

</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
export default {
    name: 'CustomerDetailModal',
    mixins: [ CustomerCols ], //項目名定数
    props: {
      office: Object,
    },
    data: function() {
      return {
        accordion: false,  //アコーディオン展開フラグ
      }
    },
    methods:{
      openAccordion: function(){
        this.accordion = true;
      },
      closeAccordion: function(){
        this.accordion = false;
      }
    },
  }
</script>