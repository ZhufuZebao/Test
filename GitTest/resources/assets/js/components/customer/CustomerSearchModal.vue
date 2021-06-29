<template>
  <transition name="customer-search-modal">
    <div class="customer-search-modal-mask">
      <div class="modal-wrapper" @click.self="$emit('searchClose')">
        <div class="modal-customer-searchcontainer">
          <div class="modal-close" @click.prevent="$emit('searchClose')">×</div>
          <div class="modalBodycontent">
            <el-form :model="searchForm" ref="form">
              <h3>詳細検索</h3>
              <dl class="clearfix">
                <dd>
                  <el-radio-group v-model="searchForm.searchType">
                    <el-radio label="AND">AND</el-radio>
                    <el-radio label="OR">OR</el-radio>
                  </el-radio-group>
                </dd>
              </dl>
              <dl class="clearfix">
                <el-form-item>
                  <el-input :placeholder="customersName('name')" v-model="searchForm.customer.name"></el-input>
                </el-form-item>
                <el-form-item>
                  <el-input :placeholder="officesName('name')" v-model="searchForm.customer.office.name"></el-input>
                </el-form-item>
                <el-form-item>
                  <el-input :placeholder="billingsName('zip')"
                            v-model="searchForm.customer.office.billing.zip"></el-input>
                </el-form-item>
                <el-form-item>
                  <el-input :placeholder="billingsName('tel')"
                            v-model="searchForm.customer.office.billing.tel"></el-input>
                </el-form-item>
              </dl>
              <div class="button-lower remark" @click.prevent="$emit('searchSubmit', searchForm)"><a
                      href="javascript:void(0);">検索</a></div>
            </el-form>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
  import CustomerCols from '../../mixins/CustomerColsNew';

  export default {
    mixins: [CustomerCols], //項目名定数
    props: {
      searchArray: Object,
    },
    data: function () {
      return {
        searchForm: {
          searchType: 'AND', //検索種別
          customer: {
            office: {
              people: {},
              billing: {},
            }
          },
        }
      }
    },
    methods: {
      //帳票を検索して提出する
      searchSubmit: function () {
        this.searchForm;
        this.$emit('searchSubmit', this.searchForm);
      },
    },
    created() {
      if (this.searchArray){
        this.searchForm = this.searchArray;
      }
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
    margin: 0 auto;
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
    font-family: Helvetica, Arial, sans-serif;
  }
</style>