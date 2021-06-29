import common from './common'

export default {
  mixins: [common],
  data() {
    let editPassword1Confirm = (rule, value, callback) => {
      if (value !== "") {
        let value_r = $.trim(value);
        if(value_r && value && value_r.length !== value.length){
          callback(new Error(this.PASSWORD_SPACE_MESSAGE));
        }
        if (typeof this.account.editPasswordConfirm !== "undefined" && this.account.editPasswordConfirm.length !== 0) {
          this.$refs.form.validateField("editPasswordConfirm");
        }
        callback();
      }
    };
    let editPassword2Confirm = (rule, value, callback) => {
      if (value !== this.account.editPassword) {
        callback(new Error(this.PASSWORD_DIFFERENT_MESSAGE));
      } else {
        let value_r = $.trim(value);
        if(value_r && value && value_r.length !== value.length){
          callback(new Error(this.PASSWORD_SPACE_MESSAGE));
        }
        callback();
      }
    };
    let password1Confirm = (rule, value, callback) => {
      if (value !== "") {
        let value_r = $.trim(value);
        if(value_r.length !== value.length){
          callback(new Error(this.PASSWORD_SPACE_MESSAGE));
        }
        if (typeof this.account.passwordConfirm !== "undefined" && this.account.passwordConfirm.length !== 0) {
          this.$refs.form.validateField("passwordConfirm");
        }
        callback();
      }
    };
    let password2Confirm = (rule, value, callback) => {
      if (value !== this.account.password) {
        callback(new Error(this.PASSWORD_DIFFERENT_MESSAGE));
      } else {
        let value_r = $.trim(value);
        if(value_r.length !== value.length){
          callback(new Error(this.PASSWORD_SPACE_MESSAGE));
        }
        callback();
      }
    };
    let editMailConfirm = (rule, value, callback) => {
      axios.post('/api/editMailConfirm', {
        id:this.accountId,
        emailAddress: value
      }).then(res => {
        if(res.data === 0){
          this.$set(this.account,'disable',true);
          callback(new Error(this.EMAIL_UNIQUE_MESSAGE));
        } else {
          this.$set(this.account,'disable',false);
          callback();
        }
      });
    }
    // メール唯一性の確認
    let newMailConfirm = (rule, value, callback) => {
      axios.post('/api/mailConfirm', {
        emailAddress: value
      }).then(res => {
        if (res.data === 1) {
          axios.post('/api/mailExist', {
            emailAddress: value
          }).then(res =>{
            let obj = this.account;
            if(res.data === 0){
              this.$set(obj,'disable',false);
              callback(new Error(this.EMAIL_UNIQUE_MESSAGE));
            } else {
              let emailTmp = res.data;
              if(emailTmp.name){
                this.$set(obj,'name',emailTmp.name);
                this.$set(obj,'last_name',emailTmp.last_name);
                this.$set(obj,'first_name',emailTmp.first_name);
                this.$set(obj,'password','secret');
                this.$set(obj,'passwordConfirm','secret');
                this.$set(obj,'disable',true);
              }
              callback();
            }
          });
        } else {
          let obj = this.account;
          this.$set(obj,'disable',false);
          callback();
        }
      });
    };
    return {
      rules: {
        editEmail:[
          this.required(),
          this.email(),
          this.max(191),
          this.customValidate(editMailConfirm),
        ],
        name: [
          this.required(),
          this.max(30),
        ],
        email: [
          this.required(),
          this.email(),
          this.max(191),
          this.customValidate(newMailConfirm),
        ],
        editPassword: [
          this.max(100),
          this.min(6),
          this.customValidate(editPassword1Confirm),
        ],
        editPasswordConfirm: [
          this.max(100),
          this.min(6),
          this.customValidate(editPassword2Confirm),
        ],
        password: [
          this.required(),
          this.max(100),
          this.min(6),
          this.password(),
          this.customValidate(password1Confirm),
        ],
        passwordConfirm: [
          this.required(),
          this.max(100),
          this.min(6),
          this.password(),
          this.customValidate(password2Confirm),
        ],
        last_name: [
          this.required(),
          this.max(59),
        ],
        first_name: [
          this.required(),
          this.max(59),
        ],
      }
    }
  }
}
