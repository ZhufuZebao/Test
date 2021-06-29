import Vue from 'vue'

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
Vue.config.productionTip = false

require('./bootstrap');

window.Vue = require('vue');
import '../scss/custom.scss'
import App from './App.vue'
import router from "./adminRouter";

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = new Vue({
 el: '#admin',
 render: h => h(App),
 router
})
