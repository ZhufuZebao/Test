<template xmlns="http://www.w3.org/1999/html">
    <section class="report-wrapper">
        <div class="report-deteil-wrap edit clearfix">

            <img src="images/friend-000001.png" alt="〇〇〇工務店">
            <dl class="clearfix">
                <dt class="name icon-s">会社名</dt>
                <dd><input type="text" placeholder="会社名：ＸＸ会社"  v-model="name" disabled></dd>

                <dt class="schedule icon-s">郵便番号</dt>
                <dd><input type="text" placeholder="郵便番号：" v-model="zip" disabled></dd>

                <dt class="place icon-s">都道府県</dt>
                <dd><input type="text" placeholder="都道府県：1234" v-model="pref" disabled></dd>

                <dt class="place icon-s">市区町村</dt>
                <dd><input type="text"  placeholder="市区町村：" v-model="village" disabled></dd>

                <dt class="place icon-s">番地</dt>
                <dd><input type="text"  placeholder="番地：" v-model="addr" disabled></dd>

                <dt class="place icon-s">建物名</dt>
                <dd><input type="text"  placeholder="建物名：" v-model="building" disabled></dd>

                <dt class="tel icon-s">電話番号</dt>
                <dd><input type="text"  placeholder="電話番号：" v-model="phone" disabled></dd>

                <dt class="user icon-s">管理者名</dt>
                <dd><input type="text"  placeholder="管理者名：" v-model="manager_name" disabled></dd>

                <dt class="mail icon-s">管理者メールアドレス</dt>
                <dd><input type="text"  placeholder="管理者メールアドレス：" v-model="manager_mail" disabled></dd>

                <dt class="evaluation icon-s">管理者パスワード</dt>
                <dd><input type="text"  placeholder="管理者パスワード：" v-model="manager_password" disabled></dd>

                <dt class="note icon-s">備考</dt>
                <dd><textarea v-model="contents" disabled></textarea></dd>
            </dl>

            <div class="clearfix"></div>

            <div class="button-wrap clearfix">
                <div class="button-lower"> <button @click="back">戻る</button> </div>
                <div class="button-lower remark"><button @click="sure">確認</button></div>

            </div>
        </div>
    </section>
</template>

<script>
    export default {
        name: "ContractorSure",
        data() {
            return {
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
        },
        methods: {
            sure() {
                this.$router.push({
                    path: "/contractor/mailValidate",
                    query:{
                        name:this.name,
                        zip:this.zip,
                        pref:this.pref,
                        village:this.village,
                        addr:this.addr,
                        building:this.building,
                        phone:this.phone,
                        manager_name:this.manager_name,
                        manager_mail:this.manager_mail,
                        manager_password:this.manager_password,
                        contents:this.contents
                    }
                });
            },
            back(){
                this.$router.push({
                    path: "/contractor/register",
                    query:{
                        name:this.name,
                        zip:this.zip,
                        pref:this.pref,
                        village:this.village,
                        addr:this.addr,
                        building:this.building,
                        phone:this.phone,
                        manager_name:this.manager_name,
                        manager_mail:this.manager_mail,
                        manager_password:this.manager_password,
                        contents:this.contents
                    }
                })
            }
        }
    }
</script>

<style scoped>

</style>