<template>
  <div>
    <ul class="content-contoff-Browse">
      <div>
        <li class="info1">総案件数</li>
        <li class="info2">{{projectCount.allCount}}</li><li class="info3">件</li>
      </div>
      <div>
        <li class="info1">月毎件数</li>
        <li class="info2">{{projectCount.monthCount}}</li><li class="info3">件</li>
      </div>
     <div>
       <li class="info1">日毎件数</li>
       <li class="info2">{{projectCount.dayCount}}</li><li class="info3">件</li>
     </div>
    </ul>
    <h3>進歩状況図</h3>
    <ECharts ref="chart1" :options="orgOptions" :auto-resize="true" class="can-rate"></ECharts>
  </div>
</template>

<script>
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    import ECharts from 'vue-echarts'
    import 'echarts/lib/chart/pie'
    import 'echarts/lib/chart/bar'
    import 'echarts/lib/component/legend'
    export default {
        name: "ContractBrowse",
        components: {
            ECharts
        },
        mixins: [Messages,Calendar],
        props: [],
        data() {
            return {
                projectCount:{},
                orgOptions:{},
                status:{},
            }
        },
        methods: {
            fetch: function () {
                // const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/projectCount',{
                        enterpriseId : this.$route.query.enterprise_id,
                }).then((res) => {
                    this.projectCount = res.data;
                    this.status=this.projectCount.progress_status;
                    this.drawLine(this.status);
                    //loading.close();

                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    //loading.close();
                });
                // loading.close();
            },


            drawLine:function(status){
                // window.console.log( status);
                this.orgOptions = {

                    yAxis: {
                      type: 'category',
                      data: ['受注前', '着工前', '順調', '多少の遅れ', '大幅遅れ'],
                      axisLine: {
                        show: false
                      },
                      axisTick: {
                        show: false
                      },
                      splitLine: {
                        show: false
                      },
                    },
                    grid: {
                        left: '12%',
                    },
                    xAxis: {
                        type: 'value',
                        axisLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        splitLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false,

                        },
                    },
                    series: [{
                        data: [this.status[1], this.status[2], this.status[4], this.status[5], this.status[6]],
                        type: 'bar',
                        smooth: true,
                        barWidth: 20,
                        itemStyle: {
                            normal: {
                                color: '#AACC32',
                            }
                        },
                    }]
                }
            }
        },
        created() {
        },
        watch: {

        },
        mounted() {
            this.fetch();
        }
    }
</script>
