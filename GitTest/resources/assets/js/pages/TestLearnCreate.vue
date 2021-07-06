<template>
    <transition name="fade">
        <div class="modal wd1 modal-show project-enterprise reportcreat">
            <div class="modalBodyCustomer modalBody" ref="modalBody"
                 v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
                <div class="modal-close" @click="closeModel">×</div>
                <div class="modalBodycontent commonMol customer-add">
                    <div class="common-deteil-wrap customerSel clearfix">
                        <div class="commonModelAdd"><span>练习数据添加</span></div>
                        <div class="testLearnCreateList">
                            <el-form>
                            <div class="inputDiv">
                                <span class="spanText">姓名：</span>
                                <el-input v-model="name" placeholder="请输入姓名" class="inputText"  maxlength="30"></el-input>
                            </div>
                                <span v-if="nameCheckError" class="checkError">{{nameErrorData}}</span>
                            <div class="inputDiv">
                                <span class="spanText">手机号：</span>
                                <el-input v-model="mobile" placeholder="请输入手机号" class="inputText"
                                          type="number" @blur="mobileBlur" maxlength="11"></el-input>
                            </div>
                                <span v-if="mobileCheckError" class="checkError">{{mobileErrorData}}</span>
                            <div class="inputDiv">
                                <span class="spanText">邮箱：</span>
                                <el-input v-model="email" placeholder="请输入邮箱" class="inputText" @blur="emailBlur" maxlength="30"></el-input>
                            </div>
                                <span v-if="emailCheckError" class="checkError">{{emailErrorData}}</span>
                            </el-form>

                        </div>


                    </div>
                    <div class="modelBut">
                        <el-button type="info" @click="createData" style="float: right">数据添加</el-button>
                    </div>
                </div>
            </div>
            <div class="modalBK"></div>
        </div>
    </transition>
</template>

<script>
    export default {
        name: "TestLearnCreate",
        data() {
            return {
                name: '',
                mobile: '',
                email: '',
                // canAddLabel:false,
                // nameLabel:false,
                // mobileLabel:false,
                // emailLabel:false,
                nameCheckError:true,
                mobileCheckError:true,
                emailCheckError:true,
                nameErrorData:'',
                mobileErrorData:'',
                emailErrorData:'',


            }
        },
        methods: {

            closeModel: function () {
                this.$emit('closeProject');
            },
            createData() {
                //if(this.nameLabel == true && this.mobileLabel == true && this.emailLabel == true){
                    var data = {
                        name:this.name,
                        mobile:this.mobile,
                        email:this.email,

                    }

                    axios.post('/api/createTestLearn',data).then((res) =>{
                        console.log(res.data);
                        var status = res.data.status;
                        if(status == 'exception'){
                            var errors = res.data.message;
                            this.nameErrorData = errors.name;
                            this.mobileErrorData = errors.mobile;
                            this.emailErrorData = errors.email;
                        }
                        if(status == 'success'){
                            window.location.reload();
                        }
                        if(status == 'error'){
                            alert('保存失败')
                        }




                    });
                //}

            },
           /*nameBlur(){
                var nameScope =  /^[a-zA-Z]{1,30}$/;         /!*^[a-zA-Z]\w{5,17}$*!/
                var nameCheck = new RegExp(nameScope);
                if(!nameCheck.test(this.name)){
                    this.nameCheckError = true;

                }else{
                    this.nameLabel = true;
                    this.nameCheckError = false;
                }
           },
            mobileBlur(){
                var mobileScope = /^[1][3,4,5,7,8][0-9]{9}$/;
                var mobileCheck = new RegExp(mobileScope);
                if(!mobileCheck.test(this.mobile)){
                    this.mobileCheckError = true;
                }else{
                    this.mobileLabel = true;
                    this.mobileCheckError = false;
                }

            },
            emailBlur(){
                var emailScope = /^\w+((-\w+)|(\.\w+))*@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
                var emailCheck = new RegExp(emailScope);
                if(!emailCheck.test(this.email)){
                    this.emailCheckError = true;
                }else{
                    this.emailLabel = true;
                    this.emailCheckError = false;
                }

            }
*/
        },
        computed: {
            modalLeft: function () {
                if (this.isMounted) {
                    return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
                } else {
                    return;
                }
            },
            modalTop: function () {
                return '0px';
            },
        },
    }
</script>

<style scoped>
    .testLearnCreateList {
        position: relative;
        width: 700px;

        background: #E5E5E5;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-top: 25px;
    }

    .inputText {

        width: 600px;
        float: right;
    }

    .inputDiv {
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .spanText {
        font-size: 20px;
        line-height: 40px;
    }
    .checkError{
        color: red;
    }

</style>