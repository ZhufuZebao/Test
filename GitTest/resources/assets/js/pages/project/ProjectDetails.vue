<template>
  <!--container-->
  <div class="commonAll container clearfix projectdetail">
    <header>
      <h1>
        <router-link :to="{ path: '/project' ,query:{flag: 'success'}}">
          <div class="commonLogo">
            <ul>
              <li class="bold">PROJECT</li>
              <li>案件</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap project-info-top">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/project' ,query:{flag: 'success'}}"><span>案件一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/project/show/'+project.id} "><span>案件情報：{{project.place_name}}</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>案件詳細：{{project.place_name}}</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--project-wrapper-->
    <section class="common-wrapper">
      <div class="common-wrapper-container">
        <div class="common-view project-detail">

          <img class="top-image" v-if="project.subject_image&&!project.subject_image.match('images')" v-bind:src="'file/projects/'+project.subject_image" data-action="zoom">
          <img class="top-image" v-else src="images/no-image.png" data-action="zoom">
          <el-tabs type="card" v-model="active">
            <el-tab-pane name="activeFirst" class="content" :disabled="!tabSwitch">
              <span slot="label">
                <p>案件基本情報</p>
              </span>
              <ProjectBasisInformation v-if="projectBasisFlag" :projectBasis="projectBasis"
                                       :updateOneFlag="updateOneFlag"
                                       @submitFormFlag="submitFormFlag"></ProjectBasisInformation>
              <table class="detail-table" v-if="!projectBasisFlag">
                <a  @click="projectBasisShow" v-if="editDetail">
                  <img src="images/edit@2x.png"/>
                </a>
                <tr>
                  <td class="tdLeft">案件名</td>
                  <td class="tdRight">{{project.place_name}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">案件No</td>
                  <td class="tdRight">{{project.project_no}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">郵便番号</td>
                  <td class="tdRight">{{project.zip}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">住所</td>
                  <td class="tdRight">{{project.address}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">電話番号</td>
                  <td class="tdRight">{{project.tel}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">FAX</td>
                  <td class="tdRight">{{project.fax}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">工事内容</td>
                  <td class="tdRight" v-if="project.construction">
                    {{project.construction}}
                  </td>
                  <td class="tdRight" v-else>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">施主情報</td>
                  <td class="tdRight">
                    <div class="infolist" v-for="(customer,index) in projectBasis.customer" v-if="customer" :key="'customer-'+index">
                      <div class="item">
                        <div class="leftDiv"><span>施主名</span></div>
                        <div class="rightDiv"><span>{{customer.name}}</span></div>
                      </div>
                      <div class="item">
                        <div class="leftDiv"><span>電話番号</span></div>
                        <div class="rightDiv"><span v-if="customer.tel">{{customer.tel}}</span></div>
                      </div>
                      <div v-if="customer.people">
                        <div class="items" v-for="people in customer.people" v-if="people" :key="'people+'+people.id">
                          <div class="item">
                            <div class="leftDiv"><span>担当者名</span></div>
                            <div class="rightDiv"><span>{{people.name}}</span></div>
                          </div>
                          <div class="item">
                            <div class="leftDiv"><span>メールアドレス</span></div>
                            <div class="rightDiv"><span>{{people.email}}</span></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">現場担当者</td>
                  <td class="tdRight">
                    <div class="infolist" v-for="(proj,index) in project.project_locale_chief">
                      <div class="item">
                        <div class="leftDiv"><span>現場担当</span></div>
                        <div class="rightDiv"><span>{{index+1}}</span></div>
                      </div>
                      <div class="item">
                        <div class="leftDiv"><span>担当者氏名</span></div>
                        <div class="rightDiv"><span>{{proj.name}}</span></div>
                      </div>
                      <div class="item item-eml">
                        <div class="leftDiv"><span>メールアドレス</span></div>
                        <div class="rightDiv"><span>{{proj.mail}}</span></div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">進捗状況</td>
                  <td class="tdRight" v-if="project.progress_status">
                    {{progressStatusName(project.progress_status)}}
                  </td>
                  <td class="tdRight" v-else="!project.progress_status">
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">進捗特記事項</td>
                  <td class="tdRight">{{project.progress_special_content}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">建物用途</td>
                  <td class="tdRight">{{project.building_type}}</td>
                </tr>
                <tr>
                  <td class="tdLeft">工事期間</td>
                  <td class="tdRight">
                    <div class="item">
                      <div class="leftDiv">着工日</div>
                      <div class="rightDiv">&nbsp;{{project.st_date}}</div>
                    </div>
                    <div class="item">
                      <div class="leftDiv">竣工日</div>
                      <div class="rightDiv">&nbsp;{{project.ed_date}}</div>
                    </div>
                    <div class="item">
                      <div class="leftDiv">オープン予定</div>
                      <div class="rightDiv">&nbsp;{{project.open_date}}</div>
                    </div>
                  </td>
                </tr>
              </table>
            </el-tab-pane>
            <el-tab-pane name="activeSecond" class="content" :disabled="!tabSwitch">
              <span slot="label">
                <p>管理会社・不動産屋 情報</p>
              </span>
              <ProjectCompanyInformation v-if="projectCompanyFlag" :projectCompany="projectCompany"
                                         :updateOneFlag="updateOneFlag"
                                         @submitFormFlag="submitFormFlag"></ProjectCompanyInformation>
              <table class="detail-table" v-if="!projectCompanyFlag">
                <a  @click="projectCompanyShow" v-if="editDetail">
                  <img src="images/edit@2x.png"/>
                </a>
                <tr>
                  <td class="tdLeft">管理会社</td>
                  <td class="tdRight">
                    <div class="item">
                      <div class="leftDiv width"><span>会社名</span></div>
                      <div class="rightDiv width"><span>{{project.mng_company_name}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>住所</span></div>
                      <div class="rightDiv width"><span>{{project.mng_company_address}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>電話</span></div>
                      <div class="rightDiv width"><span>{{project.mng_company_tel}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>担当者</span></div>
                      <div class="rightDiv width"><span>{{project.mng_company_chief}}<span
                              v-if="project.mng_company_chief">さん</span></span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>担当役職</span></div>
                      <div class="rightDiv width"><span>{{project.mng_company_chief_position}}</span></div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">不動産屋</td>
                  <td class="tdRight">
                    <div class="item">
                      <div class="leftDiv width"><span>不動産名</span></div>
                      <div class="rightDiv width"><span>{{project.realtor_name}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>住所</span></div>
                      <div class="rightDiv width"><span>{{project.realtor_address}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>電話</span></div>
                      <div class="rightDiv width"><span>{{project.realtor_tel}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>担当者</span></div>
                      <div class="rightDiv width"><span>{{project.realtor_chief}}</span><span
                              v-if="project.realtor_chief">さん</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>担当役職</span></div>
                      <div class="rightDiv width"><span>{{project.realtor_chief_position}}</span></div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="tdLeft">物件規模</td>
                  <td class="tdRight">
                    <div class="item">
                      <div class="leftDiv width"><span>敷地面積</span></div>
                      <div class="rightDiv width"><span>{{project.site_area}}<span v-if="project.site_area">㎡</span></span>
                      </div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>建物面積</span></div>
                      <div class="rightDiv width"><span>{{project.floor_area}}<span v-if="project.floor_area">㎡</span></span>
                      </div>
                    </div>
                    <div class="item">
                      <div class="leftDiv width"><span>階数</span></div>
                      <div class="rightDiv width"><span>{{project.floor_numbers}}<span
                              v-if="project.floor_numbers">階</span></span></div>
                    </div>
                  </td>
                </tr>
                <!--工事会社-->
                <tr>
                  <td class="tdLeft">工事会社</td>
                  <td class="tdRight">{{project.construction_company}}</td>
                </tr>
                <!--工事に伴う特記事項-->
                <tr>
                  <td class="tdLeft">工事に伴う特記事項</td>
                  <td class="tdRight">{{project.construction_special_content}}</td>
                </tr>
              </table>
            </el-tab-pane>
            <el-tab-pane name="activeThird" class="content" :disabled="!tabSwitch">
              <span slot="label">
                <p>安全管理情報</p>
              </span>
              <ProjectSafetyInformation v-if="projectSafetyFlag" :projectSafety="projectSafety"
                                        :updateOneFlag="updateOneFlag"
                                        @submitFormFlag="submitFormFlag"></ProjectSafetyInformation>
              <table class="detail-table" v-if="!projectSafetyFlag">
                  <a  @click="projectSafetyShow" v-if="editDetail">
                    <img src="images/edit@2x.png"/>
                  </a>
              <tr>
                <td class="tdLeft">緊急連絡先電話</td>
                <td class="tdRight">{{project.security_management_tel}}</td>
              </tr>
              <tr>
                <td class="tdLeft">現場責任者</td>
                <td class="tdRight">{{project.security_management_chief}}</td>
              </tr>
              <tr>
                <td class="tdLeft">現場副責任者</td>
                <td class="tdRight">{{project.security_management_deputy}}</td>
              </tr>
              <tr>
                <td class="tdLeft">工種別責任者</td>
                <td class="tdRight">
                  <div class="infos" v-for="(proj,index) in project.project_trades_chief">
                    <div class="item">
                      <div class="leftDiv"><span>責任者</span></div>
                      <div class="rightDiv"><span>{{index+1}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>工種別</span></div>
                      <div class="rightDiv" v-if="proj.trades_type"><span>{{tradesTypeName(proj.trades_type)}}</span></div>
                    </div>
                    <div class="item" v-if="proj.trades_type === '5'">
                      <div class="leftDiv"><span>具体的な工種名</span></div>
                      <div class="rightDiv"><span>{{proj.trades_type_detail}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>工種別会社名</span></div>
                      <div class="rightDiv"><span>{{proj.company}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>工種別責任者</span></div>
                      <div class="rightDiv"><span>{{proj.name}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>連絡先電話番号</span></div>
                      <div class="rightDiv"><span>{{proj.tel}}</span></div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="tdLeft">管轄消防署名</td>
                <td class="tdRight">{{project.fire_station_name}}</td>
              </tr>
              <tr>
                <td class="tdLeft">管轄消防署担当者</td>
                <td class="tdRight">{{project.fire_station_chief}}</td>
              </tr>
              <tr>
                <td class="tdLeft">管轄消防署電話</td>
                <td class="tdRight">{{project.fire_station_tel}}</td>
              </tr>
              <tr>
                <td class="tdLeft">管轄警察署名</td>
                <td class="tdRight">{{project.police_station_name}}</td>
              </tr>
              <tr>
                <td class="tdLeft">管轄警察署担当者</td>
                <td class="tdRight">{{project.police_station_chief}}</td>
              </tr>
              <tr>
                <td class="tdLeft">管轄警察署電話</td>
                <td class="tdRight">{{project.police_station_tel}}</td>
              </tr>
              <tr>
                <td class="tdLeft">最寄病院</td>
                <td class="tdRight">
                  <div class="infolist" v-for="(proj,index) in project.project_hospital">
                    <div class="item">
                      <div class="leftDiv"><span>最寄病院</span></div>
                      <div class="rightDiv"><span>{{index+1}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>最寄病院名</span></div>
                      <div class="rightDiv"><span>{{proj.name}}</span></div>
                    </div>
                    <div class="item">
                      <div class="leftDiv"><span>最寄病院電話番号</span></div>
                      <div class="rightDiv"><span>{{proj.tel}}</span></div>
                    </div>
                  </div>
                </td>
              </tr>
            </table>
            </el-tab-pane>
            <el-tab-pane name="activeFourth" class="content" :disabled="!tabSwitch" >
              <span slot="label"  v-if="editDetail" @click="projectDetail(project)">
                <el-button class="project-details-bulk-button">一括編集</el-button>
              </span>
            </el-tab-pane>
          </el-tabs>
          <div class="pro-button">
            <a class="modoru" @click="returnProjectList" v-if="tabSwitch">
              戻る
            </a>
            <!--<a class="henkou" @click="projectDetail(project)" v-if="editDetail">-->
              <!--変更-->
            <!--</a>-->
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import ProjectLists from "../../mixins/ProjectLists";
    import ProjectBasisInformation from '../../components/project/ProjectBasisInformation.vue'
    import ProjectCompanyInformation from '../../components/project/ProjectCompanyInformation.vue'
    import ProjectSafetyInformation from '../../components/project/ProjectSafetyInformation.vue'
    import Calendar from '../../mixins/Calendar'
    import Messages from "../../mixins/Messages";

    export default {
        name: "ProjectDetails",
        mixins: [ProjectLists,Calendar,Messages],
        components: {
            UserProfile,
            ProjectBasisInformation,
            ProjectCompanyInformation,
            ProjectSafetyInformation,
        },
        data: function () {
            return {
                active: 'activeFirst',
                editDetail:false,
                submitFlag: false,
                updateOneFlag: true,
                projectBasisDetail: true,
                projectBasisFlag: false,
                projectCompanyDetail: true,
                projectCompanyFlag: false,
                projectSafetyDetail: true,
                projectSafetyFlag: false,
                projectBasis: {
                    localeChief: [],
                    customer:[],
                },
                projectCompany: {},
                projectSafety: {
                    tradesChief: [],
                    hospital: [],
                },
                customerData: {},
                project: {},
                projectCustomerName: '',
                projectCustomerOfficeName: '',
                name: '',
                projectUser: {},
                tabSwitch:true,
            };
        },
        methods: {
            //子ページ「保存」をクリックの場合、flagはtrue
            submitFormFlag(flag) {
                this.submitFlag = flag;
            },
            //flagより、不同の案件詳細を表示
            showDetail() {
                if (this.submitFlag) {
                    this.fetchProject();
                }
                this.projectBasisFlag = false;
                this.projectBasisDetail = true;
                this.projectCompanyFlag = false;
                this.projectCompanyDetail = true;
                this.projectSafetyFlag = false;
                this.projectSafetyDetail = true;
                this.submitFlag = false;
                this.tabSwitch = true;
            },
            //案件基本情報を表示
            projectBasisShow() {
                this.projectBasisFlag = true;
                this.projectBasisDetail = false;
                this.projectCompanyFlag = false;
                this.projectCompanyDetail = true;
                this.projectSafetyFlag = false;
                this.projectSafetyDetail = true;
                this.tabSwitch = false;
            },
            //管理会社・不動産屋 情報を表示
            projectCompanyShow() {
                this.projectCompanyFlag = true;
                this.projectCompanyDetail = false;
                this.projectBasisFlag = false;
                this.projectBasisDetail = true;
                this.projectSafetyFlag = false;
                this.projectSafetyDetail = true;
                this.tabSwitch = false;
            },
            //安全管理情報を表示
            projectSafetyShow() {
                this.projectSafetyFlag = true;
                this.projectSafetyDetail = false;
                this.projectCompanyFlag = false;
                this.projectCompanyDetail = true;
                this.projectBasisFlag = false;
                this.projectBasisDetail = true;
                this.tabSwitch = false;
            },
            //変更パタンをクリック
            projectDetail(project) {
                if(!this.tabSwitch){
                    return ;
                }
                this.$router.push({path: '/project/update/' + project.id});
                // chrome
                document.body.scrollTop = 0;
                // firefox
                document.documentElement.scrollTop = 0;
                // safari
                window.pageYOffset = 0
            },
            //戻るパタンをクリック
            returnProjectList() {
                this.$router.push({path: '/project/show/' + this.project.id});
            },
            //案件についての情報を取得
            fetchProject: function () {
                let errMessage = this.commonMessage.error.projectDetail;
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.get("/api/getProjectObject", {
                    params: {
                        id: this.$route.params.id
                    }
                }).then(res => {
                    //案件基本情報
                    if (res.data.enterpriseId ===res.data.model.enterprise_id){
                        this.editDetail=true;
                    }
                    res.data=res.data.model;
                    this.$set(this.projectBasis, 'id', res.data.id);
                    this.$set(this.projectBasis, 'place_name', res.data.place_name);
                    //#2790 remove construction_name in project
                    // this.$set(this.projectBasis, 'construction_name', res.data.construction_name);
                    this.$set(this.projectBasis, 'project_no', res.data.project_no);
                    this.$set(this.projectBasis, 'zip', res.data.zip);
                    this.$set(this.projectBasis, 'pref', res.data.pref);
                    this.$set(this.projectBasis, 'town', res.data.town);
                    this.$set(this.projectBasis, 'street', res.data.street);
                    this.$set(this.projectBasis, 'house', res.data.house);
                    this.$set(this.projectBasis, 'tel', res.data.tel);
                    this.$set(this.projectBasis, 'tel_in', res.data.tel_in);
                    this.$set(this.projectBasis, 'fax', res.data.fax);
                    this.$set(this.projectBasis, 'construction', res.data.construction);
                    this.$set(this.projectBasis, 'customer_id', res.data.customer_id);
                    if(!res.data.subject_image){
                        res.data.subject_image="images/no-image.png";
                        this.$set(this.projectBasis, 'subject_image', res.data.subject_image);
                    }else{
                        this.$set(this.projectBasis, 'subject_image', res.data.subject_image);
                    }
                    this.$set(this.projectBasis, 'progress_status', res.data.progress_status);
                    this.$set(this.projectBasis, 'progress_special_content', res.data.progress_special_content);
                    this.$set(this.projectBasis, 'building_type', res.data.building_type);
                    this.$set(this.projectBasis, 'st_date', res.data.st_date);
                    this.$set(this.projectBasis, 'ed_date', res.data.ed_date);
                    this.$set(this.projectBasis, 'open_date', res.data.open_date);
                    this.$set(this.projectBasis, 'telOut', res.data.tel);
                    this.$set(this.projectBasis, 'telIn',  res.data.tel_in);
                    //管理会社・不動産屋 情報
                    this.$set(this.projectCompany, 'mng_company_name', res.data.mng_company_name);
                    this.$set(this.projectCompany, 'mng_company_address', res.data.mng_company_address);
                    this.$set(this.projectCompany, 'mng_company_tel', res.data.mng_company_tel);
                    this.$set(this.projectCompany, 'mng_company_chief', res.data.mng_company_chief);
                    this.$set(this.projectCompany, 'mng_company_chief_position', res.data.mng_company_chief_position);
                    this.$set(this.projectCompany, 'realtor_name', res.data.realtor_name);
                    this.$set(this.projectCompany, 'realtor_address', res.data.realtor_address);
                    this.$set(this.projectCompany, 'realtor_tel', res.data.realtor_tel);
                    this.$set(this.projectCompany, 'realtor_chief', res.data.realtor_chief);
                    this.$set(this.projectCompany, 'realtor_chief_position', res.data.realtor_chief_position);
                    if(res.data.site_area){
                        this.$set(this.projectCompany, 'site_area', res.data.site_area.replace(/\b(0+)/gi,""));
                    }else{
                        this.$set(this.projectCompany, 'site_area', res.data.site_area);
                    }
                    if(res.data.floor_area){
                        this.$set(this.projectCompany, 'floor_area', res.data.floor_area.replace(/\b(0+)/gi,""));
                    }else{
                        this.$set(this.projectCompany, 'floor_area', res.data.floor_area);
                    }
                    if(res.data.floor_numbers){
                        this.$set(this.projectCompany, 'floor_numbers', res.data.floor_numbers.replace(/\b(0+)/gi,""));
                    }else{
                        this.$set(this.projectCompany, 'floor_numbers', res.data.floor_numbers);
                    }
                    this.$set(this.projectCompany, 'construction_company', res.data.construction_company);
                    this.$set(this.projectCompany, 'construction_special_content', res.data.construction_special_content);
                    //安全管理情報
                    this.$set(this.projectSafety, 'security_management_tel', res.data.security_management_tel);
                    this.$set(this.projectSafety, 'security_management_chief', res.data.security_management_chief);
                    this.$set(this.projectSafety, 'security_management_deputy', res.data.security_management_deputy);
                    this.$set(this.projectSafety, 'fire_station_name', res.data.fire_station_name);
                    this.$set(this.projectSafety, 'fire_station_chief', res.data.fire_station_chief);
                    this.$set(this.projectSafety, 'fire_station_tel', res.data.fire_station_tel);
                    this.$set(this.projectSafety, 'police_station_name', res.data.police_station_name);
                    this.$set(this.projectSafety, 'police_station_chief', res.data.police_station_chief);
                    this.$set(this.projectSafety, 'police_station_tel', res.data.police_station_tel);
                    //登録情報
                    this.$set(this.projectUser, 'project_created_by_lastname', res.data.user.last_name);
                    this.$set(this.projectUser, 'project_created_by_firstname', res.data.user.first_name);
                    this.$set(this.projectUser, 'project_created_at_date', Calendar.dateFormat(res.data.created_at));
                    let projectCreatedAtTime = res.data.created_at.substring(res.data.created_at.length-8, res.data.created_at.length-3);
                    this.$set(this.projectUser, 'project_created_at_time', projectCreatedAtTime);
                    switch (new Date(Calendar.dateFormat(res.data.created_at)).getDay()) {
                        case 0:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[6]);
                            break;
                        case 1:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[0]);
                            break;
                        case 2:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[1]);
                            break;
                        case 3:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[2]);
                            break;
                        case 4:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[3]);
                            break;
                        case 5:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[4]);
                            break;
                        case 6:
                            this.$set(this.projectUser, 'project_created_at_week', Calendar.weekNames[5]);
                            break;
                    }
                    //更新情報
                    if (res.data.updated_by && res.data.created_at !== res.data.updated_at) {
                        this.$set(this.projectUser, 'project_updated_by_lastname', res.data.user_update_by.last_name);
                        this.$set(this.projectUser, 'project_updated_by_firstname', res.data.user_update_by.first_name);
                        this.$set(this.projectUser, 'project_updated_at_date', Calendar.dateFormat(res.data.updated_at));
                        let projectUpdatedAtTime = res.data.updated_at.substring(res.data.updated_at.length-8, res.data.updated_at.length-3);
                        this.$set(this.projectUser, 'project_updated_at_time', projectUpdatedAtTime);
                        switch (new Date(Calendar.dateFormat(res.data.updated_at)).getDay()) {
                            case 0:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[6]);
                                break;
                            case 1:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[0]);
                                break;
                            case 2:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[1]);
                                break;
                            case 3:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[2]);
                                break;
                            case 4:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[3]);
                                break;
                            case 5:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[4]);
                                break;
                            case 6:
                                this.$set(this.projectUser, 'project_updated_at_week', Calendar.weekNames[5]);
                                break;
                        }
                    }
                    axios.get("/api/getProjectDetail", {
                        params: {
                            id: this.$route.params.id
                        }
                    }).then(res => {
                        this.project = res.data[0];
                        if (this.project.site_area){
                            this.project.site_area=this.project.site_area.replace(/\b(0+)/gi,"");
                        }
                        if (this.project.floor_area){
                            this.project.floor_area=this.project.floor_area.replace(/\b(0+)/gi,"");
                        }
                        if (this.project.floor_numbers){
                            this.project.floor_numbers=this.project.floor_numbers.replace(/\b(0+)/gi,"");
                        }
                        //電話番号
                        if(this.project.tel_in){
                            this.$set(this.project, 'tel', this.project.tel+" 内線"+this.project.tel_in);
                        }
                        if(this.project){
                            //現場担当者
                            this.projectBasis.localeChief = this.project.project_locale_chief;
                            //工種別責任者
                            this.projectSafety.tradesChief = this.project.project_trades_chief;
                            //最寄病院
                            this.projectSafety.hospital = this.project.project_hospital;
                            this.projectBasis.customer = this.project.customer_office;
                        }
                        //着工日、竣工日、オープン予定をフォマード
                        if(this.project.st_date&&this.project.ed_date){
                            this.project.st_date = new Date(this.project.st_date);
                            this.project.ed_date = new Date(this.project.ed_date);
                            this.project.st_date = this.project.st_date.getFullYear() + "/" +
                                (this.project.st_date.getMonth() + 1) + "/" +
                                this.project.st_date.getDate();
                            this.project.ed_date = this.project.ed_date.getFullYear() + "/" +
                                (this.project.ed_date.getMonth() + 1) + "/" +
                                this.project.ed_date.getDate();
                        }
                        if(this.project.open_date){
                            this.project.open_date = new Date(this.project.open_date);
                            this.project.open_date = this.project.open_date.getFullYear() + "/" +
                                (this.project.open_date.getMonth() + 1) + "/" +
                                this.project.open_date.getDate();
                        }
                    }).catch(error => {
                        this.$alert(errMessage, {showClose: false});
                        loading.close();
                    });
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
            }
        },
        created() {
            this.fetchProject();
        },
        beforeRouteLeave (to, from, next) {
            let msg = this.commonMessage.confirm.warning.jump;
            if (to.query.flag !== 'success' &&
                (to.path === '/chat'
                    || to.path === '/schedule'
                    || to.path === '/company'
                    || to.path === '/contact'
                    || to.path === '/document'
                    || to.path === '/invite'
                    || to.path === '/friend'
                    || to.path === '/project'
                    || to.path === '/customer') &&
                (this.projectBasisFlag
                    || this.projectCompanyFlag
                    || this.projectSafetyFlag)){
                let res = confirm(msg);
                if (res) {
                    next();
                }else{
                    return false;
                }
            }else{
                next();
            }
        },
    }
</script>
