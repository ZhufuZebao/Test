<template>
  <table class="contracthistory">
    <tr>
      <td>分類</td>
      <td>アカウント名</td>
      <td>ログイン時間</td>
      <td>IPアドレス</td>
    </tr>
    <tr :id="'s'+users.id" :key="users.id" class="clearfix btns" date-tgt="wd1" v-for="users in user">
      <td >{{ users.enterprise_admin_name}}</td>
      <td >{{ users.email}}</td>
      <td >{{ users.last_date}}</td>
      <td >{{ users.ip}}</td>
    </tr>
  </table>
</template>

<script>
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    export default {
        name: "ContractConsume",
        components: {
        },
        mixins: [Messages,Calendar],
        props: [],
        data() {
            return {
                user:{}
            }
        },
        methods: {
            formatDate: function(date) {

                const dateTime = new Date(date);
                const YY = dateTime.getFullYear();
                const MM =
                    dateTime.getMonth() + 1 < 10
                        ? '0' + (dateTime.getMonth() + 1)
                        : dateTime.getMonth() + 1;
                const D =
                    dateTime.getDate() < 10 ? '0' + dateTime.getDate() : dateTime.getDate();
                const hh =
                    dateTime.getHours() < 10
                        ? '0' + dateTime.getHours()
                        : dateTime.getHours();
                const mm =
                    dateTime.getMinutes() < 10
                        ? '0' + dateTime.getMinutes()
                        : dateTime.getMinutes();
                const ss =
                    dateTime.getSeconds() < 10
                        ? '0' + dateTime.getSeconds()
                        : dateTime.getSeconds();
                return `${YY}/${MM}/${D}`;


            },
            fetchUser: function () {
                // const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/ContractHistory',{
                        enterpriseId : this.$route.query.enterprise_id,
                }).then((res) => {
                    this.user = res.data;
                    try {
                        for (let i = 0; i < res.data.length; i++) {

                            if(res.data[i].enterprise_admin==='1'){
                                this.user[i].enterprise_admin_name='管理アカウント';
                            }else{
                                this.user[i].enterprise_admin_name='一般アカウント';
                            }
                        }
                    } catch (e) {
                        this.$alert(errMessage, {showClose: false});
                        //loading.close();
                    }
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    // loading.close();
                });
                //loading.close();
            },
        },
        created() {
        },
        watch: {

        },
        mounted() {
            this.fetchUser();
        }
    }
</script>

