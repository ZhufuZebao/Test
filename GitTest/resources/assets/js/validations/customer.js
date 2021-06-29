import common from './common'

export default {
  mixins: [common],
  data() {
    let telOutConfirm = (rule, value, callback) => {
      if (value !== "") {
          callback()
        // let length = 0;
        // if (typeof this.office.telOut !== "undefined" && this.office.telOut.length !== 0) {
        //   length += this.office.telOut.length;
        // }
        // if (typeof this.office.telIn !== "undefined" && this.office.telIn.length !== 0) {
        //   length += this.office.telIn.length;
        // }
        // if ((typeof this.office.telIn !== "undefined" && this.office.telIn.length !== 0) && length > 14) {
        //   callback(new Error(this.TEL_TOO_LONG_MESSAGE));
        // } else if (!(typeof this.office.telIn !== "undefined" && this.office.telIn.length !== 0) && length > 15) {
        //   callback(new Error(this.TEL_TOO_LONG_MESSAGE));
        // } else {
        //   callback()
        // }
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
            if (!res.data) {
              this.$set(this.office, 'pref', "");
              this.$set(this.office, 'town', "");
              this.$set(this.office, 'street', "");
              callback(new Error(this.ZIP_CHECK_MESSAGE));
            } else {
              this.zipTmp = res.data;
              if (this.zipTmp.address1) {
                this.$set(this.office, 'pref', this.zipTmp.address1);
              }
              if (this.zipTmp.address2) {
                this.$set(this.office, 'town', this.zipTmp.address2);
              }
              if (this.zipTmp.address3) {
                this.$set(this.office, 'street', this.zipTmp.address3);
              }
              callback();
            }
            if (!this.isSubmit) {
              loading.close();
            }
          }).catch((err) => {
          });
        }else{
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
    };
    let zipCheckBilling = (rule, value, callback) => {
      if (this.bilZipFlag !== value) {
        this.bilZipFlag = value;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/zipCloud', {
          params: {
            zipcode: value,
          }
        }).then((res) => {
          if (!res.data) {
            this.$set(this.billing, 'pref', "");
            this.$set(this.billing, 'town', "");
            this.$set(this.billing, 'street', "");
            callback(new Error(this.ZIP_CHECK_MESSAGE));
          } else {
            this.zipTmp = res.data;
            if (this.zipTmp.address1) {
              this.$set(this.billing, 'pref', this.zipTmp.address1);
            }
            if (this.zipTmp.address2) {
              this.$set(this.billing, 'town', this.zipTmp.address2);
            }
            if (this.zipTmp.address3) {
              this.$set(this.billing, 'street', this.zipTmp.address3);
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
    };

    let zipCheckBillingEdit = (rule, value, callback) => {
      let index=parseInt(rule.field.split('.')[1]);
      if (this.zipFlagEdit[index]!== value) {
        this.zipFlagEdit[index]=value;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/zipCloud', {
          params: {
            zipcode: value,
          }
        }).then((res) => {
          if (!res.data) {
            this.$set(this.billingsTmp, 'pref', "");
            this.$set(this.billingsTmp, 'town', "");
            this.$set(this.billingsTmp, 'street', "");
            callback(new Error(this.ZIP_CHECK_MESSAGE));
          } else {
            this.zipTmp = res.data;
            let index_change = this.billingsTmp.filter(function (item) {
              return item.zip === value;
            });
            let num = index_change.length;
            let index_used = [];
            for (let i = 0; i < num; i++) {
              index_used.push(this.billingsTmp.findIndex(function (item) {
                return item.id === index_change[i]['id'];
              }))
            }
            let num_used = index_used.length;
            for (let l = 0; l < num_used; l++) {
              if (this.zipTmp.address1) {
                this.$set(this.billingsTmp[index_used[l]], 'pref', this.zipTmp.address1);
              }
              if (this.zipTmp.address2) {
                this.$set(this.billingsTmp[index_used[l]], 'town', this.zipTmp.address2);
              }
              if (this.zipTmp.address3) {
                this.$set(this.billingsTmp[index_used[l]], 'street', this.zipTmp.address3);
              }
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
      customerRules: {
        name: [
          this.customValidate(trimSpace),
          this.required(['change', 'blur']),
          this.max(50),
        ],
        phonetic: [
          this.customValidate(trimSpace),
          this.required(['change', 'blur']),
          this.max(50),
        ],
      },
      customerOfficeRules: {
        name: [
          this.max(50),
        ],
        zip: [
          this.required(['change', 'blur']),
          this.number(),
          this.max(7),
          this.customValidate(zipCheck),
        ],
        pref: [
          this.required(['change', 'blur']),
          this.max(4),
        ],
        town: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        street: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        telOut: [
          this.required(['change', 'blur']),
          this.number(),
          this.customValidate(telOutConfirm),
        ],
        telIn: [
          this.number(),
        ],
      },
      customerOfficePersonRules: {
        name: [
          this.required(['change', 'blur']),
          this.max(30),
        ],
        position: [
          this.max(20),
        ],
        dept: [
          this.max(20),
        ],
        role: [
          this.max(20),
        ],
        email: [
          this.max(254),
          this.email(),
        ],
        tel: [
          this.max(15),
          this.number(),
        ],
      },
      customerOfficeBillingRules: {
        name: [
          this.required(['change', 'blur']),
          this.max(30),
        ],
        zip: [
          this.required(['change', 'blur']),
          this.number(),
          this.max(7),
          this.customValidate(zipCheckBilling),
        ],
        zipEdit: [
          this.required(['change', 'blur']),
          this.number(),
          this.max(7),
          this.customValidate(zipCheckBillingEdit),
        ],
        pref: [
          this.required(['change', 'blur']),
          this.max(4),
        ],
        town: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        street: [
          this.required(['change', 'blur']),
          this.max(20),
        ],
        tel: [
          this.required(['change', 'blur']),
          this.max(15),
          this.number(),
        ],
      },
    }
  }
}
