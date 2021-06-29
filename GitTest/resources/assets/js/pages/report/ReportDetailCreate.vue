<template>
  <!--container-->
  <div class="container clearfix customer customerlist commonAll report">
    <header>
      <h1>
        <router-link to="/report">
          <div class="commonLogo">
            <ul>
              <li class="bold">REPORT</li>
              <li>簡易レポート</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <!--<h2>簡易レポート</h2>-->
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <!--<el-breadcrumb-item v-for="nodeItem in currentPageNodes"><span>簡易レポート一覧</span>-->
            <!--&gt; <span @click="clickHeaderNavigation(nodeItem)"> {{nodeItem.name}} </span>-->
          <!--</el-breadcrumb-item>-->
          <el-breadcrumb-item :to="{ path: '/report', query: {flag:'success'}}"><span>簡易レポート </span></el-breadcrumb-item>
          <el-breadcrumb-item v-if="folderSelect || imageSelect"><span @click="imagePick">ホーム</span></el-breadcrumb-item>
          <el-breadcrumb-item v-for="nodeItem in currentPageNodes" :key="'bread'+nodeItem.id"><span @click="clickHeaderNavigation(nodeItem)">{{nodeItem.name}} </span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <div class="container clearfix commonAll reportdetail">
      <section class="common-wrapper" v-if="infoForm">
        <div class="common-view">
          <div class="content customerdetail-nametop">
            <el-form ref="form" :model="report" :rules="rules" label-width="200px" @submit.native.prevent label-position="left">
              <div class="form-group">
                <el-form-item label="案件名" prop="project_name">
                  <el-input v-model="report.project_name" maxlength="50"></el-input>
                </el-form-item>
              </div>
              <div class="form-group inblock">
                <el-form-item label="作業日">
                  <el-form-item prop="report_date">
                    <el-date-picker
                                    type="date"
                                    :picker-options="stPickerOptions"
                                    v-model="report.report_date"
                                    value-format="yyyy-MM-dd"
                                    :clearable="clearable">

                    </el-date-picker>
                  </el-form-item>
                  ～
                  <el-form-item prop="report_date_ed">
                    <el-date-picker
                                    type="date"
                                    :picker-options="edPickerOptions"
                                    v-model="report.report_date_ed"
                                    value-format="yyyy-MM-dd"
                                    :clearable="clearable">

                    </el-date-picker>
                  </el-form-item>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item label="報告者"  prop="report_user_name">
                  <el-input v-model="report.report_user_name" maxlength="51"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item label="現場名" prop="location">
                  <el-input v-model="report.location" maxlength="51"></el-input>
                </el-form-item>
              </div>

              <div class="form-group">
                <el-button type="success" round @click="imagePick" class="report-imgupload" icon="el-icon-imgupload">写真一括選択</el-button>
                <el-button type="success" round @click="addImageAndComment" class="report-imgupload" icon="el-icon-imgupload" style="width: 250px">ブロックの追加</el-button>
              </div>

              <div class="form-group report-imglist" v-for="(item,index) in report.report_files">
                <ImageList ref="imageListRef" :imageData="item" :sortName="(index+1)" :key="'report+'+item.id" @rePickImage="rePickImage" @delImage="delImage" @delImageAndComment="delImageAndComment"></ImageList>
              </div>

              <div class="updateButton">
                <a class="updateBack" @click="deleteReport">キャンセル</a>
                <a class="updateSave" @click="save">途中保存 </a>
                <a class="updateSave" @click="pdfExport">PDF出力</a>
              </div>
            </el-form>
          </div>
        </div>
      </section>

      <section class="report-imglist" v-if="folderSelect && !imageSelect">
        <div class="common-view">
          <ul class="imglist clearfix">
            <li v-if="itemList.parent === 0" :key="'f'+index" v-for="(itemList,index) in folderList" @click="clickFolder(itemList)">
              <el-image src="images/folder.png" v-if="itemList.parent === 0"></el-image>
              <p v-if="itemList.parent === 0">{{itemList.name}}</p>
            </li>
          </ul>
          <div class="modelBut">
            <button class="updateBack" style="margin-left: 0;" @click="closeImagePick">キャンセル</button>
          </div>
        </div>
      </section>

      <section class="report-imglist" v-if="imageSelect && !folderSelect">
        <div class="common-view">
          <span @click="sort('time')" class="imglist-sort">撮影日<i class="el-icon-d-caret"></i></span>
          <ul class="imglist clearfix">
            <li v-if="currentDataList" :key="'l'+index" v-for="(itemList,index) in currentDataList">
              <el-image :src="itemList.file_path_url" fit="contain" v-if="itemList.file_path_url" @click="addChecked(itemList)"></el-image>
              <el-image src="images/folder.png" fit="contain" v-else @click="clickChildFolder(itemList)"></el-image>
              <p>{{itemList.name}}</p>
              <span v-if="itemList.isChecked">{{itemList.sort}}</span>
            </li>
          </ul>
          <div class="modelBut">
            <button class="updateBack" @click="closeImagePick">キャンセル</button>
            <button class="updateSave" @click="saveImagePick">決定</button>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import ReportCols from '../../mixins/ReportCols';
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/report';
  import ImageList from '../../components/report/ImageList';
  import Calendar from "../../mixins/Calendar";

  export default {
    name: "ReportDetail",
    mixins: [ReportCols, validation, Messages ,Calendar],
    components: {
      ImageList,
      UserProfile
    },
    data: function () {
      return {
        report: {},
        delCheck: false,   //削除確認フラグ
        clearable: false,
        uploadTmp: '',
        updateImg: false,
        rawFile: '',
        infoForm: true,
        imageSelect: false,
        folderSelect: false,
        imageList: [],
        folderList: [],
        currentDataList: [],
        selectedImage: [],
        fileList: [],
        multiple: false,
        show_file_list: false,
        limit: 1,
        removePickItems:[],
        isEditImage:false,
        editImageIndex:-1,
        stPickerOptions: {
          disabledDate: this.stDisabledDate,
        },
        edPickerOptions: {
          disabledDate: this.edDisabledDate,
        },
        order: 'asc',
        sortCol: 'time',
        currentPageNodes:[],
      };
    },
    methods: {
      clickHeaderNavigation(node){
        let nodeArrTmp = [];
        for (let i = 0;i< this.currentPageNodes.length ; i++) {
          if (this.currentPageNodes[i].id == node.id) {
            break;
          }
          nodeArrTmp.push(this.currentPageNodes[i]);
        }

        //re value
        this.currentPageNodes = nodeArrTmp;
        this.clickFolder(node);
      },

      clickChildFolder(data) {
        this.clickFolder(data);
      },

      clickFolder(data){
        this.folderSelect = false;
        this.imageSelect = true;

        this.currentPageNodes.push(data);
        this.currentDataList = [];

        let folderId = data.id;

        // this.currentDataList

        for (let j = 0;j < this.folderList.length ;j++) {
          if (this.folderList[j].parent === folderId) {
            this.currentDataList.push(this.folderList[j]);
          }
        }

        for (let i = 0;i < this.imageList.length ;i++) {
          if (this.imageList[i].parent === folderId) {
            this.currentDataList.push(this.imageList[i]);
          }
        }


      },
      openFolderList(){
        this.folderSelect = true;
      },

      sort(sortCol) {
        if (this.currentDataList.length !== 0) {
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
        if (col === 'time') {
          if (order === 'asc') {
            this.currentDataList.sort(function (a, b) {
              return a.time.localeCompare(b.time)
            });
          } else {
            this.currentDataList.sort(function (a, b) {
              return b.time.localeCompare(a.time)
            });
          }
        }
      },

      formatDate (date) {
        let y = date.getFullYear();
        let m = date.getMonth() + 1;
        m = m < 10 ? ('0' + m) : m;
        let d = date.getDate();
        d = d < 10 ? ('0' + d) : d;
        return y + '/' + m + '/' + d;
      },

    addImageAndComment(){
        let imageDefault = 'images/no-image.png';
        //【簡易レポート】設定した日付を自動反映#3252
        let date = new Date(Calendar.dateFormat(this.report.report_date));

        let resObjTemp = {
          'comment':'',
          'report_date':date,
          'work_place':'',
          'weather':'',
          'name':'NO IMAGE',
          'fileName':'NO IMAGE',
          'file_path':imageDefault,
          'file_path_url':imageDefault,
        };
        let index = this.report.report_files.length;
        this.$set(this.report.report_files, index, resObjTemp);
      },
      //作業期間 date-picker:disabledDate
      stDisabledDate(time) {
        return time.getTime() > new Date(this.report.report_date_ed).getTime();
      },
      edDisabledDate(time) {
        return time.getTime() < new Date(this.report.report_date).getTime() - 1000 * 60 * 60 * 24;
      },
      //ドナー情報を取得する
      initForm: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/createReport", {
          params: {
            id: this.$route.params.id
          }
        }).then((res) => {
          this.report = res.data;
          loading.close();
        }).catch(err => {
          let errMsg = this.commonMessage.error.system;
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      //情報を提示する
      delYes() {
        this.delCheck = false;
        this.deleteReport();
      },
      //情報を提示する
      delNo() {
        this.delCheck = false;
      },
      //情報を提示する
      openDelModal() {
        this.delCheck = true;
      },
      //情報を提示する
      closeDelModal() {
        this.delCheck = false;
      },
      //データの更新
      save: function () {
        let errMsg = this.commonMessage.error.update;
        this.report.export = false;
        let imageListRefValidFlag = true;
        this.$refs['form'].validate((valid) => {
          //imageList
          if (valid) {
            if (this.$refs.imageListRef && this.$refs.imageListRef.length > 0) {
              for (let i = 0; i < this.$refs.imageListRef.length; i++) {
                this.$refs.imageListRef[i].$refs['form'].validate((validImageList) => {
                  if (validImageList) {
                    imageListRefValidFlag = true;
                  } else {
                    imageListRefValidFlag = false;
                    return false;
                  }
                });
              }
            } else {
              //this.$refs.imageListRef は null
              imageListRefValidFlag = true;
            }

            if (imageListRefValidFlag) {
              //check success
              //submit
              for (let i = 0;i< this.report.report_files.length;i++) {
                //format report_file_date 'Date' to 'String'
                this.report.report_files[i].report_date = Calendar.dateFormat(this.report.report_files[i].report_date);
              }
              const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
              axios.post("/api/updateReport", {
                report: this.report
              }).then(res => {
                loading.close();
                this.backToPrev();
              }).catch(err => {
                loading.close();
                this.$alert(errMsg, {showClose: false});
              });
            } else {
              //check faild
              setTimeout(() => {
                let isError = document.getElementsByClassName("is-error");
                isError[0].querySelector('input').focus();
              }, 1);
            }
          } else {
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
          }
        })
      },
      pdfExport: function () {
        // this.$router.push({path: '/api/getReportPdf/' + this.report.id});
        let fieldErrMsg = this.commonMessage.error.formValidFail;
        let errMsg = this.commonMessage.error.system;
        this.report.export = true;
        let imageListRefValidFlag = true;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if (this.$refs.imageListRef && this.$refs.imageListRef.length > 0) {
              for (let i = 0; i < this.$refs.imageListRef.length ; i++) {
                this.$refs.imageListRef[i].$refs['form'].validate((validImageList ) => {
                  if (validImageList) {
                    imageListRefValidFlag = true;
                  } else {
                    imageListRefValidFlag = false;
                    return false;
                  }
                });
              }
            } else {
              //this.$refs.imageListRef は null
              imageListRefValidFlag = true;
            }
            if (imageListRefValidFlag) {
              for (let i = 0;i< this.report.report_files.length;i++) {
                //format report_file_date 'Date' to 'String'
                this.report.report_files[i].report_date = Calendar.dateFormat(this.report.report_files[i].report_date);
              }
              const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
              axios.post("/api/updateReport", {
                report: this.report
              }).then((res) => {
                const link = document.createElement('a');
                let reportUserName = '';
                let placeName = '';
                let result = this.report.report_user_name;
                let location = this.report.location;
                (result!=null && result!=="" && result!=='undefined') ? reportUserName = this.report.report_user_name : reportUserName = '';
                (location!=null && location!=="" && location!=='undefined') ? placeName = this.report.location : placeName = '';
                link.href = window.location.pathname+'api/getPDF?id='+res.data.report_id+'&createName='+reportUserName+'&placeName='+placeName;
                link.download = true;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                loading.close();
                this.backToPrev();
              }).catch(err => {
                loading.close();
                this.$alert(errMsg, {showClose: false});
              });
            } else {
              setTimeout(() => {
                let isError = document.getElementsByClassName("is-error");
                isError[0].querySelector('input').focus();
              }, 1);
            }
          } else {
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
          }
        });
      },
      handleChange: function (file) {
        this.validateMessage = this.imageValidate(file);
        if (!this.validateMessage) {
          this.rawFile = file.raw;
          this.$set(this.uploadTmp, 'subject_image', URL.createObjectURL(file.raw));
          this.updateImg = true;
          this.$emit('updateFile', this.rawFile);
        }
      },
      updateFile: function (file) {
        this.rawFile = file;
      },
      //ルーティング・ダイビング
      back() {
        this.$router.go(-1);
      },
      //リストにジャンプして戻る
      backToPrev() {
        this.$router.push({path: '/report', query: {flag: 'success'}});
      },
      //ドナーを削除する
      deleteReport: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.delete;
        let deleteNotify = this.report.project.place_name + 'の入力を破棄してもいいですか？';
        this.$confirm(deleteNotify).then(() => {
          loading.close();
          this.$router.push({path: "/report", query: {flag: 'success'}});
        }).catch(action => {
          loading.close();
        });
      },
      imagePick() {
        this.infoForm = false;
        // this.imageSelect = true;
        this.imageSelect = false;
        this.folderSelect = true;
        this.currentPageNodes = [];
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        // axios.get("/api/getProjectFiles", {
        axios.get("/api/getProjectFiles", {
          params: {
            projectId: this.report.project_id
          }
        }).then((res) => {
          let imageTmpArr = res.data.revs;
          for (let i = 0; i < imageTmpArr.length; i++) {
            imageTmpArr[i]['isChecked'] = false;
            imageTmpArr[i]['sort'] = 0;
          }

          // 画像の繰り返し filter
          if (this.report.report_files){
            for (let j = 0; j < this.report.report_files.length; j++) {
              for (let k = 0; k < imageTmpArr.length ; k ++) {
                if (this.report.report_files[j].file_path == imageTmpArr[k].file_path){
                  imageTmpArr[k].isChecked = this.report.report_files[j].isChecked;
                  imageTmpArr[k].sort = this.report.report_files[j].sort;
                  imageTmpArr.splice(k,1);
                  this.selectedImage.push(this.report.report_files[j]);
                }
              }
            }

          }

          for (let d = 0; d < imageTmpArr.length; d++) {
            imageTmpArr[d].file_path_url =  imageTmpArr[d].file_path_url;
          }

          this.imageList = imageTmpArr;
          //folder data init
          this.folderList = res.data.folders;
          loading.close();
        }).catch(err => {
          let errMsg = this.commonMessage.error.system;
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      addChecked(item) {
        let id = '';
        if (item.id){
          id = item.id;
        } else {
          id = item.node_id;
        }
        let num = this.selectedImage.length;
        let sort = 0;
        sort = num + 1;

        let numTotal = this.imageList.length;

        for (let i = 0; i < numTotal; i++) {
          if (this.imageList[i]['id'] === id) {
            if (this.imageList[i]['isChecked']) {
              this.removePick(i);
              return;
            } else {
              this.addPick(i, item, sort);
              return;
            }
          } else if (this.imageList[i]['node_id'] === id){
            if (this.imageList[i]['isChecked']) {
              this.removePick(i);
              return;
            } else {
              this.addPick(i, item, sort);
              return;
            }
          }
        }
      },
      removePick(i) {
        //to resort
        this.imageList[i]['isChecked'] = false;
        let sortThis = this.imageList[i]['sort'];
        this.imageList[i]['sort'] = 0;

        //逆選択
        // this.removePickItems.push(this.imageList[i]);

        this.removeItem(this.imageList[i]['id']);
        this.resortList(sortThis);
      },
      addPick(i, item, sort) {
        this.imageList[i]['isChecked'] = true;
        this.imageList[i]['sort'] = sort;
        this.selectedImage.push(item);
        //edit image
        if (this.isEditImage) {
          this.editImage(item);
        }
      },
      resortList(sort) {
        let numTotal = this.imageList.length;
        for (let i = 0; i < numTotal; i++) {
          if (this.imageList[i]['sort'] > sort) {
            this.imageList[i]['sort'] = this.imageList[i]['sort'] - 1;
          }
        }
      },
      removeItem(id) {
        let numTotal = this.selectedImage.length;
        for (let i = 0; i < numTotal; i++) {
          if (this.selectedImage[i]['id'] === id) {
            this.selectedImage.splice(i,1);
            return;
          }
        }
      },

      //image select close and save
      saveImagePick() {
        this.infoForm = true;
        this.imageSelect = false;
        this.folderSelect = false;
        //return selected items
        this.addSelectedItems(this.selectedImage);
        this.imageList = [];
        this.selectedImage = [];
        this.currentPageNodes = [];
      },

      //image select close
      closeImagePick(){
        this.infoForm = true;
        this.imageSelect = false;
        this.folderSelect = false;
        this.imageList = [];
        this.selectedImage = [];
        this.currentPageNodes = [];
      },

      //delete image item
      delImage: function (index) {
        //delete report_file method
        if (this.report.report_files.length > 0) {
          let sortTemp = this.report.report_files[index].sort;
          let commentTemp = this.report.report_files[index].comment;
          let reportFileIdTemp = this.report.report_files[index].report_file_id;
          let imageDefault = 'images/no-image.png';

          let resObjTemp = {
            'report_file_id':reportFileIdTemp,
            'sort':sortTemp,
            'report_date':this.report.report_files[index].report_date,
            'weather':this.report.report_files[index].weather,
            'work_place':this.report.report_files[index].work_place,
            'comment':commentTemp,
            'name':'NO IMAGE',
            'fileName':'NO IMAGE',
            'file_path':imageDefault,
            'file_path_url':imageDefault,
          }
          this.$set(this.report.report_files, index, resObjTemp);
        } else {
          // no file
        }
      },

      //delete image and comment item
      delImageAndComment: function (index) {
        //delete report_file method
        if (this.report.report_files.length > 0) {
          this.report.report_files.splice(index, 1);
        } else {
          // no file
        }
      },

      rePickImage: function (index) {
        this.isEditImage = true;
        this.editImageIndex = index;
        this.imagePick();
      },

      editImage(item) {
        this.infoForm = true;
        this.imageSelect = false;

        let resObjTemp = {
          'childNodes':item.childNodes,
          'comment':this.report.report_files[this.editImageIndex].comment,
          'report_date':this.report.report_files[this.editImageIndex].report_date,
          'weather':this.report.report_files[this.editImageIndex].weather,
          'work_place':this.report.report_files[this.editImageIndex].work_place,
          'fileName':item.fileName,
          'fileType':item.fileType,
          'file_path':item.file_path,
          'file_path_url':item.file_path_url,
          'id':item.id,
          'report_file_id':this.report.report_files[this.editImageIndex].id,
          'isChecked':item.isChecked,
          'name':item.name,
          'owner':item.owner,
          'parent':item.parent,
          'parentNodes':item.parentNodes,
          'revNo':item.revNo,
          'size':item.size,
          'sort':item.sort,
          'time':item.time,
          'type':item.type,
        };
        this.$set(this.report.report_files, this.editImageIndex, resObjTemp);

        this.isEditImage = false;
        this.editImageIndex = -1;
        this.imageList = [];
        this.selectedImage = [];
      },

      reIndexSort(sort,report_files){
        let report = report_files;
        for (let i = 0;i<report.length;i++){
          if (sort < report[i].sort){
            report[i].sort -= 1;
          }
        }
        return report;
      },

      addSelectedItems: function (datas) {
        if (datas.length){
          for (let item of datas) {
            if (this.report.report_files.length !== 0) {
              let existFlag = false;
              for (let i = 0;i<this.report.report_files.length;i++){
                if (this.report && item.file_path === this.report.report_files[i].file_path){
                  existFlag = true;
                  // existFilePath = this.report.report_files[i].file_path;
                  // this.report.report_files.splice(i,1);
                  break;
                }
              }
              if (existFlag === false) {
                this.report.report_files.push(item);
                if (!item.report_date) {
                  //【簡易レポート】設定した日付を自動反映#3252
                  this.$set(this.report.report_files[this.report.report_files.length - 1], 'report_date', new Date(Calendar.dateFormat(this.report.report_date)));
                }
              }
            } else {
              this.report.report_files.push(item);
              if (!item.report_date) {
                //【簡易レポート】設定した日付を自動反映#3252
                this.$set(this.report.report_files[this.report.report_files.length-1], 'report_date', new Date(Calendar.dateFormat(this.report.report_date)));
              }
            }
          }
        } else {
          for (let i = 0; i < this.report.report_files.length; i++) {
            if (this.report.report_files[i].id !== 0) {
              delete this.report.report_files[i];
            }
          }
          //reindex
          this.report.report_files = this.reIndexArr(this.report.report_files);
        }

        if (this.removePickItems && this.removePickItems.length !== 0){
          for (let i = 0; i< this.removePickItems.length;i++){
            for (let j = 0; j< this.report.report_files.length;j++){
              if (this.removePickItems[i].file_path === this.report.report_files[j].file_path) {
                delete this.report.report_files[j];
              }
            }
          }
          //reindex
          this.report.report_files = this.reIndexArr(this.report.report_files);
          this.removePickItems = [];
        }
      },

      //インデックスを再作成
      reIndexArr(arr){
        let res = [];
        for(var i=0;i<arr.length;i++){
          if(typeof(arr[i])!='undefined'){
            res.push(arr[i]);
          }
        }
        return res;
      },
      handleRemove(file, fileList) {
        console.log(file, fileList);
      },
      handlePreview(file) {
        console.log(file);
      },
      handleExceed(files, fileList) {
        this.$message.warning('too much files has been selected');
      },
      beforeRemove(file, fileList) {
        return this.$confirm('Remove?');
      },
      addToList(response, file, fileList) {
        let fileObj = response.data;
        console.log(fileObj);
        this.report.report_files.push(fileObj);
        this.fileList = [];
      },
      //ドナー情報を取得する
      fetchReportDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/report/detail", {
          params: {
            id: this.$route.params.id
          }
        }).then((res) => {
          this.report = res.data;
          const baseSourceUrl = process.env.MIX_APP_DIR + 'api/getDocFileThumb'; //get image file's cover
          for (let i = 0; i< this.report.report_files.length; i++) {
            if (this.report.report_files[i].file_path != "images/no-image.png") {
              let filePathTempArr = this.report.report_files[i].file_path.split('/');
              let nodeId = filePathTempArr[0];
              let revNo = filePathTempArr[1];
              this.report.report_files[i].file_path_url = baseSourceUrl + '/' + nodeId +'/' + revNo;
            } else {
              //no image
            }

          }
          loading.close();
        }).catch(err => {
          let errMsg = this.commonMessage.error.system;
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      dateFormat(date){
        let dateArray = date.split('-');
        let res = dateArray[0] + '年' + dateArray[1] + '月' + dateArray[2] + '日';
        return res;
      }
    },
    created() {
      if (this.$route.name === 'ReportDetail'){
        this.fetchReportDetail();
      } else {
        this.initForm();
      }
    }
  }
</script>
<style scoped>
  .customer-collapse {
    border-collapse: collapse;
  }
</style>
