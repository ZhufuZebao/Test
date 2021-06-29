<template>
  <!--container-->
  <div class="container clearfix customer customerlist commonAll">
    <header>
      <h1>
        <router-link to="/customer">
          <div class="commonLogo">
            <ul>
              <li class="bold">CUSTOMER</li>
              <li>施主</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/customer' }"><span>施主一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item>事業所情報</el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <section class="project-wrapper">
      <div class="common-view common-wrapper-container">
        <div class="customer-detail-modal-mask">
          <div class="modal-wrapper" @click.self="$emit('detailClose')">
            <div class="modal-container ">
              <div class="customerdetail clearfix">
                <div class="content">
                  <div class="customerdetailoffice">
                    <div class="accordion-target officeaccordion" v-if='accordion'>

                      <h3>
                        {{ office.name }}
                      </h3>
                      <ul>
                        <div class="modal-container">
                          <div class="clearfix">

                            <table class="modal-mod">
                              <a class="edit" @click.prevent="editCustomerOffice"><img src="images/edit@2x.png"/></a>
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
                                    <div class="item">
                                      <div class="leftDiv"><span>担当者</span></div>
                                      <div class="rightDiv"><span>{{index+1}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('name') }}</span></div>
                                      <div class="rightDiv"><span>{{person.name}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('position') }}</span></div>
                                      <div class="rightDiv"><span>{{person.position}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('dept') }}</span></div>
                                      <div class="rightDiv"><span>{{person.dept}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('role') }}</span></div>
                                      <div class="rightDiv"><span>{{person.role}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('email') }}</span></div>
                                      <div class="rightDiv"><span>{{person.email}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ peopleName('tel') }}</span></div>
                                      <div class="rightDiv"><span>{{person.tel}}</span></div>
                                    </div>
                                    <hr v-if="index < office.people.length -1"/>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td class="tdLeft">請求先</td>
                                <td class="tdRight">
                                  <div v-for="(billing,index) in office.billings"
                                       v-bind:key="'billing' +billing.id">
        
                                    <div class="item">
                                      <div class="leftDiv"><span>請求先</span></div>
                                      <div class="rightDiv"><span>{{index+1}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ billingsName('name') }}</span></div>
                                      <div class="rightDiv"><span>{{billing.name}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ billingsName('zip') }}</span></div>
                                      <div class="rightDiv"><span>〒{{ billing.zip }}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>住所</span></div>
                                      <div class="rightDiv"><span>{{ billing.pref }}{{ billing.town }}{{ billing.street }}{{ billing.house }}</span>
                                      </div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ billingsName('tel') }}</span></div>
                                      <div class="rightDiv"><span>{{billing.tel}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ billingsName('dept') }}</span></div>
                                      <div class="rightDiv"><span>{{billing.dept}}</span></div>
                                    </div>
                                    <div class="item">
                                      <div class="leftDiv"><span>{{ billingsName('people_name') }}</span></div>
                                      <div class="rightDiv"><span>{{billing.people_name}}</span></div>
                                    </div>
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
              <div class="pro-button">
                <a class="modoru" @click="backToCustomer">
                  戻る
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import CustomerCols from '../../mixins/CustomerColsNew';
  import messages from "../../mixins/Messages";

  export default {
    name: "CustomerOffice",
    mixins: [CustomerCols,messages], //定数
    components: {
      UserProfile
    },
    data() {
      return {
        office: {},
        accordion: true
      }
    },
    methods: {
      //事業所の詳細情報を取得する
      fetchOfficeDetail: function () {
        let errMsg = this.commonMessage.error.customerDetail;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getOfficeDetail", {
          params: {
            id: this.$route.params.id
          }
        }).then((res) => {
          this.office = res.data;
          this.office.tel = this.change_tel(this.office.tel,this.office.tel_in);
          loading.close();
        }).catch(action => {
          this.$alert(errMsg, {showClose: false});
          loading.close();
          this.$router.push({path: '/customer'});
        });
      },
      //トランジスター事業所はインタフェースを編集する
      editCustomerOffice: function () {
        this.$router.push({
          path: '/customer/edit',
          query: {id: this.office.customer_id, officeId: this.office.id, officeFlag:true}
        });
      },
      //対応するドナーインターフェースを返信します
      backToPrev() {
        this.$router.push({
          path: '/customer/detail/' + this.office.customer_id
        });
      },
      //ドナーリストインターフェースに戻る
      backToCustomer() {
        this.$router.push({
          path: '/customer'
        });
      },
      change_tel(tel,tel_in) {
          if(tel_in){
              return tel + " 内線" +tel_in;
          }else{
              return tel;
          }
      }
    },
    created() {
      this.fetchOfficeDetail();
    },
  }
</script>

<style scoped>
  .customerdetailoffice {
    border-top: none;
    border-bottom: none;
  }

  .modal-mod {
    border-collapse: collapse;
  }
</style>
