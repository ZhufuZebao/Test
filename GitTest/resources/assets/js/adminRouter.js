import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import Navigation from './admin/components/Navigation.vue'
//職人管理
const WorkerList = () => import(/* webpackChunkName: 'chunk/admin/worker' */ './admin/pages/worker/WorkerList.vue');
const WorkerDetailInformation = () => import(/* webpackChunkName: 'chunk/admin/worker' */ './admin/pages/worker/WorkerDetailInformation.vue');

const ContractOffice = () => import(/* webpackChunkName: 'chunk/admin/contract' */ "./admin/pages/contract/ContractOffice");
const ContractList = () => import(/* webpackChunkName: 'chunk/admin/contract' */ "./admin/pages/contract/ContractList");
const ContractDetail = () => import(/* webpackChunkName: 'chunk/admin/contract' */ "./admin/pages/contract/ContractDetail");
const ContractUpdate = () => import(/* webpackChunkName: 'chunk/admin/contract' */ "./admin/pages/contract/ContractUpdate");
const NoticeList = () => import(/* webpackChunkName: 'chunk/admin/notice' */ "./admin/pages/notice/NoticeList");
const NoticeCreate = () => import(/* webpackChunkName: 'chunk/admin/notice' */ "./admin/pages/notice/NoticeCreate");
const ContractEnterpriseCreate = () => import(/* webpackChunkName: 'chunk/admin/contract' */ "./admin/pages/contract/ContractEnterpriseCreate");

Vue.use(VueRouter);

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    redirect: '/notice'
  },
  {
    path: '/nav',
    component: Navigation,
    children: [
      {
        path: '/notice/edit',
        name: 'NoticeEdit',
        component: NoticeCreate
      },
      {
        path: '/notice',
        component: NoticeList
      },
      {
        path: '/notice/create',
        component: NoticeCreate
      },
      {
        path: '/worker',
        component: WorkerList
      },
      {
        path: '/worker/detailInformation/:id',
        component: WorkerDetailInformation
      },
      {
        path: '/contract/office',
        component: ContractOffice
      },
      {
        path: '/contract',
        component: ContractList
      },
      {
        path: '/contract/detail',
        component: ContractDetail
      },
      {
        path: '/contract/update',
        component: ContractUpdate
      },
      {
        path: '/contract/enterpriseCreate',
        component: ContractEnterpriseCreate
      },
    ]
  },

];

// VueRouterインスタンスを作成する
const router = new VueRouter({
  routes
});
// 前のページに戻るのを禁止
router.beforeEach((to, from, next) => {
  let msg = 'ほかのページに移動すると、入力中のデータは保存されません。よろしいですか？';
  if ((to.path === '/chat'
          || to.path === '/schedule'
          || to.path === '/company'
          || to.path === '/document'
          || to.path === '/invite'
          || to.path === '/friend'
          || to.path === '/project'
          || to.path === '/report'
          || to.path === '/customer')) {
    // let res = confirm(msg);

    return false;
  }
  next();
  history.pushState(null, null, location.href);
});
// VueRouterインスタンスをエクスポートする
export default router
