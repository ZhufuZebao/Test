import axios from 'axios';
const http = axios;

export default (Vue) => {
  Vue.http = http;
  Object.defineProperties(Vue.prototype, {
    $http: {
      get () {
        return http;
      }
    }
  });
};
/*
import axios from 'axios';
import { SET_LOADING } from 'js/store/mutation-types';

const http = axios;

export default (Vue) => {
    export default (Vue, { store }) => {
        http.interceptors.request.use((config) => {
            store.commit(SET_LOADING, true);
            return config;
        }, (error) => {
            // エラー処理
        });

        http.interceptors.response.use((response) => {
            store.commit(SET_LOADING, false);
            return response;
        }, (error) => {
            // エラー処理
        });

        Vue.http = http;
        Object.defineProperties(Vue.prototype, {
            $http: {
                get () {
                    return http;
                }
            }
        });
    };
};
*/