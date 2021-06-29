<template>
  <table class="contractConsume">
    <tr>
      <td>分類</td>
      <td>アカウント名</td>
      <td>協力会社数</td>
      <td>職人数</td>
    </tr>
    <tr :id="'s'+users.id" :key="users.id" class="clearfix btns" date-tgt="wd1" v-for="users in user">
      <td >{{ users.enterprise_admin_name}}</td>
      <td >{{ users.name}}({{ users.email}})</td>
      <td >{{ users.invitecount}}人</td>
      <td >{{ users.FriendCount}}人</td>
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
            fetchUser: function () {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/getContractOffice',{
                        enterpriseId : this.$route.query.enterprise_id,
                }).then((res) => {
                    this.user = res.data.user;
                    try {
                        for (let i = 0; i < res.data.user.length; i++) {

                            if(res.data.user[i].enterprise_admin==='1'){
                                this.user[i].enterprise_admin_name='管理アカウント';
                            }else{
                                this.user[i].enterprise_admin_name='一般アカウント';
                            }
                        }
                    } catch (e) {
                        this.$alert(errMessage, {showClose: false});
                        loading.close();
                    }

                    if (this.user) {
                      this.$emit("sendAmountCount",this.user.length,res.data.enterpriseAmount,res.data.InviteCount);
                    } else {
                      this.$emit("sendAmountCount",0,0);
                    }
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
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

