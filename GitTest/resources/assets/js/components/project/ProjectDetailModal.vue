<template>
  <transition name="fade">
    <div class="modal wd1 modal-show commonAll project-customer">
      <div class="modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-deteil-wrap report-deteil-wrap clearfix">
            <div class="common-view">
              <div class="customer-detail-modal-mask">
                <div class="modal-wrapper" @click.self="$emit('detailClose')">
                  <div class="modal-container ">
                    <div class="customerdetail clearfix">
                      <div class="content">
                        <div class="customerdetailoffice">
                          <div class="accordion-target officeaccordion">
                            <ul>
                              <div class="modal-container">
                                <div class="clearfix">
                                  <table class="detail-table">
                                    <tr>
                                      <td class="tdLeft">{{ officesName('name') }}</td>
                                      <td class="tdRight">{{ office.name }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('zip') }}</td>
                                      <td class="tdRight">〒{{ office.zip }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('pref') }}</td>
                                      <td class="tdRight">{{ office.pref }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('town') }}</td>
                                      <td class="tdRight">{{ office.town }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('street') }}</td>
                                      <td class="tdRight">{{ office.street }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('house') }}</td>
                                      <td class="tdRight">{{ office.house }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('tel') }}</td>
                                      <td class="tdRight">{{ office.tel }}</td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">{{ officesName('fax') }}</td>
                                      <td class="tdRight">{{ office.fax }}</td>
                                    </tr>

                                    <tr>
                                      <td class="tdLeft">{{ peopleName('name') }}</td>
                                      <td class="tdRight">
                                        <div v-for="(person,index) in office.people"
                                             v-bind:key="'person' +person.id">
                                          {{ person.name }}<br/>
                                          {{ person.position }}<br/>
                                          {{ person.dept }}<br/>
                                          {{ person.role }}<br/>
                                          {{ person.email }}<br/>
                                          <hr v-if="index < office.people.length -1"/>
                                        </div>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td class="tdLeft">請求先</td>
                                      <td class="tdRight">
                                        <div v-for="(billing,index) in office.billings"
                                             v-bind:key="'billing' +billing.id">
                                          {{ billing.name }}<br/>
                                          〒{{ billing.zip }}<br/>
                                          {{ billing.pref }}{{ billing.town }}{{ billing.street }}{{ billing.house
                                          }}<br/>
                                          {{ billing.tel}}<br/>
                                          {{billing.dept}}<br/>
                                          {{billing.people_name}}<br/>
                                          <hr v-if="index < office.billings.length -1"/>
                                        </div>
                                      </td>
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
            </div>
            <div class="clearfix"></div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>
<script>
  import CustomerCols from '../../mixins/CustomerColsNew'

  export default {
    name: "ProjectDetailModal",
    mixins: [CustomerCols],
    props: {
      project: Object,
    },
    data: function () {
      return {
        office: {},
        isMounted: false
      }
    },
    methods: {
      fetchOfficeDetail: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getOfficeDetail", {
          params: {
            id: this.project.customer_office_id
          }
        }).then((res) => {
          this.office = res.data;
        }).catch(error => {
        });
        loading.close();
      },
      closeDelModal() {
        this.delCheck = false;
      }
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      },
    },
    created() {
      this.fetchOfficeDetail()
    },
    mounted() {
      this.isMounted = true;
    }
  }
</script>

<style scoped>
  .project-customer {
    display: block;
  }

  .tanntou div {
    border: 1px solid;
  }

  .jyoho div {
    border: 1px solid;
  }

  .customerdetailoffice {
    border-top: none;
    border-bottom: none;
  }

  .common-view {
    width: 808px;
    height: 460px;
    overflow-x: hidden;
  }
</style>
