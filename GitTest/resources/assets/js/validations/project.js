import common from './common'

export default {
  mixins: [common],
  data() {
    let telOutConfirm = (rule, value, callback) => {
      if (value !== "") {
          callback()
        // let length = 0;
        // if (typeof this.projectBasis.telOut !== "undefined" && this.projectBasis.telOut.length !== 0) {
        //   length += this.projectBasis.telOut.length;
        // }
        // if (typeof this.projectBasis.telIn !== "undefined" && this.projectBasis.telIn.length !== 0) {
        //   length += this.projectBasis.telIn.length;
        // }
        // if ((typeof this.projectBasis.telIn !== "undefined" && this.projectBasis.telIn.length !== 0) && length > 14) {
        //   callback(new Error(this.TEL_TOO_LONG_MESSAGE));
        // } else if (!(typeof this.projectBasis.telIn !== "undefined" && this.projectBasis.telIn.length !== 0) && length > 15) {
        //   callback(new Error(this.TEL_TOO_LONG_MESSAGE));
        // } else {
        //   callback()
        // }
      }
    };
    let zipCheck = (rule, value, callback) => {
        // 追加、一括編集
        if(this.$parent.isUpdateFlag == true || this.$parent.isCreateFlag == true){
          if (this.$parent.zipFlag !== value) {
            // 郵便番号の検証を行う状態
            this.$parent.doZipCheck = true;
            this.$parent.zipFlag = value;
            if (value) {
              // 郵便番号 部分更新
              const loading = this.$loading({
                lock: true,
                target: document.querySelector("#zipLoading")
              });
              axios.get('/api/zipCloud', {
                params: {
                  zipcode: value,
                }
              }).then((res) => {
                this.$parent.doZipCheck = false;
                let obj = this.projectBasis;
                if (this.setDest && this.setDest === 'project') {
                  obj = this.project;
                }
                if (!res.data) {
                  this.$set(obj, 'pref', "");
                  this.$set(obj, 'town', "");
                  this.$set(obj, 'street', "");
                  callback(new Error(this.ZIP_CHECK_MESSAGE));
                } else {
                  this.$parent.zipTmp = res.data;
                  if (this.$parent.zipTmp.address1) {
                    this.$set(obj, 'pref', this.$parent.zipTmp.address1);
                  }
                  if (this.$parent.zipTmp.address2) {
                    this.$set(obj, 'town', this.$parent.zipTmp.address2);
                  }
                  if (this.$parent.zipTmp.address3) {
                    this.$set(obj, 'street', this.$parent.zipTmp.address3);
                  }
                  callback();
                }
                loading.close();
                if(this.$parent.isCreateFlag == true){
                  // 保存操作を行う状態
                  if(this.$parent.doSubmitForm){
                    this.validation();
                  }
                }else{
                  if(this.$parent.doSubmitForm){
                    this.$parent.submitFormAll();
                  }
                }
              }).catch((err) => {
              });
            } else {
              callback();
            }
          } else {
            if (value && !this.$parent.zipTmp){
              callback(new Error(this.ZIP_CHECK_MESSAGE));
            }else if (this.$parent.zipTmp!==undefined && value!==''){
              if (this.$parent.zipTmp.zipcode!==value){
                callback(new Error(this.ZIP_CHECK_MESSAGE));
              }
            }
            callback();
          }
        }else{  // 案件基本情報編集
          if (this.zipFlag !== value) {
            this.doZipCheck = true;
            this.zipFlag = value;
            if (value) {
              const loading = this.$loading({
                lock: true,
                target: document.querySelector("#zipLoading")
              });
              axios.get('/api/zipCloud', {
                params: {
                  zipcode: value,
                }
              }).then((res) => {
                this.doZipCheck = false;
                let obj = this.projectBasis;
                if (this.setDest && this.setDest === 'project') {
                  obj = this.project;
                }
                if (!res.data) {
                  this.$set(obj, 'pref', "");
                  this.$set(obj, 'town', "");
                  this.$set(obj, 'street', "");
                  callback(new Error(this.ZIP_CHECK_MESSAGE));
                } else {
                  this.zipTmp = res.data;
                  if (this.zipTmp.address1) {
                    this.$set(obj, 'pref', this.zipTmp.address1);
                  }
                  if (this.zipTmp.address2) {
                    this.$set(obj, 'town', this.zipTmp.address2);
                  }
                  if (this.zipTmp.address3) {
                    this.$set(obj, 'street', this.zipTmp.address3);
                  }
                  callback();
                }
                loading.close();
                if(this.doSubmitForm){
                  this.submitForm();
                }
              }).catch((err) => {
              });
            } else {
              callback();
            }
          } else {
            if (value && !this.zipTmp){
              callback(new Error(this.ZIP_CHECK_MESSAGE));
            }else if (this.zipTmp!==undefined && value!==''){
              if (this.zipTmp.zipcode!==value){
                callback(new Error(this.ZIP_CHECK_MESSAGE));
              }
            }
            callback();
          }
        }
      };

    return {
      rules: {
        place_name: [
          this.required(['change', 'blur']),
          this.max(50),
        ],
        //#2790 add field name project_no
        project_no: [
          // this.required(['change', 'blur']),
          this.projectNo(),
          this.max(50),
        ],
        zip: [
          this.max(7),
          this.number(),
          this.customValidate(zipCheck),
        ],
        pref: [
          this.max(20),
        ],
        town: [
          this.max(50),
        ],
        street: [
          this.max(50),
        ],
        telOut: [
          this.number(),
          this.customValidate(telOutConfirm),
        ],
        telIn: [
          this.number(),
        ],
        police_station_tel: [
          this.number(),
        ],
        fire_station_tel: [
          this.number(),
        ],
        security_management_tel: [
          this.number(),
        ],
        realtor_tel: [
          this.number(),
        ],
        mng_company_tel: [
          this.number(),
        ],
        site_area:[
              this.number(),
        ],
        floor_area:[
              this.number(),
        ],
        floor_numbers:[
              this.number(),
        ]
      },
      hospitalRules: {
        name: [
          this.required(['change', 'blur']),
          this.max(50),
        ],
        tel: [
          this.max(15),
          this.number(),
        ]
      },
      localeChiefRules: {
        name: [
          this.required(['change', 'blur']),
          this.max(30),
        ],
        position: [
          this.max(20),
        ],
        tel: [
          this.max(15),
          this.number(),
        ],
        mail: [
          this.max(191),
          this.email(),
        ],
      },
      tradesChiefRules: {
        trades_type: [
          this.required(['change', 'blur']),
        ],
        name: [
          this.required(['change', 'blur']),
          this.max(30),
        ],
        tel: [
          this.max(15),
          this.number(),
        ]
      },
    }
  },
  methods: {
    imageValidate: function (file) {
      let error = this.imageType(file);
      if (error) {
        return error;
      }
      error = this.imageSize(file, APP_IMAGE_SIZE_LIMIT);
      if (error) {
        return error;
      }
    },
  }
}
