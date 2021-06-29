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
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item v-if="infoForm" :to="{ path: '/report', query: {flag:'success'}}"><span>簡易レポート一覧</span>
          </el-breadcrumb-item>
          <el-breadcrumb-item v-if="imageSelect" :to="{ path: '/report', query: {flag:'success'}}"><span>簡易レポート一覧</span>
            > <span>チャット</span> > 〇〇案件フォルダ
          </el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <div class="container clearfix commonAll">
      <section class="common-wrapper" v-if="infoForm">
        <div class="common-view">
          <div class="content customerdetail-nametop">
            <el-form ref="form" :model="report" :rules="rules" label-width="200px" @submit.native.prevent
                     class="formStyle updateFormStyle" onkeypress="if (event.keyCode === 13) return false;">
              <div class="form-group">
                <el-form-item :label="reportName('report_date')" class="nameupdata fristinput" prop="report_date">
                  <el-date-picker class="scheduleadd-fristdata" type="date" v-model="report.report_date"
                                  value-format="yyyy-MM-dd" :clearable="clearable"></el-date-picker>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="reportName('user_name')">
                  <el-input disabled="disabled" v-if="report.user" v-model="report.user.name" maxlength="50"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="reportName('location')" prop="location">
                  <el-input v-model="report.location" maxlength="50"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="reportName('type')" prop="type">
                  <li style="margin-left: 30px;">
                    <el-radio v-model="report.type" label="1">{{reportName('typeItem1')}}</el-radio>
                    <el-radio v-model="report.type" label="2">{{reportName('typeItem2')}}</el-radio>
                  </li>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="reportName('imagePicker')">
                  <el-button type="success" round @click="imagePick">写真選択</el-button>
                </el-form-item>
              </div>

              <div class="form-group" v-if="report.report_files" v-for="(item,index) in report.report_files">
                <ImageList :imageData="item" :sortName="(index+1)" :key="'report+'+item.id"
                           @delImage="delImage"></ImageList>
              </div>

              <div class="updateButton">
                <a class="updateBack" @click="deleteReport">キャンセル</a>
                <a class="updateSave" @click="save">一時保存</a>
                <a class="updateSave" @click="pdfExport">レポート作成</a>
              </div>
            </el-form>
          </div>
        </div>
      </section>

      <section class="report-imglist" v-if="imageSelect">
        <div class="common-view">
          <ul class="imglist clearfix">
            <li v-if="imageList" :key="'l'+index" v-for="(itemList,index) in imageList" @click="addChecked(itemList)">
              <el-image :src="itemList.file_name" fit="contain"></el-image>
              <p v-if="itemList.isChecked">{{itemList.sort}}</p>
            </li>
          </ul>
          <div class="modelBut">
            <button class="updateBack" @click="closeImagePick">キャンセル</button>
            <button class="updateSave" @click="save">保存</button>
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

  export default {
    name: "ReportDetail",
    mixins: [ReportCols, validation, Messages], //定数
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
        imageList: [],
        selectedImage: []
      };
    },
    methods: {
      //ドナー情報を取得する
      fetchReportDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/report/detail", {
          params: {
            id: this.$route.params.id
          }
        }).then((res) => {
          loading.close();
          this.report = res.data;
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
        let fieldErrMsg = this.commonMessage.error.formValidFail;
        this.report.export = false;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post("/api/updateReport", {
              report: this.report
            }).then(res => {
              loading.close();
            }).catch(err => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            this.$alert(fieldErrMsg, {showClose: false});
          }
        })
      },
      pdfExport: function () {
        // this.$router.push({path: '/api/getReportPdf/' + this.report.id});
        let fieldErrMsg = this.commonMessage.error.formValidFail;
        let errMsg = this.commonMessage.error.system;
        this.report.export = true;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post("/api/updateReport", {
              report: this.report
            }).then((res) => {
              if (res.data.filePath) {
                loading.close();
                this.$alert(res.data.filePath, {showClose: false});
              } else {
                loading.close();
                this.$alert(errMsg, {showClose: false});
              }
            }).catch(err => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            this.$alert(fieldErrMsg, {showClose: false});
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
        console.log(this.report);
        let deleteNotify = this.report.project.construction_name + '入力を破棄して？';
        this.$confirm(deleteNotify).then(() => {
          loading.close();
          this.$router.push({path: "/report", query: {flag: 'success'}});
        }).catch(action => {
          loading.close();
        });
      },
      imagePick() {
        this.infoForm = false;
        this.imageSelect = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getProjectFiles", {
          params: {
            projectId: this.report.project_id
          }
        }).then((res) => {
          let imageTmpArr = res.data;
          for (let i = 0; i < imageTmpArr.length; i++) {
            imageTmpArr[i]['isChecked'] = false;
            imageTmpArr[i]['sort'] = 0;
          }
          this.imageList = imageTmpArr;
          loading.close();
        }).catch(err => {
          let errMsg = this.commonMessage.error.system;
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      addChecked(item) {
        let id = item.id;
        let num = this.selectedImage.length;
        let numTotal = this.imageList.length;
        let sort = num + 1;
        for (let i = 0; i < numTotal; i++) {
          if (this.imageList[i]['id'] === id) {
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
        this.removeItem(this.imageList[i]['id']);
        this.resortList(sortThis);
      },
      addPick(i, item, sort) {
        this.imageList[i]['isChecked'] = true;
        this.imageList[i]['sort'] = sort;
        this.selectedImage.push(item);
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
            this.selectedImage.splice(i);
            return;
          }
        }
      },
      closeImagePick() {
        this.infoForm = true;
        this.imageSelect = false;
        //return selected items
        this.addSelectedItems(this.selectedImage);
        this.imageList = [];
        this.selectedImage = [];
      },
      delImage: function (id) {
        let num = this.report.report_files.length;
        if (num > 0) {
          for (let i = 0; i < num; i++) {
            if (this.report.report_files[i].id == id) {
              this.report.report_files.splice(i, 1);
              return true;
            }
          }
        }
      },
      addSelectedItems: function (datas) {
        //todo
      }
    },
    created() {
      this.fetchReportDetail();
    }
  }
</script>
<style scoped>
  .customer-collapse {
    border-collapse: collapse;
  }
</style>
