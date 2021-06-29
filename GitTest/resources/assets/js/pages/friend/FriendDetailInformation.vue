<template>
  <div class="container clearfix friend customerlist commonAll">
    <header>
      <h1>
        <router-link to="/friend">
          <div class="commonLogo">
            <ul>
              <li class="bold">FRIEND</li>
              <li>職人</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/friend' }"><span>職人一覧</span></el-breadcrumb-item>
          <el-breadcrumb-item><span>職人詳細</span></el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <section class="common-wrapper friend-detail">
      <div class="common-view project-detail">
        <div class="content">
          <router-link to="/friend">
            <span class="el-icon-arrow-left"></span>
          </router-link>
          <div class="friend-name-retu">
            <span class="name">{{friendInformation.name}}
            </span>
          </div>
          <table class="detail-table">
            <tr>
              <td  class="tdLeft">職種・対応可能な分野</td>
              <td class="tdRight" v-if="friendInformation.publicity === '1'">
                <div v-for="categorie in friendInformation.categories">
                  <span v-if="categorie">{{categorie.category_name1}}</span>
                  <span v-if="categorie">{{categorie.category_name2}}</span>
                  <span v-if="categorie">
                    <span v-if="!categorie.years_of_experience || categorie.years_of_experience === ''">未入力</span>
                    <span v-if="categorie.years_of_experience === 'under1'">一年未満</span>
                    <span v-if="categorie.years_of_experience === 'over1'">1年以上</span>
                    <span v-if="categorie.years_of_experience === 'over3'">3年以上</span>
                    <span v-if="categorie.years_of_experience === 'over10'">10年以上</span>
                  </span>
                  <br>
                </div>
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">得意な分野</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.specialty}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">会社名・屋号</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                <span v-if="friendInformation.company_div === '1'">法人</span>
                <span v-else>個人</span>
                <br>
                <span v-if="friendInformation.corporate_name_position === '1'">
                  <span v-if="friendInformation.corporate_type === '1'">株式会社</span>
                  <span v-if="friendInformation.corporate_type === '2'">有限会社</span>
                  <span v-if="friendInformation.corporate_type === '3'">合同会社</span>
                </span>
                {{friendInformation.company_name}}
                <span v-if="friendInformation.corporate_name_position === '2'">
                  <span v-if="friendInformation.corporate_type === '1'">株式会社</span>
                  <span v-if="friendInformation.corporate_type === '2'">有限会社</span>
                  <span v-if="friendInformation.corporate_type === '3'">合同会社</span>
                </span>
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">住所</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.pref_name}}{{friendInformation.city}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">対応可能エリア</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.workarea}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">誕生日</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.birth}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">性別</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                <span v-if="friendInformation.sex === '1'">男性</span>
                <span v-if="friendInformation.sex === '2'">女性</span>
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">免許資格</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.license}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">所属</td>
              <td  class="tdRight">
                {{friendInformation.company_name}}
              </td>
            </tr>
            <tr>
              <td  class="tdLeft">スキル</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.skill}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">電話番号</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.telno1}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">将来の夢</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.dream}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">座右の銘</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.motto}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
            <tr>
              <td  class="tdLeft">３～5年で実現したいこと</td>
              <td  class="tdRight" v-if="friendInformation.publicity === '1'">
                {{friendInformation.things_to_realize}}
              </td>
              <td v-else>{{noPublic}}</td>
            </tr>
          </table>

            <div class="pro-button">
              <router-link to="/friend">
              <a class="modoru">
                戻る
              </a>
              </router-link>
            </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import Calendar from "../../mixins/Calendar";

  export default {
    components: {
      UserProfile
    },
    mixins: [Messages,Calendar],
    data() {
      return {
        noPublic:'',
        friendInformation: {},
      }
    },
    mounted() {
      this.noPublic = this.commonMessage.warning.noPublic;
      this.$router.afterEach((to, from, next) => {
        window.scrollTo(0, 0)
      })
    },
    methods: {
      // 職人詳細画面
      fetchFriendDetailInformation: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.friendList;
        axios.post('/api/getFriendDetailInformation', {
          userId: this.$route.params.id
        }).then((res) => {
          this.friendInformation = res.data[0];
          if (this.friendInformation.birth) {
            this.friendInformation.birth = Calendar.dateFormat(this.friendInformation.birth);
          }
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
        loading.close();
      },
    },
    created() {
      this.fetchFriendDetailInformation();
    }
  }
</script>
<style scoped>
  .el-icon-arrow-left{
    font-size: 70px;
    color: #a9a9ab;
    position: absolute;
    left: -80px;
    top: -29px;
  }
</style>
