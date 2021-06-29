import common from './common'
import Messages from "../mixins/Messages";

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
                        let obj = this.companyBasis;
                        if (this.setDest && this.setDest === 'company') {
                            obj = this.company;
                        }
                        if (!res.data) {
                            this.$set(obj, 'pref', "");
                            this.$set(obj, 'town', "");
                            this.$set(obj, 'street', "");
                            errorFlag = false;
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
                            let obj = this.companyBasis;
                            if (this.setDest && this.setDest === 'company') {
                                obj = this.company;
                            }
                            if (!res.data) {
                                this.$set(obj, 'pref', "");
                                this.$set(obj, 'town', "");
                                this.$set(obj, 'street', "");
                                errorFlag = false;
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
            }
        };

        return {
            rules: {
                type: [
                    this.required(['change', 'blur']),
                ],
                name: [
                    this.required(['change', 'blur']),
                    this.max(50),
                ],
                phonetic: [
                    this.required(['change', 'blur']),
                    this.max(50),
                ],
                zip: [
                    this.required(['change', 'blur']),
                    this.max(7),
                    this.number(),
                    this.customValidate(zipCheck),
                ],
                pref: [
                    this.required(['change', 'blur']),
                    this.max(4),
                ],
                town: [
                    this.required(['change', 'blur']),
                    this.max(50),
                ],
                street: [
                    this.required(['change', 'blur']),
                    this.max(50),
                ],
                tel: [
                    this.required(['change', 'blur']),
                    this.number(),
                    //    this.customValidate(telOutConfirm),
                ],
            },

            localeChiefRules: {
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
                mail: [
                    this.max(191),
                    this.email(),
                ],
                email: [
                    this.max(191),
                    this.email(),
                ],
                tel: [
                    this.max(15),
                    this.number(),
                ],
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
