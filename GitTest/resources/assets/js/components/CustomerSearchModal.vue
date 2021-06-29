<template>
  <transition name="customer-search-modal">
    <div class="customer-search-modal-mask">
      <div class="modal-wrapper" @click.self="$emit('searchClose')">
        <div class="modal-container">
          <form>
            <div class="form-group">
              <label for="search-type">検索種別:</label>
              <input type="radio" class="form-radio" id="radio-and" value='AND' v-model="searchForm.searchType">
              <label for="search-type">AND検索</label>
              <input type="radio" class="form-radio" id="radio-or" value='OR' v-model="searchForm.searchType">
              <label for="search-type">OR検索</label>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="name-check" v-model="searchForm.enableFlg.customer.name">
              <label for="customer-name">{{ customersCols.find(key => key.col === 'name').name }}</label>
              <input type="text" class="form-control" id="customer-name" v-model="searchForm.customer.name" :disabled='!searchForm.enableFlg.customer.name'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="phonetic-check" v-model="searchForm.enableFlg.customer.phonetic">
              <label for="customer-phonetic">{{ customersCols.find(key => key.col === 'phonetic').name }}</label>
              <input type="text" class="form-control" id="customer-phonetic" v-model="searchForm.customer.phonetic" :disabled='!searchForm.enableFlg.customer.phonetic'>
            </div>
            <div>------------------------------------------------------------------------</div>
            事業所
            <div>------------------------------------------------------------------------</div>

          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-name-check" v-model="searchForm.enableFlg.customer.office.name">
            <label for="office-name">{{ officesCols.find(key => key.col === 'name').name }}</label>
            <input type="text" class="form-control" id="office-name" v-model="searchForm.customer.office.name" :disabled='!searchForm.enableFlg.customer.office.name'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-zip-check" v-model="searchForm.enableFlg.customer.office.zip">
            <label for="office-zip">{{ officesCols.find(key => key.col === 'zip').name }}</label>
            <input type="text" class="form-control" id="office-zip" v-model="searchForm.customer.office.zip" :disabled='!searchForm.enableFlg.customer.office.zip'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-pref-check" v-model="searchForm.enableFlg.customer.office.pref">
            <label for="office-pref">{{ officesCols.find(key => key.col === 'pref').name }}</label>
            <input type="text" class="form-control" id="office-pref" v-model="searchForm.customer.office.pref" :disabled='!searchForm.enableFlg.customer.office.pref'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-town-check" v-model="searchForm.enableFlg.customer.office.town">
            <label for="office-town">{{ officesCols.find(key => key.col === 'town').name }}</label>
            <input type="text" class="form-control" id="office-town" v-model="searchForm.customer.office.town" :disabled='!searchForm.enableFlg.customer.office.town'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-street-check" v-model="searchForm.enableFlg.customer.office.street">
            <label for="office-street">{{ officesCols.find(key => key.col === 'street').name }}</label>
            <input type="text" class="form-control" id="office-street" v-model="searchForm.customer.office.street" :disabled='!searchForm.enableFlg.customer.office.street'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-house-check" v-model="searchForm.enableFlg.customer.office.house">
            <label for="office-house">{{ officesCols.find(key => key.col === 'house').name }}</label>
            <input type="text" class="form-control" id="office-house" v-model="searchForm.customer.office.house" :disabled='!searchForm.enableFlg.customer.office.house'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-tel-check" v-model="searchForm.enableFlg.customer.office.tel">
            <label for="office-tel">{{ officesCols.find(key => key.col === 'tel').name }}</label>
            <input type="text" class="form-control" id="office-tel" v-model="searchForm.customer.office.tel" :disabled='!searchForm.enableFlg.customer.office.tel'>
          </div>
          <div class="form-group">
            <input type="checkbox" class="form-check" id="office-fax-check" v-model="searchForm.enableFlg.customer.office.fax">
            <label for="office-fax">{{ officesCols.find(key => key.col === 'fax').name }}</label>
            <input type="text" class="form-control" id="office-fax" v-model="searchForm.customer.office.fax" :disabled='!searchForm.enableFlg.customer.office.fax'>
          </div>
            <div>------------------------------------------------------------------------</div>
            担当者
            <div>------------------------------------------------------------------------</div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="people-name-check" v-model="searchForm.enableFlg.customer.office.people.name">
              <label for="people-name">{{ peopleCols.find(key => key.col === 'name').name }}</label>
              <input type="text" class="form-control" id="people-name" v-model="searchForm.customer.office.people.name" :disabled='!searchForm.enableFlg.customer.office.people.name'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="people-position-check" v-model="searchForm.enableFlg.customer.office.people.position">
              <label for="people-position">{{ peopleCols.find(key => key.col === 'position').name }}</label>
              <input type="text" class="form-control" id="people-position" v-model="searchForm.customer.office.people.position" :disabled='!searchForm.enableFlg.customer.office.people.position'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="people-dept-check" v-model="searchForm.enableFlg.customer.office.people.dept">
              <label for="people-dept">{{ peopleCols.find(key => key.col === 'dept').name }}</label>
              <input type="text" class="form-control" id="people-dept" v-model="searchForm.customer.office.people.dept" :disabled='!searchForm.enableFlg.customer.office.people.dept'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="people-role-check" v-model="searchForm.enableFlg.customer.office.people.role">
              <label for="people-role">{{ peopleCols.find(key => key.col === 'role').name }}</label>
              <input type="text" class="form-control" id="people-role" v-model="searchForm.customer.office.people.role" :disabled='!searchForm.enableFlg.customer.office.people.role'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="people-email-check" v-model="searchForm.enableFlg.customer.office.people.email">
              <label for="people-email">{{ peopleCols.find(key => key.col === 'email').name }}</label>
              <input type="text" class="form-control" id="people-email" v-model="searchForm.customer.office.people.email" :disabled='!searchForm.enableFlg.customer.office.people.email'>
            </div>
            <div>------------------------------------------------------------------------</div>
            請求先
            <div>------------------------------------------------------------------------</div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-name-check" v-model="searchForm.enableFlg.customer.office.billing.name">
              <label for="billing-name">{{ billingsCols.find(key => key.col === 'name').name }}</label>
              <input type="text" class="form-control" id="billing-name" v-model="searchForm.customer.office.billing.name" :disabled='!searchForm.enableFlg.customer.office.billing.name'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-zip-check" v-model="searchForm.enableFlg.customer.office.billing.zip">
              <label for="billing-zip">{{ billingsCols.find(key => key.col === 'zip').name }}</label>
              <input type="text" class="form-control" id="billing-zip" v-model="searchForm.customer.office.billing.zip" :disabled='!searchForm.enableFlg.customer.office.billing.zip'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-pref-check" v-model="searchForm.enableFlg.customer.office.billing.pref">
              <label for="billing-pref">{{ billingsCols.find(key => key.col === 'pref').name }}</label>
              <input type="text" class="form-control" id="billing-pref" v-model="searchForm.customer.office.billing.pref" :disabled='!searchForm.enableFlg.customer.office.billing.pref'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-town-check" v-model="searchForm.enableFlg.customer.office.billing.town">
              <label for="billing-town">{{ billingsCols.find(key => key.col === 'town').name }}</label>
              <input type="text" class="form-control" id="billing-town" v-model="searchForm.customer.office.billing.town" :disabled='!searchForm.enableFlg.customer.office.billing.town'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-street-check" v-model="searchForm.enableFlg.customer.office.billing.street">
              <label for="billing-street">{{ billingsCols.find(key => key.col === 'street').name }}</label>
              <input type="text" class="form-control" id="billing-street" v-model="searchForm.customer.office.billing.street" :disabled='!searchForm.enableFlg.customer.office.billing.street'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-house-check" v-model="searchForm.enableFlg.customer.office.billing.house">
              <label for="billing-house">{{ billingsCols.find(key => key.col === 'house').name }}</label>
              <input type="text" class="form-control" id="billing-house" v-model="searchForm.customer.office.billing.house" :disabled='!searchForm.enableFlg.customer.office.billing.house'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-tel-check" v-model="searchForm.enableFlg.customer.office.billing.tel">
              <label for="billing-tel">{{ billingsCols.find(key => key.col === 'tel').name }}</label>
              <input type="text" class="form-control" id="billing-tel" v-model="searchForm.customer.office.billing.tel" :disabled='!searchForm.enableFlg.customer.office.billing.tel'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-fax-check" v-model="searchForm.enableFlg.customer.office.billing.fax">
              <label for="billing-fax">{{ billingsCols.find(key => key.col === 'fax').name }}</label>
              <input type="text" class="form-control" id="billing-fax" v-model="searchForm.customer.office.billing.fax" :disabled='!searchForm.enableFlg.customer.office.billing.fax'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-people_name-check" v-model="searchForm.enableFlg.customer.office.billing.people_name">
              <label for="billing-name">{{ billingsCols.find(key => key.col === 'people_name').name }}</label>
              <input type="text" class="form-control" id="billing-name" v-model="searchForm.customer.office.billing.people_name" :disabled='!searchForm.enableFlg.customer.office.billing.people_name'>
            </div>
            <div class="form-group">
              <input type="checkbox" class="form-check" id="billing-dept-check" v-model="searchForm.enableFlg.customer.office.billing.dept">
              <label for="billing-dept">{{ billingsCols.find(key => key.col === 'dept').name }}</label>
              <input type="text" class="form-control" id="billing-dept" v-model="searchForm.customer.office.billing.dept" :disabled='!searchForm.enableFlg.customer.office.billing.dept'>
            </div>
            <button @click.prevent="searchSubmit">
              検索
            </button>
            <button @click="$emit('searchClose')">
              閉じる
            </button>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
export default {
    mixins: [ CustomerCols ], //項目名定数
    props: {
      searchArray: Object,
    },
    data: function() {
      return {
        searchForm: {
          searchType: 'AND', //検索種別
          customer:{
            name: '',
            phonetic: '',
            office: {
              name: '',
              zip: '',
              pref: '',
              town: '',
              street: '',
              house: '',
              tel: '',
              fax: '',
              people: {
                name: '',
                position: '',
                dept: '',
                role: '',
                email: '',
              },
              billing: {
                name: '',
                zip: '',
                pref: '',
                town: '',
                street: '',
                house: '',
                tel: '',
                fax: '',
                people_name: '',
                position: '',
                dept: '',
                email: '',
              },
            }
          },
          enableFlg:{
            customer:{
              name: false,
              phonetic: false,
              office: {
                name: false,
                zip: false,
                pref: false,
                town: false,
                street: false,
                house: false,
                tel: false,
                fax: false,
                people: {
                  name: false,
                  position: false,
                  dept: false,
                  role: false,
                  email: false,
                },
                billing: {
                  name: false,
                  zip: false,
                  pref: false,
                  town: false,
                  street: false,
                  house: false,
                  tel: false,
                  fax: false,
                  people_name: false,
                  position: false,
                  dept: false,
                  email: false,
                },
              }
            }
          }
        }
      }
    },
    methods:{
      searchSubmit: function() {
        this.searchForm;
        this.$emit('searchSubmit',this.searchForm);
      },
    },
    created(){
      if(this.searchArray)
        this.searchForm = this.searchArray;
    }
  }
</script>

<style scoped>
  .customer-search-modal-mask {
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