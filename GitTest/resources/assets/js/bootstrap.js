
window._ = require('lodash');

/**
 * Vue is a modern JavaScript library for building interactive web interfaces
 * using reactive data binding and reusable components. Vue's API is clean
 * and simple, leaving you to focus on building your next great project.
 */

window.Vue = require('vue');

/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 */

Vue.http.interceptors.push((request, next) => {
    request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);

    next();
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

const DEV_URL = process.env.MIX_APP_DIR;
const PROD_URL = '';
const BASE_URL = process.env.APP_ENV === 'production' ? PROD_URL : DEV_URL;
window.APP_IMAGE_SIZE_LIMIT = 5;
window.BASE_URL = BASE_URL;

import { getCookieValue } from './util'

window.axios = require('axios')

window.axios.defaults.baseURL = BASE_URL
// Ajaxリクエストであることを示すヘッダーを付与する
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

window.axios.interceptors.request.use(config => {
  // クッキーからトークンを取り出してヘッダーに添付する
  config.headers['X-XSRF-TOKEN'] = getCookieValue('XSRF-TOKEN')

  return config
})

window.axios.interceptors.response.use(
    response => {
      return response;
    },
    error => {
      if (error.response) {
        switch (error.response.status) {
            case 401:
            case 402:
            case 403:
                //  scope = admin 管理者インターフェースを要求します。アクセス権限がなければ、フロントはlogin Formページにジャンプします。
                if(error.response.data.error === '' || error.response.data.error === undefined){
                    if(error.response.data.params.scope === 'admin'){
                        location.href = 'loginForm';
                    }else{
                        location.href = 'login';
                    }
                }else {
                    location.href = 'login';
                }
                break;
          case 500:
            // alert('System Error!!');
            break;
          case 200:
            window.location.href = "/";
            break;
        }
      }
      return Promise.reject(error.response ? error.response.data : error);
    });
