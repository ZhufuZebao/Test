import common from './common'
import Messages from "../mixins/Messages";

export default {
  mixins: [common, Messages],
  data() {
    let oldPasswordConfirm = (rule, value, callback) => {
      const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
      let errMsg = this.commonMessage.error.system;
      axios.post('/api/getEnterprisesVerifyPwd', {
        pwd: value
      }).then(res => {
        loading.close();
        if (res.data === 1) {
          callback(new Error(this.PASSWORD_ERROR_MESSAGE));
        } else {
          callback();
        }
      }).catch(e => {
        loading.close();
        this.$alert(errMsg, {showClose: false});
      });

    };
    let password1Confirm = (rule, value, callback) => {
      if (value !== "") {
        if (typeof this.user.password_confirmation !== "undefined" && this.user.password_confirmation.length !== 0) {
          this.$refs.form.validateField("password_confirmation");
        }
        callback()
      }
    };
    let password2Confirm = (rule, value, callback) => {
      if (value !== this.user.password) {
        callback(new Error(this.PASSWORD_DIFFERENT_MESSAGE));
      } else {
        callback();
      }
    };
    let newMailConfirm = (rule, value, callback) => {
      //#1988 「変更する」ボタンを2回押さないと行けない を対応
      // const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
      let errMsg = this.commonMessage.error.system;
      axios.post('/api/editMailUnique', {
        emailAddress: value
      }).then(res => {
        // loading.close();
        if (res.data === 1) {
          callback(new Error(this.EMAIL_UNIQUE_MESSAGE));
        } else {
          callback();
        }
      }).catch(e => {
        // loading.close();
        this.$alert(errMsg, {showClose: false});
      });
      // if (!this.isSubmit) {
      //   loading.close();
      // }
    };
    let zipCheck = (rule, value, callback) => {
      if (this.zipFlag!==this.user.enterprise.zip) {
        this.zipFlag=this.user.enterprise.zip;
        if (value) {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let errMsg = this.commonMessage.error.system;
          axios.get('/api/zipCloud', {
            params: {
              zipcode: value,
            }
          }).then((res) => {
            if (!res.data) {
              this.user.enterprise.pref = '';
              this.user.enterprise.town = '';
              this.user.enterprise.street = '';
              callback(new Error(this.ZIP_CHECK_MESSAGE));
            } else {
              this.zipTmp = res.data;
              if (this.zipTmp.address1) {
                this.user.enterprise.pref = this.zipTmp.address1
              }
              if (this.zipTmp.address2) {
                this.user.enterprise.town = this.zipTmp.address2
              }
              if (this.zipTmp.address3) {
                this.user.enterprise.street = this.zipTmp.address3;
              }
              callback();
            }
          }).catch(e => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          });
          if (!this.isSubmit) {
            loading.close();
          }
        }else{
          callback();
        }
      }else{
        if (value && !this.zipTmp){
          callback(new Error(this.ZIP_CHECK_MESSAGE));
        }else if (this.zipTmp!==undefined && value!==''){
          if (this.zipTmp.zipcode!==value){
            callback(new Error(this.ZIP_CHECK_MESSAGE));
          }
        }
        callback();
      }
    };
    let trimSpace = (rule, value, callback) => {
      value = value.trim();
      if (value !== "") {
        callback()
      }else{
        callback(new Error(this.CREATE_SCHEDULE_ONLY_SPACE));
      }
    };
    return {
      pwd: {
        oldPassword: [
          this.required(),
          this.password(),
          this.max(100),
          this.min(6),
          this.customValidate(oldPasswordConfirm),
        ],
        password: [
          this.required(),
          this.password(),
          this.max(100),
          this.min(6),
          this.customValidate(password1Confirm),
        ],
        password_confirmation: [
          this.required(),
          this.password(),
          this.max(100),
          this.min(6),
          this.customValidate(password2Confirm),
        ],

      },
      enterprise: {
        mail: [
          this.required(),
          this.email(),
          this.max(191),
          this.customValidate(newMailConfirm),
        ],
        name: [
          this.required(),
          this.max(50),
        ],
        zip: [
          this.required(),
          this.max(7),
          this.number(),
          this.customValidate(zipCheck),
        ],
        pref: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        town: [
          this.required(['change', 'blur']),
          this.max(30),
        ],
        street: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        house: [
          this.max(70),
        ],
        tel: [
          this.required(),
          this.number(),
          this.max(15),
        ],
        last_name: [
          this.required(),
          this.max(59),
          this.customValidate(trimSpace),
        ],
        first_name: [
          this.required(),
          this.max(59),
          this.customValidate(trimSpace),
        ],
      },
    }
  }
}
