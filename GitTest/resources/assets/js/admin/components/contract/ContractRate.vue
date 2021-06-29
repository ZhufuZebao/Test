<template>
  <div class="content-ul" >
    <template>
      <div class="content-li">
        <span style="margin-right: 40px">機能名</span><span>使用頻度</span>

        <ECharts ref="chart1" :options="orgOptions" :auto-resize="true" class="can-rate"></ECharts>

      </div>
    </template>

  </div>
</template>


<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    import ECharts from 'vue-echarts'
    import 'echarts/lib/chart/pie'
    import 'echarts/lib/chart/bar'
    import 'echarts/lib/component/legend'

    export default {
        name: "ContractRate",
        components: {
            UserProfile, ECharts
        },
        mixins: [Messages,Calendar],
        data() {
            return {
                friendInformation: {},
                projectCount:{},
                orgOptions:{},
                status:{},
            }
        },
        methods: {
            fetch: function () {
                // const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.get('/api/getBrowse',{
                    params: {
                        enterpriseId : this.$route.query.enterprise_id,
                    }
                }).then((res) => {
                    this.projectCount = res.data;
                    this.status=this.projectCount.progress_status;
                    this.drawLine(this.status);
                    //  loading.close();

                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    // loading.close();
                });
                // loading.close();
            },

            drawLine:function(status){
                this.orgOptions = {
                    yAxis: {
                        type: 'category',
                        data: ['スケジュール', '案件', 'チャット', '仲間', '関係者','施主管理','プロフィール','アカウント管理'],
                        axisLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },

                    },
                    grid: {
                        left: '15%',
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
                            // textStyle: {
                            //     color: '#000',
                            // }
                        },
                    },
                    series: [{
                        data: [
                            this.status[0],this.status[1],this.status[2],this.status[3], this.status[4], this.status[5], this.status[6],this.status[7]
                        ],
                        // data: [
                        //     {name:'asas',value:100},
                        //     {name:'asas1',value:100},
                        //     {name:'asas2',value:100},
                        //     {name:'asas3',value:100},
                        //     {name:'asas4',value:100},
                        // ],
                        type: 'bar',
                        smooth: true,
                        barWidth: 20,
                        itemStyle: {
                            normal: {
                                color: '#AACC32',
                                label: {
                                    show: true,
                                    position: 'right',
                                    formatter: '{c}%',
                                    textStyle: {
                                      color: '#252429',
                                      fontSize: 16
                                    }
                                },
                              title:{
                                color: '#252429',
                                fontSize: 20
                              }
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
            // this.drawLine();
        }
    }
</script>


