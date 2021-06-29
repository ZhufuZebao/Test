import common from './common'
//
export default {
  mixins: [common],
  data() {
    let password1Confirm = (rule, value, callback) => {
      if (value !== "") {
        if (typeof this.cooperators.password_confirmation !== "undefined" && this.cooperators.password_confirmation.length !== 0) {
          this.$refs.form.validateField("password_confirmation");
        }
        callback()
      }
    };
    let password2Confirm = (rule, value, callback) => {
      if (value !== this.cooperators.userPassword) {
        callback(new Error(this.PASSWORD_DIFFERENT_MESSAGE));
      } else {
        callback();
      }
    };
    let zipCheck = (rule, value, callback) => {
      if (this.zipFlag !== value) {
        this.zipFlag = value;
        if (value) {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.get('/api/zipCloud', {
            params: {
              zipcode: value,
            }
          }).then((res) => {
            let obj = this.cooperators;
            // if (this.setDest && this.setDest === 'project') {
            //   obj = this.project;
            // }
            if (!res.data) {
              this.$set(obj, 'pref', "");
              this.$set(obj, 'town', "");
              this.$set(obj, 'street', "");
              callback(new Error(this.ZIP_CHECK_MESSAGE));
            } else {
              let zipTmp = res.data;
              if (zipTmp.address1) {
                this.$set(obj, 'pref', zipTmp.address1);
              }
              if (zipTmp.address2) {
                this.$set(obj, 'town', zipTmp.address2);
              }
              if (zipTmp.address3) {
                this.$set(obj, 'street', zipTmp.address3);
              }
              callback();
            }
            if (!this.isSubmit) {
              loading.close();
            }
          }).catch((err) => {
          });
        } else {
          callback();
        }
      } else {
        callback();
      }
    };
    return {
      rules: {
        last_name: [
          this.required(),
          this.max(59),
        ],
        first_name: [
          this.required(),
          this.max(59),
        ],
        cooperator_name: [
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
          this.max(30),
        ],
        street: [
          this.max(20),
        ],
        house: [
          this.max(70),
        ],
        tel: [
          this.max(15),
          this.number(),
        ],
        userPassword: [
          this.required(),
          this.max(100),
          this.min(6),
          this.password(),
          this.customValidate(password1Confirm),
        ],
        password_confirmation: [
          this.required(),
          this.max(100),
          this.min(6),
          this.password(),
          this.customValidate(password2Confirm),
        ],
        email: [
          this.email(),
          this.max(191),
        ],
      }
    }
  }
}
