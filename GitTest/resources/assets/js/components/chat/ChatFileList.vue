<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show chatfilelist">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click.prevent="closeFiles">×</div>
        <div class="modalBodycontentChatList">
          <h3>ファイル一覧</h3>
          <div class="filelistsearch">
            <el-input placeholder="ファイル名で検索" v-model="searchFileWord" suffix-icon="searchForm-submit"
                      class="popoverto-input" @change="fetchFileLists" style="width: 90%"></el-input>
          </div>
          <div>
            <div class="file-list-top">
              <el-checkbox v-model="checkAll" @change="fillCheckAll" :disabled="fileLists.length === 0"></el-checkbox>
              <span class="fileFont">ファイル名</span>
              <span class="fileSize">サイズ</span>
            </div>
            <el-checkbox-group v-model="downloadFileList">
            <el-tabs tab-position="left">
              <el-tab-pane v-for="(fileList,index) in fileLists" :key="'fileList'+index">
                <span slot="label" class="chatfileslist-left">
                  <div style="height:60px;border-bottom: 1px solid #e5e5e5;">
                    <el-checkbox :label="fileList.id" class="chatfileslist-checkbox">
                      <div id="chatFlieItem">
                        <div id="left">
                          <img :src="'file/' + groupId + '/' + encodeURIComponent(fileList.file_name)" v-if="fileList.isPic"/>
                          <img src="images/icon-file40.png" v-else />
                        </div>
                        <div id="center">
                          <span class="span-left">
                            {{
                              fileList.file_name.substring(
                                  fileList.file_name.lastIndexOf("/") + 1,
                                  fileList.file_name.length).substring(14)
                            }}
                          </span>
                        </div>
                        <div id="right">
                          <span>{{ fileList.fileSize }}</span>
                        </div>
                      </div>
                    </el-checkbox>
                  </div>
                </span>
                <div class="chatfileslist-right">
                  <li class="chatfileslist-right-title" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 4;overflow: hidden;">{{fileList.file_name.substring(fileList.file_name.lastIndexOf('/') + 1,
                    fileList.file_name.length).substring(14)}}</li>
                  <li v-if="fileList.isPic"><img :src="'file/'+groupId+'/' + encodeURIComponent(fileList.file_name)"></li>
                  <li v-else><img src="images/icon-file269.png"></li>
                  <li class="chatfileslist-right-p"><p class="p-left">サイズ</p>
                    <p>{{fileList.fileSize}}</p></li>
                  <li class="chatfileslist-right-p"><p class="p-left">登録日</p>
                    <p>{{fileList.created_at}}</p></li>
                  <el-button class="downloadButton" :style="changeColor()" @click="downloadBtn" :disabled="downloadFileList.length === 0">ダウンロード</el-button>
                </div>
              </el-tab-pane>
            </el-tabs>
            </el-checkbox-group>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import Messages from "../../mixins/Messages";

  export default {
    name: 'ChatFileList',
    mixins: [Messages],
    props: ['groupId'],
    data: function () {
      return {
        searchFileWord: '',
        isMounted: false,
        fileLists: [],
        downloadFileList: [],
        isOk: true,
        checkAll:false,
      }
    },
    methods: {
      fetchFileLists() {
        if (this.groupId === '' || this.isOk === false) {
          return;
        }
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getGroupFileList', {
          params: {
            searchFileWord: this.searchFileWord,
            groupId: this.groupId
          }
        }).then((res) => {
          this.fileLists = res.data;
          if (this.searchFileWord === '') {
            this.isOk = false;
          }
          loading.close();
        }).catch(error => {
          loading.close();
        });
      },
      closeFiles() {
        this.$emit('closeFileShow');
      },
      downloadBtn() {
        let errMessage = this.commonMessage.error.noFileDownload;
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let fileCount = this.downloadFileList.length;
        if(!fileCount) {
          this.$alert(errMessage, {showClose: false});
        }
        if(fileCount === 1) {
          const link = document.createElement('a');
          let selectedId = this.downloadFileList[0];
          let fileName = '';
          for(let i=0;i<this.fileLists.length;i++) {
            if(this.fileLists[i].id === selectedId) {
              fileName = this.fileLists[i].file_name;
            }
          }
          link.href = 'download/' + this.groupId + '/' + encodeURIComponent(fileName);
          loading.close();
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        } else {
          axios.post('/api/downloadBatchSelectedChatFile', {
            selected: this.downloadFileList
          },{responseType:'arraybuffer'}).then((res) => {
            if (res.data.byteLength > 10) {
              //file exists
              let blob = new Blob([res.data], { type: "application/octet-stream" });
              const elink = document.createElement('a');
              elink.download = 'images.zip';
              elink.style.display = 'none';
              elink.href = URL.createObjectURL(blob);
              document.body.appendChild(elink);
              elink.click();
              URL.revokeObjectURL(elink.href);
              document.body.removeChild(elink);
            } else {
              //file not exists
              this.$alert(errMessage, {showClose: false});
            }
            loading.close();
          }).catch(err => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
        }
      },
      fillCheckAll(){
        if (this.checkAll) {
          this.downloadFileList = [];
          for (let i = 0;i<this.fileLists.length;i++) {
            this.downloadFileList.push(this.fileLists[i].id);
          }
        } else {
          this.downloadFileList = [];
        }
      },
      changeColor(){
        if(this.downloadFileList.length === 0){
          return "background-color:#cccccc";
        }
        return "background-color:#219894";
      }
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      }
    },
    mounted() {
      this.isMounted = true;
      this.fetchFileLists();
    },
    watch: {
      searchFileWord() {
        this.isOk = true;
      },
      downloadFileList() {
        if (this.downloadFileList.length === this.fileLists.length) {
          this.checkAll = true;
        } else {
          this.checkAll = false;
        }
      }
    }
  }
</script>
<style lang="scss" scoped>
  .file-list-top {
    background-color: white;
    height: 60px;
    width: 400px;
    position: fixed;
    top: 131px;
    left: 0px;
    border-bottom: 4px solid #cccccc;
    padding-left: 20px;
  }
  #chatFlieItem {
    width: 350px;
  }
  #left {
    width: 350px;
    position: absolute;
    top: 11px;
    left: 25px;
  }
  #center {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    width: 150px;
    position: absolute;
    top: 22px;
    right: 86px;
    left: 0;
    margin: auto;
  }
  #right {
    width: 60px;
    position: absolute;
    top: 22px;
    right: 20px;
  }
  .fileFont {
    font-size: 18px;
    margin-left: 10px
  }
  .fileSize {
    float: right;
    margin-right: 35px;
    font-size: 18px;
  }
  .downloadButton {
    border-radius: 0px;
    background-color: #219894;
    color: white;
    top: 300px;
    position: relative;
    left: 10px;
    width: 280px;
  }
  @media (max-height: 850px) {
    .downloadButton {
        top: 200px;
    }
  }
  @media (max-height: 750px) {
    .downloadButton {
        top: 100px;
    }
  }
  @media (max-height: 650px) {
    .downloadButton {
        top: 20px;
    }
  }
  
</style>