<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="back">×</div>
        <div class="modalBodycontent commonMol project-trades">
          <div class="common-deteil-wrap report-deteil-wrap clearfix">
            <div class="commonModelAdd"><span>{{projectName('tradesChiefAdd')}}</span></div>
            <el-form :model="chief" :rules="tradesChiefRules" ref="form" label-width="200px" class="commonForm">
              <div class="trades-type-detail">
                <el-form-item prop="trades_type">
                  <el-select v-model="chief.trades_type" :placeholder="tradesChiefName('trades_type')" @change="selectChange">
                    <el-option v-for="type in tradesType" :key="type.id" :label="type.name"
                               :value="type.id"></el-option>
                  </el-select>
                </el-form-item>
                <el-form-item class="form-item-detail" v-if="isDisabled">
                  <el-input v-model="chief.trades_type_detail"
                            :placeholder="tradesChiefName('trades_type_detail')" maxlength="20"></el-input>
                </el-form-item>
              </div>
              <el-form-item>
                <el-input v-model="chief.company" :placeholder="tradesChiefName('company')" maxlength="30"></el-input>
              </el-form-item>
              <el-form-item prop="name">
                <el-input v-model="chief.name" :placeholder="tradesChiefName('name')" maxlength="30"></el-input>
              </el-form-item>
              <el-form-item prop="tel">
                <el-input v-model="chief.tel" :placeholder="tradesChiefName('tel')" maxlength="15"></el-input>
              </el-form-item>
            </el-form>
            <div class="modelButton">
              <a @click="addChief">追加</a>
            </div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import ProjectLists from '../../mixins/ProjectLists'
  import validation from '../../validations/project.js'

  export default {
    name: "ProjectTradesChiefModal",
    mixins: [
      validation,
      ProjectLists,
    ],
    data: function () {
      return {
        isDisabled: false,
        isMounted: false,
        chief: {},
      }
    },
    methods: {
      // 工種別責任者追加
      addChief: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$emit("addTradesChief", this.chief);
          }
        });
      },
        selectChange: function() {
          if(this.chief.trades_type == 5) {
              this.isDisabled = true;
          }else {
              this.isDisabled = false;
              this.chief.trades_type_detail =""
          }
        },
      //工種別責任者モデルを閉め
      back: function () {
        this.$emit("addTradesChief", "");
      }
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
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>