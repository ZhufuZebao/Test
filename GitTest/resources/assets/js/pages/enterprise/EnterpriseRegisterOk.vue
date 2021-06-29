<template>
  <div class="enterprise-registerok">
    <div class="image">
      <div class="logo"></div>
    </div>
    <div class="message">登録が完了しました。</div>
    <div class="but">
      <router-link :to="{path:'/'}">
        <button>TOPページへ</button>
      </router-link>
    </div>
  </div>
</template>

<script>
  export default {
    name: "EnterpriseRegisterOk",
    created() {
      sessionStorage.removeItem('data');
    }
  }
</script>
