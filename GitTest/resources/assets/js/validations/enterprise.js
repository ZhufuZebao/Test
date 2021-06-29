import common from './common'
import Messages from '../mixins/Messages'

export default {
  mixins: [common, Messages],
  data() {
    let password1Confirm = (rule, value, callback) => {
      if (value !== "") {
        if (typeof this.enterprise.password_confirmation !== "undefined" && this.enterprise.password_confirmation.length !== 0) {
          this.$refs.form.validateField("password_confirmation");
        }
        callback()
      }
    };
    let password2Confirm = (rule, value, callback) => {
      if (value !== this.enterprise.userPassword) {
        callback(new Error(this.PASSWORD_DIFFERENT_MESSAGE));
      } else {
        callback();
      }
    };
    let emailUnique = (rule, value, callback) => {
      const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
      let errMsg = this.commonMessage.error.system;
      axios.post('/api/mailUnique', {
        emailAddress: value
      }).then((res) => {
        loading.close();
        if (res.data === 1) {
          callback(new Error(this.EMAIL_UNIQUE_MESSAGE));
        } else {
          callback();
        }
      }).catch(e => {
        loading.close();
        this.$alert(errMsg, {showClose: false});
      });
    };
    let zipCheck = (rule, value, callback) => {
      if (this.zipFlag !== value) {
        this.zipFlag = value;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.system;
        axios.get('/api/zipCloud', {
          params: {
            zipcode: value,
          }
        }).then((res) => {
          if (!res.data) {
            callback(new Error(this.ZIP_CHECK_MESSAGE));
          } else {
            this.zipTmp = res.data;
            if (this.zipTmp.address1) {
              this.enterprise.pref = this.zipTmp.address1;
            }
            if (this.zipTmp.address2) {
              this.enterprise.town = this.zipTmp.address2;
            }
            if (this.zipTmp.address3) {
              this.enterprise.street = this.zipTmp.address3;
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
      } else {
        callback();
      }
    };
    return {
      rules: {
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
          this.max(15),
          this.number(),
        ],
        userName: [
          this.required(),
          this.max(30),
        ],
        userEmail: [
          this.required(),
          this.email(),
          this.max(191),
          this.customValidate(emailUnique),
        ],
        userPassword: [
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
        emailKey: [
          this.required(),
        ],
        userLastName: [
          this.required(),
          this.max(59),
        ],
        userFirstName: [
          this.required(),
          this.max(59),
        ],
      }
    }
  }
}
