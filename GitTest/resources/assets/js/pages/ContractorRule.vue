<template>
        <div class="rulePage" >
            <div class="rule">利用規約</div>
            <div class="ruleNaiyou">プライバシーポリシー</div>
            <div class="ruleButton" id="check">
                <input type="checkbox" name="checkbox" id="item1" checked v-model="IsChecked" v-on:click="buttonStatus">
                <router-link to="/contractor/register"><input type="submit" id='button' value="上記に同意しました" disabled></router-link>
            </div>
        </div>
</template>

<script>
    export default {
        name: 'CustomerCreate',
        data() {
            return {
                IsChecked:false
            }
        },
        methods:{
            buttonStatus:function() {
                if(this.IsChecked){
                    $("#button").attr("disabled", true);
                }else {
                    $("#button").attr("disabled", false);
                }
            }
        }
    }
</script>

<style scoped>

</style>