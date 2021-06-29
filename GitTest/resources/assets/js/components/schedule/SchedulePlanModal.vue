<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="schedule-add-wrap clearfix">
          <el-form :model="plan" :rules="planRules" ref="form" label-width="200px">
            <el-form-item :label="planName('repeatKbn')" prop="repeat_kbn">
              <el-select v-model="plan.repeat_kbn">
                <el-option v-for="repeat in repeatKbnCols" :key="repeat.id" :label="repeat.name"
                           :value="repeat.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item :label="planName('interval')" v-if="plan.repeat_kbn==='4'">
              <el-select v-model="plan.interval">
                <el-option v-for="repeat in repeatKbnCols" :key="repeat.id" :label="repeat.name"
                           :value="repeat.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item :label="planName('week')" v-if="plan.repeat_kbn==='4'">
              <el-checkbox v-for="week in weeks" :key="week.id" @change="checkWeek(week.id)"
                           :checked="week.isChecked">{{week.name}}
              </el-checkbox>
            </el-form-item>
            <el-form-item :label="planName('stSpan')" prop="st_span">
              <el-date-picker type="date" v-model="plan.st_span" value-format="yyyy-MM-dd"></el-date-picker>
            </el-form-item>
            <el-form-item :label="'※'+planName('edSpan')" prop="ed_span">
              <el-radio-group v-model="plan.edCheck" @change="checkEdDate">
                <el-radio :label="1">{{planName('times')}}
                  <el-input style="width:195px" v-model="plan.times" :disabled="plan.edCheck===2"></el-input>
                </el-radio>
                <br>
                <el-radio :label="2">{{planName('date')}}</el-radio>
                <el-date-picker type="date" v-model="plan.ed_span" value-format="yyyy-MM-dd"
                                :disabled="plan.edCheck===1"></el-date-picker>
              </el-radio-group>
            </el-form-item>
          </el-form>
          <div class="clearfix"></div>
          <div class="button-wrap clearfix" style="margin-left: 200px">
            <div class="button-lower"><a href="javascript:void(0)" @click="add">OK</a></div>
            <div class="button-lower remark"><a href="javascript:void(0)" @click="back">{{scheduleName('cancel')}}</a>
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
  import ScheduleLists from '../../mixins/ScheduleLists'
  import validation from '../../validations/schedule.js'

  export default {
    name: "SchedulePlanModal",
    mixins: [
      validation,
      ScheduleLists,
    ],
    props: {
      planObj: Object,
      reOpen: Boolean,
    },
    data: function () {
      return {
        isMounted: false,
        plan: {
          repeat_kbn: '',
          interval: '',
          st_span: '',
          times: '',
          ed_span: '',
          edCheck: 1,
          checkWeekArr: [],
        },
        weeks: [],
      }
    },
    methods: {
      add: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$emit('planDataBack', this.plan);
          }
        });
      },
      back: function () {
        this.$emit('planDataBack');
      },
      closeDelModal() {
        this.delCheck = false;
      },
      checkWeek(index) {
        this.plan.checkWeekArr[index - 1] = !this.plan.checkWeekArr[index - 1];
      },
      checkEdDate() {
        if (this.plan.edCheck === 1) {
          this.plan.ed_span = '';
        } else {
          this.plan.times = '';
        }
      },
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
      this.weeks = this.weekCols;
      this.isMounted = true;
      this.showPlan = false;
      if (this.reOpen) {
        this.plan = JSON.parse(JSON.stringify(this.planObj));
        for (let i = 0; i < 7; i++) {
          if (this.plan.checkWeekArr[i]) {
            this.weeks[i].isChecked = true;
          } else {
            this.weeks[i].isChecked = false;
          }
        }
      } else {
        for (let i = 0; i < 7; i++) {
          this.weeks[i].isChecked = false;
        }
      }
    },
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>