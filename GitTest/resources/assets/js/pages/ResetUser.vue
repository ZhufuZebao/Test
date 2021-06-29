<template>
  <div class="wrapper low setup" id="top">
    <div class="container clearfix">
      <header>
        <h1><a href="javascript:void(0)">設定</a></h1>
        <ul class="header-nav document">

        </ul>
        <div class="title-wrap">
        </div>
        <UserProfile/>
      </header>

      <section class="report-wrapper">
        <div class="report-deteil-wrap edit clearfix">
          <dl class="clearfix">
            <dt>
              <img :src="avatar" class="img-avatar" id="img" alt="写真を変更">
              <label for="img"></label>
              <!--<input type="file" accept="image/gif,image/jpeg,image/jpg,image/png"-->
              <!--@change="changeImage($event)" value="button" :hidden="disabled">-->
            </dt>
          </dl>
          <dl class="clearfix">
            <dt class="user icon-s">姓</dt>
            <dd><input placeholder="姓" type="text" v-model="saveForm.last_name" :disabled="disabled">
              <span class="danger">*</span>
              <span class="danger">{{last_name}}</span></dd>


            <dt class="user icon-s">名</dt>
            <dd><input placeholder="名" type="text" v-model="saveForm.first_name" :disabled="disabled">
              <span class="danger">*</span>
              <span class="danger">{{first_name}}</span></dd>

            <dt><label for="category">職種・対応可能な分野</label></dt>
            <dd>
              <select :disabled="disabled" id="category" v-model="saveForm.category">
                <option value=null >職種</option>
                <option value="1" >内装</option>
                <option value="2" >外装</option>
                <option value="3" >全装</option>
              </select>
              <span class="danger">*</span>
              <span class="danger">{{category}}</span></dd>

            <dt class="construction icon-s">得意な分野</dt>
            <dd><input placeholder="得意な分野" type="text" v-model="saveForm.specialty"
                       :disabled="disabled">
              <span class="danger">*</span>
              <span class="danger">{{specialty}}</span></dd>

            <dt class="construction icon-s">郵便番号</dt>
            <dd><input placeholder="郵便番号" type="number" v-model="saveForm.zip"
                       :disabled="disabled">
              <span class="danger">*</span>
              <span class="danger">{{zip}}</span></dd>

            <dt class="place icon-s"><label for="pref">都道府県</label></dt>
            <dd>
              <select name="pref" v-model="saveForm.pref" :disabled="disabled" id="pref">
                <option value=null>都道府県</option>
                <option v-for="pre in prefs" :value="pre.id">
                  {{ pre.name }}
                </option>
              </select>
              <span class="danger">*</span>
              <span class="danger">{{zip}}</span></dd>
            <dt class="place icon-s">市区町村</dt>
            <dd><input placeholder="市区町村" type="text"
                       v-model="saveForm.addr_code"
                       :disabled="disabled"
                       id="addr_code">
              <span class="danger">*</span>
              <span class="danger">{{addr_code}}</span></dd>


            <dt>対応可能エリア</dt>
            <dd>
              <label for="area1"></label>
              <select name="area1" v-model="saveForm.area1" :disabled="disabled" id="area1">
                <option value="1">1</option>
                <option value="2">2</option>
              </select>
              <label for="area2"></label>
              <select name="area2" v-model="saveForm.area2" :disabled="disabled" id="area2">
                <option value="1">2</option>
                <option value="2">3</option>
              </select>
              <label for="area3"></label>
              <select name="area3" v-model="saveForm.area3" :disabled="disabled" id="area3">
                <option value="1">4</option>
                <option value="2">5</option>
              </select>
              <label for="area4"></label>
              <select name="area4" v-model="saveForm.area4" :disabled="disabled" id="area4">
                <option value="1">6</option>
                <option value="2">7</option>
              </select>
              <label for="area5"></label>
              <select name="area5" v-model="saveForm.area5" :disabled="disabled" id="area5">
                <option value="1">8</option>
                <option value="2">9</option>
              </select>
            </dd>


            <dt class="schedule icon-s">生年月日</dt>
            <dd><input placeholder="生年月日" type="date" v-model="saveForm.birth"
                       :disabled="disabled"></dd>


            <dt>姓別</dt>
            <dd>
              <input type="radio" id="men" :value="0" v-model="saveForm.sex" :disabled="disabled">
              <label for="men" >男</label>
            </dd>
            <dt>&nbsp;&nbsp;</dt>
            <dd>
              <input type="radio" id="women" :value="1" v-model="saveForm.sex" :disabled="disabled">
              <label for="women">女</label>
            </dd>


            <dt class="note icon-s">免許資格</dt>
            <dd><input placeholder="免許資格" type="text" v-model="saveForm.license"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">職歴</dt>
            <dd><input placeholder="職歴" type="text" v-model="saveForm.career"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">所属</dt>
            <dd><input placeholder="所属" type="text" v-model="saveForm.belong"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">スキル</dt>
            <dd><input placeholder="スキル" type="text" v-model="saveForm.skill"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">希望条件</dt>
            <dd><input placeholder="希望条件" type="text"
                       v-model="saveForm.desired_condition"
                       :disabled="disabled"></dd>


            <dt><label for="flag1">未経験</label></dt>
            <dd><input placeholder="未経験" id="flag1" type="checkbox" name="flag1" v-model="saveForm.flag1"
                       :disabled="disabled" style="vertical-align: middle">
            </dd>


            <dt><label for="flag2">師匠募集</label></dt>
            <dd><input placeholder="師匠募集" id="flag2" type="checkbox" name="flag2" v-model="saveForm.flag2"
                       :disabled="disabled" style="vertical-align: middle">
            </dd>


            <dt><label for="flag3">弟子希望</label></dt>
            <dd><input placeholder="弟子希望" id="flag3" type="checkbox" name="flag3" v-model="saveForm.flag3"
                       :disabled="disabled" style="vertical-align: middle">
            </dd>


            <dt class="tel icon-s">電話番号</dt>
            <dd><input placeholder="電話番号" type="text" v-model="saveForm.telno1"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">将来の夢</dt>
            <dd><input placeholder="将来の夢" type="text" v-model="saveForm.dream"
                       :disabled="disabled"></dd>


            <dt class="note icon-s">座右の銘</dt>
            <dd><input placeholder="座右の銘" type="text" v-model="saveForm.motto"
                       :disabled="disabled"></dd>

            <dt class="note icon-s">3〜5年で実現したいこと</dt>
            <dd><input placeholder="5年で実現したいこと" type="text"
                       v-model="saveForm.things_to_realize"
                       :disabled="disabled"></dd>

          </dl>

          <div class="clearfix"></div>

          <div class="button-wrap clearfix">
            <div class="button-lower remark" v-if="$route.name==='settingShowUser'">
              <a href="javascript:void(0)" @click="show">編集</a>
            </div>
            <div v-else>
              <div class="button-lower remark">
                <a href="javascript:void(0)" @click="edit">設定</a>
              </div>
              <router-link to="/setting">キャンセル</router-link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'
  import UserProfile from "../components/common/UserProfile";

  export default {
    components: {
      UserProfile,
    },
    data() {
      return {
        hidden: false,
        disabled: true,
        avatar: 'images/friend-000001.png',

        last_name: '',
        first_name: '',
        category: '',
        specialty: '',
        zip: '',
        pref: '',
        addr_code: '',

        saveForm: {
          name: '',
          last_name: '',      //姓
          first_name: '',     //名

          category: '',       //職種・対応可能な分野
          category1: '',
          category2: '',
          category3: '',

          specialty: '',      //得意な分野
          zip: '',            //郵便番号
          pref: '',           //都道府県
          addr_code: '',      //市区町村

          area1: '',          //対応可能エリア
          area2: '',
          area3: '',
          area4: '',
          area5: '',

          birth: '',          //生年月日
          sex: '',            //姓別
          license: '',           //免許資格
          career: '',         //職歴
          belong: '',         //所属
          skill: '',          //スキル
          desired_condition: '',          //希望条件

          flag1: '',          //未経験
          flag2: '',          //師匠募集
          flag3: '',          //弟子希望

          telno1: '',         //電話番号
          dream: '',          //将来の夢
          motto: '',          //座右の銘
          things_to_realize: '',//3〜5年で実現したいこと
        }
      }
    },

    methods: {
      fetchReports: function () {
        axios.get('/api/setting/user').then((res) => {
          this.saveForm = res.data;
          if (!this.saveForm.sex){
            this.saveForm.sex=0;
          }
        })
      }
      , show: function () {
        this.disabled = false;
        this.$router.push({path: '/setting/editUser'});
      }
      , edit: function () {
        if (!this.saveForm.last_name) {
          this.last_name = '姓は空にできません';
          return false;
        }else {
          this.last_name = '';
        }
        if (!this.saveForm.first_name) {
          this.first_name = '名は空にできません';
          return false;
        }else {
          this.last_name = '';
        }
        if (!this.saveForm.category) {
          this.category = '職種・対応可能な分野は空にできません';
          return false;
        }else {
          this.category = '';
        }
        if (!this.saveForm.specialty) {
          this.specialty = '得意な分野は空にできません';
          return false;
        }else {
          this.specialty = '';
        }
        if (!this.saveForm.zip) {
          this.zip = '郵便番号は空にできません';
          return false;
        }else {
          this.zip = '';
        }
        if (!this.saveForm.pref) {
          this.pref = '都道府県は空にできません';
          return false;
        }else {
          this.pref = '';
        }
        if (!this.saveForm.addr_code) {
          this.addr_code = '市区町村は空にできません';
          return false;
        }else {
          this.addr_code = '';
        }

        axios.post('/api/setting/user', {
          saveForm: this.saveForm
        }).then(res => {
          console.log(res.data);
          this.saved = true;
          this.$router.push({path: '/setting'});
        });
      }


    },

    created() {
      this.fetchReports()
    },
  }

</script>

<style scoped>
  #men {
    width: 23px;
  }

  #women {
    width: 23px;
    margin-left: 23px;
  }

  .danger {
    color: #ff0000;
  }

  #addr_code {
    width: 230px;
  }
</style>
