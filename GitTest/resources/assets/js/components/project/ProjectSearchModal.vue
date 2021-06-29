<template>
  <transition name="fade">
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
        <div class="common-deteil-wrap report-deteil-wrap clearfix">
          <div style="margin:30px 0px 30px 250px">
            <span>検索種別:</span>
            <el-radio-group v-model="searchType">
              <el-radio label="AND">AND</el-radio>
              <el-radio label="OR">OR</el-radio>
            </el-radio-group>
          </div>
          <div style="margin-left: 150px">
            <div class="button-lower remark">
              <a href="javascript:void(0)" @click="searchSelect(1)">案件基本情報</a>
            </div>
            <div class="button-lower remark">
              <a href="javascript:void(0)" @click="searchSelect(2)">管理会社/契約時不動産仲介業者</a>
            </div>
            <div class="button-lower remark">
              <a href="javascript:void(0)" @click="searchSelect(3)">安全管理情報</a>
            </div>
          </div>

          <!--******案件基本情報******-->
          <el-form :model="searchArray" ref="form" label-width="250px" v-if="search===1">
            <el-form-item label="物件・現場名">
              <el-input placeholder="物件・現場名" v-model="searchArray.project.place_name">
              </el-input>
            </el-form-item>
            <el-form-item label="工事件名">
              <el-input placeholder="工事件名" v-model="searchArray.project.construction_name"></el-input>
            </el-form-item>
            <el-form-item label="郵便番号">
              <el-input placeholder="〒郵便番号" v-model="searchArray.project.zip"></el-input>
            </el-form-item>
            <!--<el-input>※(半角数字)ハイン(-)なしで入力してください。</el-input>-->
            <el-form-item label="案件住所">
              <el-input placeholder="案件住所" v-model="searchArray.project.address">
              </el-input>
            </el-form-item>
            <el-form-item label="電話番号" prop="">
              <el-input placeholder="電話番号" v-model="f_tel" style="width: 150px"></el-input>
              (内線:
              <el-input placeholder="内線" v-model="f_tel" style="width: 150px"></el-input>
              )
            </el-form-item>
            <!--<el-input>※(半角数字)ハイン(-)なしで入力してください。</el-input>-->
            <el-form-item label="FAX" prop="">
              <el-input placeholder="FAX" v-model="searchArray.project.fax"></el-input>
            </el-form-item>
            <!--<el-input>※(半角数字)ハイン(-)なしで入力してください。</el-input>-->
            <el-form-item label="新築・改築">
              <el-radio-group style="margin-top: 20px" v-model="searchArray.project.construction_type">
                <el-radio v-for="names in constructionType"
                          :label="names.id" :key="names.id">
                  {{names.name}}
                </el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="施主情報" prop="">
              <el-input placeholder="施主情報" v-model="searchArray.customer.name">
              </el-input>
            </el-form-item>
            <el-form-item label="担当者氏名" prop="">
              <el-input placeholder="担当者氏名" v-model="searchArray.chief.name">
              </el-input>
            </el-form-item>
            <el-form-item label="現場担当者-担当者役職" prop="">
              <el-input placeholder="担当者役職" v-model="searchArray.chief.position">
              </el-input>
            </el-form-item>
            <el-form-item label="現場担当者-携帯電話" prop="">
              <el-input placeholder="携帯電話" v-model="searchArray.chief.tel">
              </el-input>
            </el-form-item>
            <el-form-item label="現場担当者-メールアドレス" prop="">
              <el-input v-model="searchArray.chief.mail" placeholder="現場担当者-メールアドレス">
              </el-input>
            </el-form-item>
            <el-form-item label="進捗状況">
              <el-select v-model="searchArray.project.progress_status">
                <el-option v-for="names in progressStatus" :value="names.name" :key="names.id"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="進捗特記事項" prop="">
              <el-input placeholder="進捗特記事項" v-model="searchArray.project.progress_special_content">
              </el-input>
            </el-form-item>
            <!--建物用途-->
            <el-form-item label="建物用途" prop="">
              <el-input placeholder="建物用途" v-model="searchArray.project.building_type">
              </el-input>
            </el-form-item>
            <el-form-item label="着工" prop="">
              <el-date-picker type="date" placeholder="着工" v-model="searchArray.project.st_date">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="竣工" prop="">
              <el-date-picker type="date" placeholder="竣工" v-model="searchArray.project.ed_date">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="オープン予定日" prop="">
              <el-date-picker type="date" placeholder="オープン予定日" v-model="searchArray.project.open_date">
              </el-date-picker>
            </el-form-item>
          </el-form>
          <!--******管理会社・不動産屋　情報******-->
          <!--管理会社-->
          <el-form :model="searchArray" ref="form" label-width="250px" v-else-if="search===2">
            <el-form-item label="管理会社-会社名" prop="">
              <el-input placeholder="会社名" v-model="searchArray.builder.mng_company_name">
              </el-input>
            </el-form-item>
            <el-form-item label="管理会社-住所" prop="">
              <el-input type="text" placeholder="住所"
                        v-model="searchArray.builder.mng_company_adress">
              </el-input>
            </el-form-item>
            <el-form-item label="管理会社-電話" prop="">
              <el-input placeholder="電話" v-model="searchArray.builder.mng_company_tel">
              </el-input>
            </el-form-item>
            <el-form-item label="管理会社-担当者" prop="">
              <el-input type="text" placeholder="担当者"
                        v-model="searchArray.builder.mng_company_chief">
              </el-input>
            </el-form-item>
            <el-form-item label="管理会社-担当役職" prop="">
              <el-input placeholder="担当役職"
                        v-model="searchArray.builder.mng_company_chief_position"></el-input>
              <!--契約時不動産仲介業者-->
            </el-form-item>
            <el-form-item label="契約時不動産仲介業者-不動産名" prop="">
              <el-input placeholder="不動産名"
                        v-model="searchArray.builder.realtor_name">
              </el-input>
            </el-form-item>
            <el-form-item label="契約時不動産仲介業者-住所" prop="">
              <el-input placeholder="住所"
                        v-model="searchArray.builder.realtor_adress">
              </el-input>
            </el-form-item>
            <el-form-item label="契約時不動産仲介業者-電話" prop="">
              <el-input placeholder="電話" v-model="searchArray.builder.realtor_tel">
              </el-input>
            </el-form-item>
            <el-form-item label="契約時不動産仲介業者-担当者" prop="">
              <el-input placeholder="担当者"
                        v-model="searchArray.builder.realtor_chief">
              </el-input>
            </el-form-item>
            <el-form-item label="契約時不動産仲介業者-担当役職" prop="">
              <el-input placeholder="担当役職"
                        v-model="searchArray.builder.realtor_chief_position">
              </el-input>
              <!--物件規模-->
            </el-form-item>
            <el-form-item label="物件規模-敷地面積" prop="">
              <el-input placeholder="敷地面積" v-model="searchArray.builder.site_area">
              </el-input>
            </el-form-item>
            <el-form-item label="物件規模-建物面積" prop="">
              <el-input placeholder="建物面積" v-model="searchArray.builder.floor_area">
              </el-input>
            </el-form-item>
            <el-form-item label="物件規模-階数" prop="">
              <el-input placeholder="階数" v-model="searchArray.builder.floor_numbers">
              </el-input>
            </el-form-item>
            <el-form-item label="工事会社" prop="">
              <el-input placeholder="工事会社"
                        v-model="searchArray.builder.construction_company">
              </el-input>
            </el-form-item>
            <el-form-item label="工事に伴う特記事項" prop="">
              <el-input placeholder="工事に伴う特記事項"
                        v-model="searchArray.builder.construction_special_content">
              </el-input>
            </el-form-item>
          </el-form>
          <el-form :model="searchArray" ref="form" label-width="250px" v-else-if="search===3">
            <!--******安全管理情報******-->
            <el-form-item label="緊急連絡先電話" prop="">
              <el-input placeholder="緊急連絡先電話"
                        v-model="searchArray.safety.security_management_tel"></el-input>
            </el-form-item>
            <el-form-item label="現場責任者" prop="">
              <el-input placeholder="現場責任者"
                        v-model="searchArray.safety.security_management_chief"></el-input>
            </el-form-item>
            <el-form-item label="現場副責任者" prop="">
              <el-input placeholder="現場副責任者"
                        v-model="searchArray.safety.security_management_deputy"></el-input>
            </el-form-item>
            <el-form-item label="工種別責任者-工種別" prop="">
              <el-select v-model="searchArray.trades_chief.trades_type">
                <el-option v-for="names in tradesType" :value="names.name" :key="names.id"
                           placeholder="工種別"></el-option>
              </el-select>
              <el-input type="text" v-model="searchArray.trades_chief.trades_type_detail" style="width: 100px"
                        placeholder="詳細な">
              </el-input>
            </el-form-item>
            <el-form-item label="工種別責任者-工種別会社名" prop="">
              <el-input placeholder="工種別会社名" v-model="searchArray.trades_chief.company">
              </el-input>
            </el-form-item>
            <el-form-item label="工種別責任者-工種別責任者" prop="">
              <el-input placeholder="工種別責任者" v-model="searchArray.trades_chief.name">
              </el-input>
            </el-form-item>
            <el-form-item label="工種別責任者-連絡先電話番号" prop="">
              <el-input placeholder="連絡先電話番号" v-model="searchArray.trades_chief.tel">
              </el-input>

            </el-form-item>
            <el-form-item label="管轄消防署名" prop="">
              <el-input placeholder="管轄消防署名" v-model="searchArray.safety.fire_station_name">
              </el-input>
            </el-form-item>
            <el-form-item label="管轄消防署担当者" prop="">
              <el-input placeholder="管轄消防署担当者" v-model="searchArray.safety.fire_station_chief">
              </el-input>
            </el-form-item>
            <el-form-item label="管轄消防署電話" prop="">
              <el-input placeholder="管轄消防署電話" v-model="searchArray.safety.fire_station_tel">
              </el-input>
            </el-form-item>
            <el-form-item label="管轄警察署名" prop="">
              <el-input placeholder="管轄警察署名" v-model="searchArray.safety.police_station_name">
              </el-input>
            </el-form-item>
            <el-form-item label="管轄警察署担当者" prop="">
              <el-input placeholder="管轄警察署担当者"
                        v-model="searchArray.safety.police_station_chief">
              </el-input>
            </el-form-item>
            <el-form-item label="管轄警察署電話" prop="">
              <el-input placeholder="管轄警察署電話" v-model="searchArray.safety.police_station_tel">
              </el-input>
            </el-form-item>
            <el-form-item label="最寄病院-最寄病院名" prop="">
              <el-input placeholder="最寄病院名" v-model="searchArray.hospital.name">
              </el-input>
            </el-form-item>
            <el-form-item label="最寄病院-最寄病院電話番号" prop="">
              <el-input placeholder="最寄病院電話番号" v-model="searchArray.hospital.tel">
              </el-input>
            </el-form-item>
          </el-form>

          <div class="button-wrap clearfix" style="margin-left: 250px">
            <div class="button-lower remark">
              <a href="javascript:void(0)" @click="searchArrayProjects">OK</a>
            </div>
            <div class="button-lower">
              <a @click="$emit('closeModal')">キャンセル</a>
            </div>
          </div>

        </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>
<script>
  import ProjectLists from "../../mixins/ProjectLists";

  export default {
    mixins: [
      ProjectLists,
    ],
    name: "ProjectSearchModal",
    data: function () {
      return {
        l_tel: '',
        f_tel: '',
        search: 1,
        searchType: 'AND',
        searchArray: {
          project: {
            progress_status: '',
            construction_type: '1',
          },
          customer: {},
          builder: {},
          safety: {},
          chief: {},
          hospital: {},
          trades_chief: {
            trades_type: '',
          },
        },
        isMounted: false,
        projects: [],
        checked: true,
      }
    },

    mounted() {
      this.isMounted = true;
    },

    methods: {
      searchArrayProjects: function () {
        if (this.f_tel && !this.l_tel) {
          this.searchArray.project.tel = this.f_tel
        } else if (!this.f_tel && this.l_tel) {
          this.searchArray.project.tel = this.l_tel
        } else if (this.f_tel && this.l_tel) {
          this.searchArray.project.tel = this.f_tel + '-' + this.l_tel
        }
        axios.post('/api/getProjectSearch', {
          selectWord: this.search,
          searchType: this.searchType,
          searchArray: this.searchArray
        }).then((res) => {
          this.$emit('closeSearchModal', res.data);
        })
      },
      searchSelect($num) {
        this.search = $num;
      },
      closeDelModal() {
        this.delCheck = false;
      },

    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      }
    },
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }

  .flo {
    float: left;
    margin-left: 30px;
  }

  .clearfix input {
    border: 1px solid black;
  }
</style>
