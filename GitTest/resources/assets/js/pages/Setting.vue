<template>
  <div class="wrapper low setup" id="top">
    <div class="container clearfix">
      <header>
        <h1><a href="javascript:void(0)">設定</a></h1>
        <ul class="header-nav setup">

        </ul>
        <div class="title-wrap">
        </div>
        <UserProfile/>
      </header>
      <section class="report-wrapper">
        <ul class="button_table">
          <li class="sel_btn">
            <router-link to="/setting/showUser">プロフィール</router-link>
          </li>

          <li class="sel_btn">
            <router-link to="/setting/pwd">パスワード変更</router-link>
          </li>
        </ul>
      </section>
    </div>
  </div>
</template>

<script>
  import UserProfile from '../components/common/UserProfile';

  export default {
    components: {UserProfile},
    name: 'setting'
  }

</script>

<style scoped>
  .sel_btn {
    display: block;
    height: 150px;
    line-height: 150px;
    width: 30%;
    background: #9dc815;
    border: 1px #110606 solid;
    border-radius: 3px;
    text-decoration: none;
    font-size: 40px;
    outline: none;
    text-align: center;
    margin: 50px auto
  }

  .button_table {
    margin-top: 40px;
  }

</style>