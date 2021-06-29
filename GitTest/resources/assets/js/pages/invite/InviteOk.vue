<template>
  <div class="all">
    <div class="image">
      <img src="images/logo.png">
    </div>
    <div class="ruleBorder1">
      &nbsp;&nbsp;&nbsp;&nbsp;協力会社になりました
    </div>

  </div>
</template>

<script>
  export default {
    data(){
      return {
        count:"",
      }
    },
    methods: {
      clickJump(){
        let timejump = 3;
        if(!this.timer){
          this.count = timejump ;
          this.timer = setInterval(()=>{
            if(this.count > 0 && this.count <= timejump ){
              this.count--;
            }else{
              clearInterval(this.timer);
              this.timer = null;
              this.$router.push({path: '/'});
            }
          },1000)
        }
      },
    },
    created(){
      this.clickJump();
    }
  }
</script>

<style scoped>
  .all{
    background-color: #b1e917;
    position: relative;
    width: 100vw;
    height: 100vh;
    padding: 5%;
  }
  .image {
    text-align: center;
  }
  img {
    width: 300px;
    height: 100px;
    background-color: #b1e917;
  }
  .ruleBorder1 {
    /*width: 480px;*/
    /*height: 300px;*/
    /*position: absolute;*/
    /*left: 50%;*/
    /*margin-top:40px;*/
    /*margin-left: -240px;*/
    /*padding-top: 10px;*/
    /*border: 1px solid #000;*/
    /*overflow-y: scroll;*/
    font-size: larger;
    margin: 10% auto;
    text-align: center;
  }
  .agree input {
    width: 15px;
    height: 15px;
  }
</style>