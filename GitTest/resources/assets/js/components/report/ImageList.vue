<template>
  <div class="updateFormStyle">
    <div>
      <el-form ref="form" :model="imageData" :rules="imageRules" label-width="100px" >
        <div class="updateFormStyle-left">
          <el-form-item>
            <div class="upImg">
              <li class="imgName">{{imageData.fileName}}</li>
              <li style="display: flex">
                <el-button class="report-imageindex" icon="el-icon-imgupload" @click="rePickImage(imageIndex)">写真選択</el-button>
                <el-button class=" danger"  icon="el-icon-danger" type="success" @click="delImage(imageIndex)">写真削除</el-button>
              </li>
            </div>
            <el-image
                    style="width: 100%; height: 200px;margin: 50px 0px 25px;"
                    :src="imageData.file_path_url"
                    fit="contain"></el-image>
          </el-form-item>
        </div>
      <div>
        <el-form-item label="作業日" prop="report_date">
          <el-date-picker class="scheduleadd-fristdata"
                          type="date"
                          v-model="imageData.report_date"
                          value-format="yyyy-MM-dd"
                          :clearable="clearable">

          </el-date-picker>
        </el-form-item>
        <el-form-item label="作業箇所" prop="work_place">
          <el-input v-model="imageData.work_place" maxlength="100"></el-input>
        </el-form-item>
        <el-form-item label="天気" prop="weather">
          <el-input v-model="imageData.weather" maxlength="50"></el-input>
        </el-form-item>
        <el-form-item  class="report-textarea" prop="comment">
          <el-input type="textarea" placeholder="作業内容" v-model="imageData.comment"></el-input>
        </el-form-item>
      </div>

      </el-form>
    </div>
    <div class="upBut">
      <el-button class="danger" type="success"  icon="el-icon-danger"  @click="delImageAndComment(imageIndex)">ブロック削除</el-button>
    </div>

  </div>

</template>

<script>
  import ReportCols from '../../mixins/ReportCols';
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/report';

  export default {
    name: "ImageList",
    mixins: [ReportCols, Messages, validation], //定数
    props: {
      imageData: null,
      sortName: '',
    },
    data: function () {
      return {
        imageIndex: this.sortName - 1,
        clearable: false,
      };
    },
    methods: {
      delImage(index) {
        this.$emit('delImage', index);
      },

      rePickImage(index) {
        this.$emit('rePickImage', index);
      },

      delImageAndComment(index) {
        this.$emit('delImageAndComment', index);
      }
    }
  }
</script>

<style scoped>

</style>
