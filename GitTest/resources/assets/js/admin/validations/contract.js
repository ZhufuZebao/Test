import common from '../../validations/common'
import Messages from "../mixins/Messages";
//
export default {
  mixins: [common,Messages],
  data() {
    let errorFlag = true;
    let zipCheck = (rule, value, callback) => {
      if (this.zipFlag !== value) {
        this.zipFlag = value;
        if (value) {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let errMsg = this.commonMessage.error.system;
          axios.get('/api/zipCloud', {
            params: {
              zipcode: value,
            }
          }).then((res) => {
            let obj = this.contractEnterprise;
            // if (this.setDest && this.setDest === 'project') {
            //   obj = this.project;
            // }
            if (!res.data) {
              this.$set(obj, 'pref', "");
              this.$set(obj, 'town', "");
              this.$set(obj, 'street', "");
              errorFlag = false;
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
              errorFlag = true;
              callback();
            }
            if (!this.isSubmit) {
              loading.close();
            }
          }).catch((err) => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          });
        } else {
          callback();
        }
      } else {
        if (errorFlag) {
          callback();
        } else {
          if (value) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let errMsg = this.commonMessage.error.system;
            axios.get('/api/zipCloud', {
              params: {
                zipcode: value,
              }
            }).then((res) => {
              let obj = this.contractEnterprise;
              // if (this.setDest && this.setDest === 'project') {
              //   obj = this.project;
              // }
              if (!res.data) {
                this.$set(obj, 'pref', "");
                this.$set(obj, 'town', "");
                this.$set(obj, 'street', "");
                errorFlag = false;
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
                errorFlag = true;
                callback();
              }
              if (!this.isSubmit) {
                loading.close();
              }
            }).catch((err) => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            callback();
          }
        }
      };
    };

    let errorFlag2 = true;
    let zipCheck2 = (rule, value, callback) => {
      if (this.zipFlag !== value) {
        this.zipFlag = value;
        if (value) {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let errMsg = this.commonMessage.error.system;
          axios.get('/api/zipCloud', {
            params: {
              zipcode: value,
            }
          }).then((res) => {
            let obj = this.contractor;
            // if (this.setDest && this.setDest === 'project') {
            //   obj = this.project;
            // }
            if (!res.data) {
              this.$set(obj, 'pref', "");
              this.$set(obj, 'town', "");
              this.$set(obj, 'street', "");
              errorFlag2 = false;
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
              errorFlag2 = true;
              callback();
            }
            if (!this.isSubmit) {
              loading.close();
            }
          }).catch((err) => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          });
        } else {
          callback();
        }
      } else {
        if (errorFlag2) {
          callback();
        } else {
          if (value) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let errMsg = this.commonMessage.error.system;
            axios.get('/api/zipCloud', {
              params: {
                zipcode: value,
              }
            }).then((res) => {
              let obj = this.contractor;
              // if (this.setDest && this.setDest === 'project') {
              //   obj = this.project;
              // }
              if (!res.data) {
                this.$set(obj, 'pref', "");
                this.$set(obj, 'town', "");
                this.$set(obj, 'street', "");
                errorFlag2 = false;
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
                errorFlag2 = true;
                callback();
              }
              if (!this.isSubmit) {
                loading.close();
              }
            }).catch((err) => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            callback();
          }
        }
      };
    };

    let errorFlag3 = true;
    let zipCheck3 = (rule, value, callback) => {
      if (this.zipFlag !== value) {
        this.zipFlag = value;
        if (value) {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let errMsg = this.commonMessage.error.system;
          axios.get('/api/zipCloud', {
            params: {
              zipcode: value,
            }
          }).then((res) => {
            let obj = this.enterprise;
            // if (this.setDest && this.setDest === 'project') {
            //   obj = this.project;
            // }
            if (!res.data) {
              this.$set(obj, 'pref', "");
              this.$set(obj, 'town', "");
              this.$set(obj, 'street', "");
              errorFlag3 = false;
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
              errorFlag3 = true;
              callback();
            }
            if (!this.isSubmit) {
              loading.close();
            }
          }).catch((err) => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
          });
        } else {
          callback();
        }
      } else {
        if (errorFlag3) {
          callback();
        } else {
          if (value) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let errMsg = this.commonMessage.error.system;
            axios.get('/api/zipCloud', {
              params: {
                zipcode: value,
              }
            }).then((res) => {
              let obj = this.enterprise;
              // if (this.setDest && this.setDest === 'project') {
              //   obj = this.project;
              // }
              if (!res.data) {
                this.$set(obj, 'pref', "");
                this.$set(obj, 'town', "");
                this.$set(obj, 'street', "");
                errorFlag3 = false;
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
                errorFlag3 = true;
                callback();
              }
              if (!this.isSubmit) {
                loading.close();
              }
            }).catch((err) => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          } else {
            callback();
          }
        }
      };
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
    return {
      rules: {
        name: [
          this.required(),
          this.max(50),
        ],
        zip: [
          this.max(7),
          this.number(),
          this.required(),
          this.customValidate(zipCheck),
        ],
        pref: [
          this.required(),
          this.max(20),
        ],
        town: [
          this.required(),
          this.max(30),
        ],
        street: [
          this.required(),
          this.max(20),
        ],
        house: [
          this.max(70),
        ],
        tel: [
          this.max(15),
          this.number(),
          this.required(),
        ],
      },
      contractEnterpriseRules:{
        amount: [
          this.required(),
          this.number(),
        ],
        storage: [
          this.required(),
          this.max(10),
          this.number(),
        ],
      },
      contractorRules:{
        name: [
          this.required(),
          this.max(50),
        ],
        zip: [
          this.max(7),
          this.number(),
          this.required(),
          this.customValidate(zipCheck2),
        ],
        pref: [
          this.required(),
          this.max(20),
        ],
        town: [
          this.required(),
          this.max(30),
        ],
        street: [
          this.required(),
          this.max(20),
        ],
        house: [
          this.max(70),
        ],
        tel: [
          this.max(15),
          this.number(),
          this.required(),
        ],
        people: [
          this.required(),
          this.max(30),
        ],
      },
      enterpriseRules: {
        name: [
          this.required(),
          this.max(50),
        ],
        zip: [
          this.max(7),
          this.number(),
          this.required(),
          this.customValidate(zipCheck3),
        ],
        pref: [
          this.required(),
          this.max(20),
        ],
        town: [
          this.required(),
          this.max(30),
        ],
        street: [
          this.required(),
          this.max(20),
        ],
        house: [
          this.max(70),
        ],
        tel: [
          this.max(15),
          this.number(),
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
        ],
        amount: [
          this.number(),
        ],
      },
    }
  }
}
