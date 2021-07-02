<template>
    <div class="container clearfix customer customerlist commonAll">
        <header>
            <h1>
                <router-link to="/TestLearn">
                    <div class="commonLogo">
                        <ul>
                            <li class="bold">TestLearn</li>
                            <li>练习</li>
                        </ul>
                    </div>
                </router-link>
            </h1>
            <!-- 機能タイトルヘッダー -->
            <div class="title-wrap">
                <h2>练习数据一覧</h2>
                <div class="title-add">
                    <img src="images/add@2x.png" @click="openCreate"/>
                </div>
            </div>
        </header>
        <div class="customer-wrapper report-form delet-img">
            <el-table
                    :data="datas"
                    stripe
                    style="width: 100%">
                <el-table-column
                        prop="name"
                        label="姓名"
                        width="300">
                </el-table-column>
                <el-table-column
                        prop="mobile"
                        label="手机号"
                        width="300">
                </el-table-column>
                <el-table-column
                        prop="email"
                        label="邮箱"
                        width="300">
                </el-table-column>
                <el-table-column
                        prop="updated_at"
                        label="更新日期">
                </el-table-column>
            </el-table>
        </div>
       <TestLearnCreate v-if="this.createLabel" @closeProject="closeProject"></TestLearnCreate>
    </div>
</template>

<script>

    import TestLearnCreate from "./TestLearnCreate";
    export default {
        components: {TestLearnCreate},
        data() {
            return{
                datas:null,
                createLabel:false,
            }
        },
        mounted:function(){
            this.selectTestLearnData();
        },
        methods : {
            openCreate(){
                this.createLabel = true;
            },
            closeProject(){
                this.createLabel = false;
            },
            selectTestLearnData(){
                axios.get('/api/selectTestLearn').then((res) =>{
                    this.datas = res.data;
                });
            }
        }

    }
</script>

<style scoped>

</style>