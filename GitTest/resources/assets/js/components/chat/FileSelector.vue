<template>
  <transition name="fade">
    <div class="modal wd1 modal-show account commonAll customerlist project-enterprise">
      <div class="modalBodyCustomer fileselector" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="closeModel">×</div>
        <div class="modalBodycontent commonMol ">
          <div class="common-view account-list">
            <!--<h3 class="modal-account-head" style="text-align:center;">FileSelector</h3>-->
            <el-button class="changedir" type="info" icon="el-icon-back" circle v-if="folderId !== null" @click="changeDir(folderId)"></el-button>
            <el-button class="changedir" type="info" icon="el-icon-back" disabled circle v-else @click="changeDir(folderId)"></el-button>
            <section class="list-del customer-wrapper invite-form">
                <div class="customertable">
                  <ul class="list-del-header report-serch clearfix">
                    <li class="customer-li"></li>
                    <li class="customer-li">
                      名前
                      <span @click="sort('name')"></span>
                    </li>
                    <li class="customer-li">
                      種類
                      <span @click="sort('type')"></span>
                    </li>
                    <li class="customer-li">
                      最終更新日時
                      <span @click="sort('time')"></span>
                    </li>
                    <li class="customer-li">
                      ファイルサイズ
                      <span @click="sort('size')"></span>
                    </li>
                  </ul>
                  <ul class="table-scroll">
                    <el-checkbox-group v-model="selected">
                      <li :id="'s'+tree.id" class="clearfix btns" date-tgt="wd1" v-for="tree in folderTree"
                          :key="tree.id">
                        <span class="customer-li">
                          <el-checkbox
                            disabled
                            :label="tree.id"
                            v-if="tree.parent === 0">&nbsp;
                          </el-checkbox>
                          <el-checkbox :label="tree.id" v-else>&nbsp;</el-checkbox>
                        </span>
                        <span v-if="tree.type > 0" class="customer-li" style="cursor:pointer;">
                          <img :src="makeLink(tree.type, tree.id, tree.rev_no, tree.fileType)">
                          {{tree.name}}
                        </span>
                        <span v-else class="customer-li" @click="changeDir(tree.id)" style="cursor:pointer;">
                          <img :src="makeLink(tree.type, tree.id, tree.rev_no, tree.fileType)">
                          {{tree.name}}
                        </span>
                        <span class="customer-li" >
                           {{tree.fileType}}
                        </span>
                        <span class="customer-li">
                          {{tree.time}}
                        </span>
                        <span class="customer-li">
                          {{formatFileSize(tree.size)}}
                        </span>
                      </li>
                    </el-checkbox-group>
                    </ul>
                </div>
              </section>
            <el-button class="sendto" :disabled="!selected.length" plain @click="addToAttachment">添付ファイルに追加</el-button>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>


<script>
  import messages from "../../mixins/Messages";

  export default {
    name: "FileSelector",
    mixins: [
      messages,
    ],
    props:{
      nodes:Array,
      groupId: {type: Number,
        default: 0},
    },
    data: function () {
      return {
        selected: [],
        isMounted: false,
        folderTree: [],
        folderId: 0,
        current: 0,
      }
    },
    methods: {
      //並べ替え
      sort(sortCol) {
        if (this.accounts.length !== 0) {
          if (this.sortCol === sortCol) {
            this.changeOrder();
          } else {
            this.sortCol = sortCol;
            this.order = 'desc'
          }
          //序列
          this.sortOrder();
        }
      },
      //順序付け規則を変更する
      changeOrder() {
        if (this.order === 'asc') {
          this.order = 'desc';
        } else {
          this.order = 'asc';
        }
      },
      //ソート方法
      sortOrder: function () {
        let col = this.sortCol;
        let order = this.order;
        if (col === 'name') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.name.localeCompare(b.name)
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.name.localeCompare(a.name)
            });
          }
        }
        if (col === 'type') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.enterprise_admin - b.enterprise_admin
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.enterprise_admin - a.enterprise_admin
            });
          }
        }
        if (col === 'size') {
          if (order === 'asc') {
            this.accounts.sort(function (a, b) {
              return a.email.localeCompare(b.email)
            });
          } else {
            this.accounts.sort(function (a, b) {
              return b.email.localeCompare(a.email)
            });
          }
        }
      },
      closeModel: function () {
        this.$emit('closeFileSelector');
      },
      getNodes(nodes, nodeId) {
        let showDoc = [];
        for(let i=0; i<nodes.length; i++) {
          if(nodes[i].parent === nodeId) {
            showDoc.push(nodes[i]);
          }
        }
        return showDoc;
      },
      changeDir(nodeId) {
        this.selected = [];
        this.current = nodeId;
        this.folderTree = this.getNodes(this.nodes, nodeId);
        let parent = this.getOneNode(this.nodes, nodeId);
        if(parent) {
          this.folderId = parent.parent;
        } else {
          this.folderId = null;
        }
      },
      getOneNode(nodes, nodeId) {
        for(let i=0; i<nodes.length; i++) {
          if(nodes[i].id === nodeId) {
            return nodes[i];
          }
        }
        return null;
      },
      makeLink(nodeType, nodeId, nodeRev = 1, fileType) {
        const baseSourceUrl = process.env.MIX_APP_DIR + '/api/getDocFileThumb'; //get image file's cover
        const baseIconUrl = process.env.MIX_APP_DIR + '/api/getDocFileIcon'; //get file's icon
        
        if (nodeType === 0) {
          // always show a folder's icon
          return baseIconUrl + '/Folder';
        }

        switch (fileType) {
          case 'JPG':
          case 'JPEG':
          case 'PNG':
          case 'GIF':
            if (nodeId > 0) {
              // make a document file path
              if (nodeRev === null) {
                return baseSourceUrl + '/' + nodeId + '/1';
              }
              return baseSourceUrl + '/' + nodeId +'/' + nodeRev;
            }
            // make a chatgroup file path
            return baseSourceUrl + '/' + nodeId + '/0';
          case 'DOC':
            return baseIconUrl + '/DOC';
          case 'DOCX':
            return baseIconUrl + '/DOCX';
          case 'XLS':
            return baseIconUrl + '/XLS';
          case 'XLSX':
            return baseIconUrl + '/XLSX';
          case 'PPT':
            return baseIconUrl + '/PPT';
          case 'PPTX':
            return baseIconUrl + '/PPTX';
          case 'PDF':
            return baseIconUrl + '/PDF';
          default:
            return baseIconUrl + '/Other';
        }
      },
      formatFileSize(size) {
        if(!size) {
          return '';
        }
        const sizes = ['バイト', 'KB', 'MB', 'GB', 'TB'];
        var i = 1;
        for (; i < sizes.length; i += 1) {
          if (size < Math.pow(1024, i)) {
            break;
          }
        }
        i -= 1;
        return (size / Math.pow(1024, i)).toFixed(2) + sizes[i];
      },
      addToAttachment() {
        let fileAttachment = [];
        let errMessage = this.commonMessage.error.chatMsgSend;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/moveFileToChat", {groupId: this.groupId, selected: this.selected, current: this.current})
          .then(res => {
            if(res.data.status) {
              fileAttachment = res.data.filesData;
              //add files to attachment
              this.$emit('closeFileSelectorWithSelected',fileAttachment);
            } else {
              errMessage = res.data.message;
              this.$alert(errMessage, {showClose: false});
              loading.close();
            }
          }).then(res => {
            loading.close();
          }).catch(err => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
      }
    },
    created() {
      this.folderTree = this.getNodes(this.nodes, 0);
      this.folderId = null;
    },
    mounted() {
      this.isMounted = true;
    },
    watch: {
      selected: function () {
        $(".is-checkedbox").removeClass('is-checkedbox');
        let num = this.selected.length;
        for (let i = 0; i < num; i++) {
          let str = '#s' + this.selected[i];
          $(str).addClass('is-checkedbox');
        }
      }
    },
    //窓際の位置を計算する
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
