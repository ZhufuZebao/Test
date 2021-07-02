import Vue from 'vue'
import VueRouter from 'vue-router'

// ページコンポーネントをインポートする
import Navigation from './components/Navigation.vue'
const Chat = () => import(/* webpackChunkName: 'chunk/chat' */ './pages/chat/Chat.vue')
const CustomerList = () => import(/* webpackChunkName: 'chunk/customer' */ './pages/customer/CustomerList.vue')
const CustomerCreate = () => import(/* webpackChunkName: 'chunk/customer' */ './pages/customer/CustomerCreate.vue')
const ProjectList = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectList.vue')
const AccountList = () => import(/* webpackChunkName: 'chunk/account' */ './components/account/AccountList.vue')
const AccountCreate = () => import(/* webpackChunkName: 'chunk/account' */ './pages/account/AccountCreate.vue')
const Dashbord = () => import(/* webpackChunkName: 'chunk/dashbord' */ './pages/Dashbord.vue')
const ReportList = () => import(/* webpackChunkName: 'chunk/report' */ './pages/report/ReportList.vue')
const ReportCreate = () => import(/* webpackChunkName: 'chunk/report' */ './pages/report/ReportCreate.vue')
const ReportDetailCreate = () => import(/* webpackChunkName: 'chunk/report' */ './pages/report/ReportDetailCreate.vue')
const ReportDetail = () => import(/* webpackChunkName: 'chunk/report' */ './pages/report/ReportDetail.vue')
const Setting = () => import(/* webpackChunkName: 'chunk/setting' */ './pages/Setting.vue')
const ResetPwd = () => import(/* webpackChunkName: 'chunk/setting' */ './pages/ResetPwd.vue')
const ResetUser = () => import(/* webpackChunkName: 'chunk/setting' */ './pages/ResetUser.vue')
const ProjectCreate = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectCreate.vue')
const FriendList = () => import(/* webpackChunkName: 'chunk/friend' */ './pages/friend/FriendList.vue')
const FriendDetailInformation = () => import(/* webpackChunkName: 'chunk/friend' */ './pages/friend/FriendDetailInformation.vue')
const ScheduleMonth = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleMonth.vue');
const ScheduleWeek = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleWeek.vue')
const ScheduleOrgWeek = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleOrgWeek.vue');
const ScheduleCreate = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleCreate.vue');
const ScheduleDetail = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleDetail.vue');
const SchedulePeopleMonth = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/SchedulePeopleMonth.vue');
const ScheduleDay = () => import(/* webpackChunkName: 'chunk/schedule' */ './pages/schedule/ScheduleDay');
const ProjectDetail = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectDetail')
// const EnterpriseModal = () => import(/* webpackChunkName: 'chunk/enterprise' */ ‘./components/enterprise/Enterprise';
// const EnterpriseDetailModal = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseDetail';
const EnterpriseMailModal = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseMailReset.vue');
const documentList = () => import(/* webpackChunkName: 'chunk/document' */ './pages/document/documentList.vue');
// const EnterprisePwdModal = () => import(/* webpackChunkName: 'chunk/enterprise' */ './components/enterprise/EnterprisePwdReset';
const EnterpriseAfterMail = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseAfterMail.vue');
const EnterpriseRule = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseRule.vue')
const EnterpriseRegister = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseRegister.vue')
const EnterpriseRegisterSure = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseRegisterSure.vue')
const EnterpriseMailValidate = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseMailValidate.vue')
const EnterpriseRegisterOk = () => import(/* webpackChunkName: 'chunk/enterprise' */ './pages/enterprise/EnterpriseRegisterOk.vue')
const CustomerDetail = () => import(/* webpackChunkName: 'chunk/customer' */ './pages/customer/CustomerDetail.vue')
const CustomerOffice = () => import(/* webpackChunkName: 'chunk/customer' */ './pages/customer/CustomerOffice.vue')
const ProjectBasisInformation = () => import(/* webpackChunkName: 'chunk/project' */ './components/project/ProjectBasisInformation.vue')
const ProjectCompanyInformation = () => import(/* webpackChunkName: 'chunk/project' */ './components/project/ProjectCompanyInformation.vue')
const ProjectSafetyInformation = () => import(/* webpackChunkName: 'chunk/project' */ './components/project/ProjectSafetyInformation.vue')
const ProjectUpdate = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectUpdate.vue')
const ProjectShow = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectShow.vue')
const ErrorMail = () => import(/* webpackChunkName: 'chunk/errorMail' */ './components/common/ErrorMail.vue')
const InviteList = () => import(/* webpackChunkName: 'chunk/invite' */ './pages/invite/InviteList.vue')
const InviteOk = () => import(/* webpackChunkName: 'chunk/invite' */ './pages/invite/InviteOk.vue')
const InviteNo = () => import(/* webpackChunkName: 'chunk/invite' */ './pages/invite/InviteNo.vue')
const InviteNot = () => import(/* webpackChunkName: 'chunk/invite' */ './pages/invite/InviteNot.vue')
const ProjectMap = () => import(/* webpackChunkName: 'chunk/project' */ './pages/project/ProjectMap.vue')
const InviteCooperatorRegister = () => import(/* webpackChunkName: 'chunk/invite' */ './pages/invite/InviteCooperatorRegister.vue')
const ContactList = () => import(/* webpackChunkName: 'chunk/contact' */ './pages/contact/ContactList.vue')
const CompanyCreate = () => import(/* webpackChunkName: 'chunk/company' */ './pages/company/CompanyCreate.vue');
const CompanyDetail = () => import(/* webpackChunkName: 'chunk/company' */ './pages/company/CompanyDetail.vue');
const CompanyList = () => import(/* webpackChunkName: 'chunk/company' */ './pages/company/CompanyList.vue');
const dashboardList = () => import(/* webpackChunkName: 'chunk/dashboard' */ './pages/dashboard/dashboardList.vue')
const SysNoticeDetail = () => import(/* webpackChunkName: 'chunk/sysNotice' */ './pages/dashboard/dashboardSysNoticeDetail.vue')
const ProjectDetails = () => import(/* webpackChunkName: 'chunk/project' */ "./pages/project/ProjectDetails.vue")
const TestView = () => import('./pages/TestView.vue')
const TestLearn = () => import('./pages/TestLearn.vue')
Vue.use(VueRouter);

// パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/nav',
    component: Navigation,
    children: [
      {
        path: '/TestLearn',
        component: TestLearn
      },

      {
        path: '/dashboard',
        component: dashboardList
      },
      {
        path: '/dashboard/sysNoticeDetail/:id',
        component: SysNoticeDetail
      },
      {
        path: '/document',
        component: documentList
      }, {
        path: '/error',
        component: ErrorMail
      }, {
        path: '/enterprise/afterMail',
        component: EnterpriseAfterMail
      }, {
        path: '/enterprise/mail',
        component: EnterpriseMailModal
      }, {
        path: '/chat',
        component: Chat
      }, {
        path: '/customer',
        component: CustomerList
      },
      {
        path: '/customer/create',
        component: CustomerCreate
      },
      {
        path: '/customer/office/:id',
        name: 'office',
        component: CustomerOffice
      },
      {
        path: '/customer/edit',
        name: 'edit',
        component: CustomerCreate
      },
      {
        path: '/project',
        component: ProjectList
      },
      {
        path: '/project/show/:id',
        component: ProjectShow
      },
      {
        path: '/project/map',
        component: ProjectMap
      },
      // {
      //   path: '/report',
      //   component: ReportList
      // },
      // {
      //   path: '/report/create',
      //   component: ReportCreate
      // },
      // {
      //   path: '/report/edit/:id',
      //   name: 'reportEdit',
      //   component: ReportCreate
      // },
      // {
      //   path: '/report/detail/:id',
      //   name: 'reportDetail',
      //   component: ReportDetail
      // },
      {
        path: '/setting/',
        component: Setting
      },
      {
        path: '/setting/pwd',
        component: ResetPwd
      },
      {
        path: '/setting/showUser',
        name: 'settingShowUser',
        component: ResetUser
      },
      {
        path: '/setting/editUser',
        name: 'settingEditUser',
        component: ResetUser
      },
      {
        path: '/project/create',
        component: ProjectCreate
      },
      {
        path: '/project/edit/:id',
        name: 'projectEdit',
        component: ProjectCreate
      },
      {
        path: '/project/detail/:id',
        name: 'projectDetail',
        component: ProjectDetail
      },
      {
        path: '',
        component: Dashbord
      },
      {
        path: '/friend',
        component: FriendList
      },
      {
        path: '/friend/detailInformation/:id',
        component: FriendDetailInformation
      },
      {
        path: '/schedule',
        component: ScheduleOrgWeek
      },
      {
          path: '/schedule/scheduleDay',
          component: ScheduleDay
      },
      {
        path: '/schedule/week',
        component: ScheduleWeek
      },
      {
        path: '/schedule/org/week',
        component: ScheduleOrgWeek
      },
      {
        path: '/schedule/create',
        component: ScheduleCreate
      },
      {
        path: '/schedule/edit',
        name: 'scheduleEdit',
        component: ScheduleCreate
      },
      {
        path: '/schedule/detail',
        component: ScheduleDetail
      },
      {
        path: '/schedule/people/month',
        component: SchedulePeopleMonth
      },
      {
        path: '/customer/detail/:id',
        name: 'customerDetail',
        component: CustomerDetail
      },
      {
        path: '/project/createBasisInformation',
        name: 'projectCreateBasisInformation',
        component: ProjectBasisInformation
      },
      {
        path: '/project/updateBasisInformation/:id',
        name: 'projectUpdateBasisInformation',
        component: ProjectBasisInformation
      },
      {
        path: '/project/createCompanyInformation',
        name: 'projectCreateCompanyInformation',
        component: ProjectCompanyInformation
      },
      {
        path: '/project/updateCompanyInformation/:id',
        name: 'projectUpdateCompanyInformation',
        component: ProjectCompanyInformation
      },
      {
        path: '/project/createSafetyInformation',
        name: 'projectCreateSafetyInformation',
        component: ProjectSafetyInformation
      },
      {
        path: '/project/updateSafetyInformation/:id',
        name: 'projectUpdateSafetyInformation',
        component: ProjectSafetyInformation
      },
      {
        path: '/project/update/:id',
        name: 'ProjectUpdate',
        component: ProjectUpdate
      },
      {
        path: '/invite',
        component: InviteList
      },
      // {
      //   path: '/report',
      //   component: ReportList
      // },
      {
        path: '/contact',
        component: ContactList
      },
      {
        path: '/contact/invite',
        component: InviteList
      },
      {
        path: '/contact/friend',
        component: FriendList
      },
      {
        path: '/company/create',
        component: CompanyCreate
      },
      {
        path: '/company/detail/:id',
        name: 'companyDetail',
        component: CompanyDetail
      },
       {
        path: '/company',
        component: CompanyList
      },
      {
        path: '/report',
        component: ReportList
      },
      {
        path: '/report/detail/:id',
        component: ReportDetailCreate,
        name: 'ReportDetail',
      },
      {
        path: '/report/create/:id',
        component: ReportDetailCreate
      },
      {
          path: '/project/details/:id',
          component: ProjectDetails
      },
    ]
  },
  {
    path: '/enterprise/rule',
    component: EnterpriseRule
  },
  {
    path: '/enterprise/register',
    component: EnterpriseRegister
  },
  {
    path: '/enterprise/registerSure',
    component: EnterpriseRegisterSure
  },
  {
    path: '/enterprise/mailValidate',
    component: EnterpriseMailValidate
  },
  {
    path: '/enterprise/registerOk',
    component: EnterpriseRegisterOk
  },
  {
    path: '/invite/ok',
    component: InviteOk
  },
  {
    path: '/invite/no',
    component: InviteNo
  },
  {
    path: '/invite/not',
    component: InviteNot
  },
  {
    path: '/enterprise/rule/:fromUserId/:token',
    component: EnterpriseRule
  },
  {
    path: '/invite/cooperator/register/:token',
    component: InviteCooperatorRegister
  },
    {
        path : '/TestView',
        component: TestView
    },

];

// VueRouterインスタンスを作成する
const router = new VueRouter({
  routes
});
// 前のページに戻るのを禁止
router.beforeEach((to, from, next) => {
  let msg = 'ほかのページに移動すると、入力中のデータは保存されません。よろしいですか？';
  if (to.query.flag !== 'success' &&
      (from.path.indexOf('/project/create') !== -1
          || from.path.indexOf("/project/update") !== -1
          || from.path.indexOf("/customer/edit") !== -1
          || from.path.indexOf('/customer/create') !== -1
          || from.path.indexOf('/report/create') !== -1
          || from.path.indexOf('/report/detail') !== -1
          || from.path.indexOf('/schedule/edit') !== -1
          || from.path.indexOf('/company/create') !== -1
          || from.path.indexOf('/schedule/create') !== -1) &&
      (to.path === '/chat'
          || to.path === '/dashboard'
          || to.path === '/schedule'
          || to.path === '/company'
          || to.path === '/document'
          || to.path === '/invite'
          || to.path === '/friend'
          || to.path === '/contact'
          || to.path === '/project'
          || to.path === '/report'
          || to.path === '/customer')) {
    let res = confirm(msg);
    if (res) {
      next();
    } else {
      return false;
    }
  }
  next();
  history.pushState(null, null, location.href);
});
// VueRouterインスタンスをエクスポートする
export default router
