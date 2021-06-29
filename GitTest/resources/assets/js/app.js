/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');
require('./initFirebase');
import Vue from 'vue'
import '../scss/custom.scss'
import router from './router'
import App from './App.vue'
import CommonModal from './components/customer/CommonModal.vue'

import { emoji } from '../utils/emoji.js'
Vue.prototype.emoji = emoji

Vue.config.productionTip = false
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('CommonModal', CommonModal);

/* const app = new Vue({
    el: '#app',
    router, // ルーティングの定義を読み込む
    components: { App }, // ルートコンポーネントの使用を宣言する
//    template: '<App />' // ルートコンポーネントを描画する
}); */

import infiniteScroll from './vue-infinite-scroll'
Vue.use(infiniteScroll)

const app = new Vue({
 render: h => h(App),
 router
}).$mount('#app')
