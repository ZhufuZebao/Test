<template>
  <section class="project-wrapper">
    <div class="common-view">
      <div class="customer-detail-modal-mask">
        <div class="modal-wrapper" @click.self="$emit('detailClose')">
          <div class="modal-container ">
            <div class="customerdetail clearfix">
              <div class="content">
                <div class="customerdetailoffice">
                    <h3>
                      TEST
                    </h3>
                    <ul>
                      <div class="modal-container">
                        <div class="clearfix">
                          <table style="border-collapse:collapse">
                            <a class="edit" @click.prevent="closeModal"><img src="images/edit@2x.png"/></a>
                            <tr>
                              <td class="tdLeft">メールアドレス</td>
                              <td class="tdRight">{{ userInfo.email }}</td>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
  export default {
    name: "UserInfoModal",
    data() {
      return {
        userInfo: {}
      }
    },
    methods: {
      closeModal() {
        this.$emit('closeUserModal');
      },
      editUser() {

      },
      getUserInfo() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getEnterprisesList').then((res) => {
          loading.close();
          this.userInfo = res.data.user[0];
        })
      }
    },
    mounted() {
      this.getUserInfo();
    }
  }
</script>

<style scoped>
  .customerdetailoffice {
    border-top: none;
    border-bottom: none;
  }
</style>
