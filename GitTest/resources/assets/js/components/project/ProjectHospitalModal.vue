<template>
  <transition name="fade">
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="back">×</div>
        <div class="modalBodycontent commonMol project-hospital">
          <div class="common-deteil-wrap report-deteil-wrap clearfix">
            <div class="commonModelAdd"><span>{{projectName('hospitalAdd')}}</span></div>
            <el-form :model="hospital" :rules="hospitalRules" ref="form" label-width="200px" class="commonForm">
              <el-form-item prop="name">
                <el-input v-model="hospital.name" :placeholder="hospitalName('name')" maxlength="50"></el-input>
              </el-form-item>
              <el-form-item prop="tel">
                <el-input v-model="hospital.tel" :placeholder="hospitalName('tel')" maxlength="15"></el-input>
              </el-form-item>
            </el-form>
            <div class="modelButton">
              <a @click="addHospital">追加</a>
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
    name: "ProjectHospitalModal",
    mixins: [
      validation,
      ProjectLists,
    ],
    data: function () {
      return {
        isMounted: false,
        hospital: {
          name: '',
          tel: '',
        },
      }
    },
    methods: {
      //最寄病院追加
      addHospital: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$emit("addHospital", this.hospital);
          }
        });
      },
      //最寄病院モデルを閉め
      back: function () {
        this.$emit("addHospital", "");
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
      this.hospital.name = "";
      this.hospital.tel = "";
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>