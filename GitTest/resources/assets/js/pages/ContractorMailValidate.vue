<template>
    <div class="mailValidate">
        <div class="mailrow">
            <dt class="mail icon-s">認証キー</dt>
            <dd><input type="text"  placeholder="認証キー" v-model="mailKey"></dd>
        </div>
        <button class="MailButton" @click="checkMailKey">次へ</button>
    </div>

</template>

<script>
    export default {
        name: "ContractorMailValidate",
        mounted(){

        },
        methods: {
            checkMailKey: function() {
                axios.get("/api/setContractor", {
                        params:{
                            mailKey:this.mailKey,
                            name:this.$route.query.name,
                            zip:this.$route.query.zip,
                            pref:this.$route.query.pref,
                            village:this.$route.query.village,
                            addr:this.$route.query.addr,
                            building:this.$route.query.building,
                            phone:this.$route.query.phone,
                            manager_name:this.$route.query.manager_name,
                            manager_mail:this.$route.query.manager_mail,
                            manager_password:this.$route.query.manager_password,
                            contents:this.$route.query.contents
                        }
                    })
                    .then(res => {
                        if(res.data=='1'){
                            this.$router.push('/contractor/success');
                        }else{
                            alert("邮箱验证码错误！");
                        }
                    });
            }
        }
    }
</script>

<style scoped>

</style>