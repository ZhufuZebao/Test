<template>
    <div>
        <form>
            用户名：<el-input v-model="user" class="Input-length"></el-input>
            性别：<el-input v-model="sex" class="Input-length"></el-input>
            爱好：<el-input v-model="like" class="Input-length"></el-input>
            <button @click="sub">提交</button>
<!--            <button @click="selectUser">检索</button>-->
        </form>
        <hr>
        <!--<table align="center" width="100%" border="1px" cellpadding="6px" cellspacing="0px">
            <tr>
                <td>用户名</td>
                <td>性别</td>
                <td>爱好</td>
                <td>操作</td>
            </tr>
            <tr v-for="userr in users" :key="userr.id">
                <td>{{userr.user}}</td>
                <td>{{userr.sex}}</td>
                <td>{{userr.like}}</td>
                <td>
                    <a class="upd" href="javascript:void(0)" @click="upd(userr.id)">修改</a>
                    <span>   </span>
                    <a class="del" href="javascript:void(0)" @click="del(userr.id)">删除</a>
                </td>
            </tr>
            <tr>
                <td colspan="4">共{{usersNum}}条数据</td>
            </tr>
        </table>-->
        <el-table
                :data="users"
                style="width: 100%">
            <el-table-column
                    prop="user"
                    label="用户名"
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="sex"
                    label="性别"
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="like"
                    label="爱好">
            </el-table-column>
            <el-table-column
                    label="操作">
                <template slot-scope="scope">
                    <el-button @click="upd(scope.row.id)" type="text" >修改</el-button>
                    <span>    </span>

                    <el-button @click="del(scope.row.id)" type="text" >删除</el-button>

                </template>
            </el-table-column>


        </el-table>
        <div class="updateDiv" v-if="updateLable">
            <span class="updateTitle">修改</span>
            <form >
                用户名：<el-input v-model="updateUser"  class="Input-length"></el-input>
                性别：<el-input v-model="updateSex"  class="Input-length"></el-input>
                爱好：<el-input v-model="updateLike"  class="Input-length"></el-input>
                <button @click="updated(updateId)">提交修改</button>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: '',
                sex:'',
                like:'',
                users:null,
                usersNum:0,
                updateId:null,
                updateUser:'',
                updateSex:'',
                updateLike:'',
                updateLable:false,
            }
        },
        mounted:function() {
            this.selectUser();
        },
        methods : {
            sub(){

                let data = {

                        user:this.user,
                        sex:this.sex,
                        like:this.like,

                };
                if(this.user.length == 0||this.sex.length == 0||this.like.length == 0){
                    alert("输入框中不能为空！");
                    return;
                }
                axios.post('/api/TestViewAdd',data).then((res) =>{

                    if(res.data = 'success'){

                        window.location.reload();

                    }else {
                        alert("保存失败");
                    }
                });
            },
            selectUser:function () {
                axios.get('/api/TestViewSelect').then((res) =>{
                    this.users = res.data;
                    this.usersNum = res.data.length;


                });
            },

            del(id){
                var delId = id;
                axios.post('/api/TestViewDelete',{delID:delId}).then((res) =>{
                    if(res.data = 'success'){

                        window.location.reload();
                    }else {
                        alert("删除失败");
                    }
                });

            },
            upd(id){

                axios.post('/api/TestViewUpdateSelect',{updId:id}).then((res) =>{
                    var updSelect = res.data;
                    console.log(res);
                    console.log(res.data);
                    this.updateUser = updSelect[0].user;
                    this.updateSex = updSelect[0].sex;
                    this.updateLike = updSelect[0].like;
                    this.updateId = updSelect[0].id;
                    this.updateLable = true;
                });

            },
            updated(id){
                let updateData = {
                    update:id,
                    updateUser:this.updateUser,
                    updateSex:this.updateSex,
                    updateLike:this.updateLike,
                }
                axios.post('/api/TestViewUpdate',updateData).then((res) =>{
                    if(res.data = 'success'){
                        alert("修改成功");
                        this.updateLable = false;
                        window.location.reload();
                    }else{
                        alert("修改失败");
                    }
                });

            }
        },

    }

</script>
<style>
    .Input-length{
        width: 200px;
    }
    td{
        text-align: center;
        font-size: 18px;
    }
    .upd{
        color: #0000FF;
    }
    .del{
        color: red;
    }
    .updateDiv{
        width: 100%;
        height: 200px;
        margin:0 auto;
        background-color: #0a8abd;
        text-align: center;
        line-height:100px;
    }
    .updateTitle{
        font-size: 30px;
        color: red;

    }


</style>

