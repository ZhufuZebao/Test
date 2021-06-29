<template>
  <el-dialog title="ブロック" :visible.sync="WorkerBlock" width="400px" center :close-on-click-modal="false"
          @close="handleClose" :modal-append-to-body='false' :show-close='false' custom-class="workerblockdialog">
      <div>
        <ul>
          <li class="avatar">
            <img v-if="file" :src="file">
            <img v-else src="images/icon-chatperson.png">
          </li>
          <li><span>{{email}}</span><span>{{last_name}}</span></li>
        </ul>
        <ul class="workerBlock-text">
          <h3>ブロック理由</h3>
          <textarea v-model="block_msg" id="" cols="32" rows="6"></textarea>
        </ul>
      </div>
      <div slot="footer" class="dialog-footer">
        <el-button  class="modoru Cancelblock-button" @click="hideHandle">戻る</el-button>
        <el-button type="primary" @click="workerBlock">ブロックする</el-button>
      </div>
    </el-dialog>


</template>

<script>
    import Calendar from "../../../mixins/Calendar";
    import Messages from "../../../mixins/Messages";

    export default {
        name: "WorkerBlock",
        mixins: [Calendar, Messages],
        props: {
          WorkerBlockVisible:{
            type: Boolean,
            default: false
          },
            editable: {
                Type: Boolean,
                default: true
            },
            id: {
                Type: String,
                default: () => {
                }
            },
            block: {
                Type: String,
            },
            email:{
                Type: String,
            },
            file:{
                Type: String,
            },
            last_name:{
                Type: String,
            },
            topY: 0,
            col: 0
        },
        data() {
            return {
              WorkerBlock:false,
                isUpdate: false,
                block_msg:'',
                type: 0,
            }
        },
        methods: {

          handleClose(){
            this.$emit('changeShow','false')
          },
            hideHandle() {
              this.WorkerBlockVisible = false;
            },
            workerBlock(){
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.post('/api/workerBlock', {
                    block_msg: this.block_msg,
                    id: this.id,
                }).then(res => {
                    this.$emit('reload');
                    loading.close();
                }).catch(error => {
                    loading.close();
                    this.$alert(this.commonMessage.error.workerEdit, {showClose: false});
                });
            },

        },
      watch:{
        WorkerBlockVisible(oldVal,newVal){
          this.WorkerBlock = this.WorkerBlockVisible
        },
      }
    }
</script>
