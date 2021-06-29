<template>
  <transition name="fade">
    <div class="manage-model modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-deteil-wrap report-deteil-wrap clearfix">
            <div class="title-wrap ">
              <h2>{{project.id}}&nbsp;&nbsp;{{project.place_name}}</h2>
              <div class="button-s" @click="$emit('closeModal')">案件一覧へ</div>
            </div>
            <section class="process-wrapper one-year">
              <div class="process-title-wrap clearfix">
                <div class="task-name">タスク名</div>
                <div class="task-start-time">開始時間</div>
                <div class="task-days">日数</div>
                <div class="task-add"></div>
                <div class="process-sheet">
                  <div class="process-sheet-month">
                    <span>4月</span>
                    <span class=" thirty-first">5月</span>
                    <span>6月</span>
                  </div>
                  <div class="process-sheet-day">
                    <span class="sun">31</span>
                    <span>01</span>
                    <span>02</span>
                    <span>03</span>
                    <span>04</span>
                    <span>05</span>
                    <span class="sat">06</span>
                    <span class="sun">07</span>
                    <span>08</span>
                    <span>09</span>
                    <span>10</span>
                    <span>11</span>
                    <span>12</span>
                    <span class="sat">13</span>
                    <span class="sun">14</span>
                    <span>15</span>
                    <span>16</span>
                    <span>17</span>
                    <span>18</span>
                    <span>19</span>
                    <span class="sat">20</span>
                    <span class="sun">21</span>
                    <span>22</span>
                    <span>23</span>
                    <span>24</span>
                    <span>25</span>
                    <span>26</span>
                    <span class="sat">27</span>
                    <span class="sun">28</span>
                    <span>29</span>
                    <span>30</span>
                    <span>31</span>
                    <span>01</span>
                  </div>
                  <div class="process-sheet-week">
                    <span class="sun">日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                    <span>金</span>
                    <span class="sat">土</span>
                    <span class="sun">日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                    <span>金</span>
                    <span class="sat">土</span>
                    <span class="sun">日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                    <span>金</span>
                    <span class="sat">土</span>
                    <span class="sun">日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                    <span>金</span>
                    <span class="sat">土</span>
                    <span class="sun">日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                  </div>
                </div>
              </div>

              <div class="process-content-wrap clearfix">
                <div class="task-name">仮設工事</div>
                <div class="task-start-time">2018-06-01</div>
                <div class="task-days">4</div>
                <div class="task-add"></div>
                <div class="process-sheet clearfix">
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>

              <div class="process-content-wrap clearfix">
                <div class="task-name">仮設工事</div>
                <div class="task-start-time">2018-06-01</div>
                <div class="task-days">4</div>
                <div class="task-add"></div>
                <div class="process-sheet clearfix">
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>

              <div class="process-content-wrap clearfix">
                <div class="task-name">仮設工事</div>
                <div class="task-start-time">2018-06-01</div>
                <div class="task-days">4</div>
                <div class="task-add"></div>
                <div class="process-sheet clearfix">
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>

              <div class="process-content-wrap clearfix">
                <div class="task-name">仮設工事</div>
                <div class="task-start-time">2018-06-01</div>
                <div class="task-days">4</div>
                <div class="task-add"></div>
                <div class="process-sheet clearfix">
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>

              <div class="process-content-wrap clearfix">
                <div class="task-name">仮設工事</div>
                <div class="task-start-time">2018-06-01</div>
                <div class="task-days">4</div>
                <div class="task-add"></div>
                <div class="process-sheet clearfix">
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span class="sat"></span>
                  <span class="sun"></span>
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>

</template>
<script>
  export default {
    name: "ProjectManagerModal",
    props: {
      project: Object,
    },
    data: function () {
      return {}
    },
    methods: {
      closeDelModal() {
        this.delCheck = false;
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
    }
  }
</script>

<style scoped>
  .manage-model {
    display: block;
  }
</style>
