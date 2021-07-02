<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/TestViewAdd','TestViewController@testViewAdd');
Route::get('/TestViewSelect','TestViewController@testViewSelect');
Route::post('/TestViewDelete','TestViewController@testViewDelete');
Route::post('/TestViewUpdateSelect','TestViewController@testViewUpdateSelect');
Route::post('/TestViewUpdate','TestViewController@testViewUpdate');

Route::post('/createTestLearn','TestLearnController@createTestLearn');
Route::get('/selectTestLearn','TestLearnController@selectTestLearn');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Off側の作業
// Public
Route::middleware('guestandauth')->namespace('Web')->group(function () {
    // 事業者登録
    Route::post('/enterpriseLogin', 'EnterpriseController@store');
    Route::get('/zipCloud', 'PublicController@zipCloud');
    Route::post('/enterpriseGetAuthKey', 'EnterpriseController@getAuthKey');
    Route::post('/mailUnique', 'EnterpriseController@mailUnique');
    Route::post('/userLogin', 'EnterpriseController@loginIn');
    Route::post('/enterpriseContain', 'EnterpriseController@enterpriseContain');
    Route::post('/cooperatorRegister', 'InviteController@cooperatorRegister');
    Route::post('/cooperatorFetch', 'InviteController@cooperatorFetch');
    //PDF2IMG
    Route::post('/pdfConvert', 'ConvertController@pdf2img');
    Route::post('/getOnePage', 'ConvertController@getOnePage');
    Route::post('/uploadPdf', 'ConvertController@storePdfFile');
    Route::post('/pushChatCall', 'ChatController@pushChatCall');
    //FIrebase
    Route::post('/firebaseTokenUpdate', 'FirebaseNotificationController@saveFirebaseID');
    Route::post('/deleteFirebaseID', 'FirebaseNotificationController@deleteFirebaseID');


});
Route::middleware('auth', 'authentication')->namespace('Web')->group(function () {
    //ダッシュボード
    Route::get('/getDashboardList', 'DashboardController@getDashboardList');
    Route::post('/editDashBoardStatus', 'DashboardController@editDashBoardStatus');
    Route::post('/getSysNoticeDetail', 'DashboardController@getSysNoticeDetail');
    Route::post('/setProjectFixedLabel', 'DashboardController@setProjectFixedLabel');
    Route::post('/clearNewMsg', 'DashboardController@clearNewMsg');
    Route::post('/clearProjectNum', 'DashboardController@clearProjectNum');
    Route::post('/getDashboardNewMsg', 'DashboardController@getDashboardNewMsg');
    Route::post('/setNodeId', 'DashboardController@setNodeId');
    // 日報
//    Route::get('/getReportList', 'ReportController@index');
//    Route::get('/getReportSearch', 'ReportController@search');
//    Route::post('/getReportDetail', 'ReportController@show');
//    Route::post('/setReport', 'ReportController@store');
//    Route::post('/editReport', 'ReportController@update');
    //設定 setting 削除予定
    // Route::post('/setting/pwd', 'SettingController@postReset');
    // Route::get('/setting/user', 'SettingController@getUser')->name('setting.user');
    // Route::post('/setting/user', 'SettingController@editUser');
    //事業者プロファイル
    Route::get('/getFilesNeedSent', 'EnterpriseController@getFilesBeforeSent');
    Route::get('/getEnterprisesList', 'EnterpriseController@index');
    Route::get('/getNoticeCount', 'EnterpriseController@getNoticeCount');
    Route::post('/noticeAlreadyRead', 'EnterpriseController@noticeAlreadyRead');
    Route::post('/setEnterprisesList/detail', 'EnterpriseController@edit');
    Route::post('/setEnterprisesList/pwd', 'EnterpriseController@editPwd');
    Route::post('/setEnterprisesList/mail', 'EnterpriseController@editMail');
    Route::post('/getEnterprisesImageUrl', 'EnterpriseController@getImageUrl');
    Route::post('/getEnterprisesVerifyPwd', 'EnterpriseController@verifyPwd');
    Route::post('/editMailUnique', 'EnterpriseController@editMailUnique');
    // 案件
    Route::get('/getProjectDetail', 'ProjectController@show');
    Route::get('/getProjectObject', 'ProjectController@getProject');
    Route::get('/getProjectAndCustomer', 'ProjectController@getProjectAndCustomer');
    Route::post('/getProjectList', 'ProjectController@index');
    Route::post('/getMapProjectList', 'ProjectController@getMapProjectList');
    Route::get('/getProjectCustomerOffice', 'ProjectController@showCustomerOffice');
    Route::post('/getProjectSearch', 'ProjectController@getProjectSearch');
    Route::post('/setProject', 'ProjectController@store');
    Route::post('/editProject', 'ProjectController@update');
    Route::post('/getCustomerRoleName', 'ProjectController@getCustomerRoleName');
    Route::post('/getProjectImageUrl', 'ProjectController@getImageUrl');
    Route::post('/deleteProject', 'ProjectController@deleteProject');
    Route::post('/getParticipants', 'ProjectController@getParticipants');
    Route::post('/getChatMassage', 'ProjectController@getChatMessage');
    Route::post('/insertProjectParticipants', 'ProjectController@insertToProjectParticipants');
    Route::post('/getProjectParticipants', 'ProjectController@getProjectParticipants');
    Route::post('/delProjectParticipant', 'ProjectController@delProjectParticipant');
    Route::post('/getFileList', 'ProjectController@getFileList');
    Route::post('/updateProjectProgressStatus', 'ProjectController@updateProjectProgressStatus');
    //共通
    Route::post('/upload/{type}', 'PublicController@upload');
    Route::post('/setBrowse', 'PublicController@setBrowse');
    //施主
    Route::get('/getCustomer', 'CustomerController@getCustomer');
    Route::post('/getCustomerOffice', 'CustomerController@getCustomerOffice');
    Route::post('/createCustomer', 'CustomerController@createCustomer');
    Route::get('/getEditCustomer', 'CustomerController@getEditCustomer');
    Route::post('/editCustomerOffice', 'CustomerController@update');
    Route::post('/updateOffice', 'CustomerController@updateOffice');
    Route::get('/getCustomerName', 'CustomerController@getCustomerName');
    Route::post('/deleteCustomerOffice', 'CustomerController@deleteOffice');
    Route::post('/updateCustomerName', 'CustomerController@updateCustomerName');
    Route::get('/getCustomerDetail', 'CustomerController@show');
    Route::post('/deleteCustomer', 'CustomerController@delete');
    Route::get('/getCustomerList', 'CustomerController@getlist');
    Route::post('/customerDetailedSearch', 'CustomerController@detailedSearch');
    Route::post('/searchCustomers', 'CustomerController@easySearch');
    Route::get('/getOfficeDetail', 'CustomerController@officeDetail');

    // アカウント管理
    Route::post('/switchIdentity', 'AccountController@switchIdentity');
    Route::get('/getAccountList', 'AccountController@index');
    Route::get('/getAccountSearch', 'AccountController@search');
    Route::post('/deleteAccount', 'AccountController@delete');
    Route::post('/setAccount', 'AccountController@create');
    Route::post('/mailConfirm', 'AccountController@mailConfirm');
    Route::post('/mailExist', 'AccountController@mailExist');
    Route::get('/getAccountInviteList', 'AccountController@getAccountInviteList');
    Route::post('/getAccountMsg', 'AccountController@getAccountMsg');
    Route::post('/editMailConfirm', 'AccountController@editMailConfirm');
    Route::get('/getEnterpriseAmount', 'AccountController@getEnterpriseAmount');

    //スケジュール
    Route::post('/setSchedule', 'ScheduleController@save');
    Route::post('/getJapaneseDate', 'ScheduleController@getJapaneseDate');
    Route::get('/searchParticipants', 'ScheduleController@searchUser');
    Route::post('/getScheduleList', 'ScheduleController@index');
    Route::get('/getScheduleById', 'ScheduleController@getScheduleById');
    Route::delete('/deleteScheduleById', 'ScheduleController@deleteScheduleById');
    Route::post('/VerifyParticipant', 'ScheduleController@VerifyParticipant');
	Route::post('/getCaseList', 'ScheduleController@getCaseList');
	Route::post('/getScheduleListSelect', 'ScheduleController@getScheduleListSelect');
    Route::post('/indexToday', 'ScheduleController@indexToday');
    Route::post('/dashboardScheduleCheck', 'ScheduleController@dashboardScheduleCheck');
    Route::post('/checkSchedule', 'ScheduleController@checkSchedule');


    // スケジュール(個人週間)
    Route::post('/getScheduleWeekList', 'ScheduleController@indexSchWeek');
    Route::post('/indexSchWeekTest', 'ScheduleController@indexSchWeekTest');
    Route::post('/deleteSubScheduleWeek', 'ScheduleController@deleteSubScheduleWeek');
    Route::post('/deleteSchScheduleWeek', 'ScheduleController@deleteSchScheduleWeek');
    Route::post('/getScheduleSubjects', 'ScheduleController@getScheduleSubjects');
    Route::get('/getUser', 'ScheduleController@getUser');
    Route::get('/getOtherUser', 'ScheduleController@getOtherUser');
    Route::get('/checkUserClearGroup', 'ScheduleController@checkUserClearGroup');
    //チャット
    Route::post('/getPro', 'ChatController@getPro');
    Route::post('/getGroupDetailUser', 'ChatController@getGroupDetailUser');
    Route::post('/uploadFiles', 'ChatController@uploadFiles');
    Route::post('/getGroup', 'ChatController@getGroup');
    Route::post('/getPersonList', 'ChatController@getPersonList');
    Route::post('/getProjectChatList', 'ChatController@getProjectChatList');
    Route::post('/editGroup', 'ChatController@editGroup');
    Route::post('/getGroupContact', 'ChatController@getGroupContact');
    Route::get('/getEnterpriseName', 'ChatController@getEnterpriseName');
    Route::post('/getChatList', 'ChatController@getChatList');
    Route::post('/setChatMessage', 'ChatController@setChatMessage');
    Route::post('/delChatMessage', 'ChatController@delChatMessage');
    Route::post('/getSearchPersonList', 'ChatController@getSearchPersonList');
    // 旧Route、後で削除
    // Route::post('/getChatPersonList', 'ChatController@ChatPersonList');
    // 新Route：案件グループ以外のチャット連絡先リスト取得
    Route::post('/getChatPersonList', 'ChatController@getContactsWithoutProjectGroup');
    Route::post('/getContactsWithoutProjectGroup', 'ChatController@getContactsWithoutProjectGroup');
    Route::post('/getProjGroupsChatList', 'ChatController@getProjGroupsChatList');
    Route::post('/getChatTaskList', 'ChatController@getChatTaskList');
    Route::post('/getChatFileList', 'ChatController@getChatFileList');
    Route::get('/getGroupFileList', 'ChatController@getGroupFileList');
    Route::post('/setChatTask', 'ChatController@insertChatTask');
    Route::post('/deleteChatTask', 'ChatController@deleteChatTask');
    Route::post('/updateChatTask', 'ChatController@updateChatTask');
    Route::get('/getChatTaskSearch', 'ChatController@searchChatTask');
    Route::get('/chatFileDownload', 'ChatController@chatFileDownload');
    Route::get('/searchChatPeopleAndGroup', 'ChatController@searchChatPeopleAndGroup');
    Route::get('/downloadFile', 'ChatController@downloadFile');
    Route::post('/setGroup', 'ChatController@setGroup');
    Route::post('/delChatList', 'ChatController@delChatList');
    Route::post('/topChatList', 'ChatController@topChatList');
    Route::post('/setChatList', 'ChatController@setChatList');
    Route::get('/getIsFriend', 'ChatController@getIsFriend');
    Route::get('/getGroupUser', 'ChatController@getGroupUser');
    Route::get('/getGroupFriend', 'ChatController@getGroupFriend');
    Route::get('/fetchChatUserList', 'ChatController@fetchChatUserList');
    Route::post('/setFriendToGroup', 'ChatController@setFriendToGroup');
    Route::post('/delInGroup', 'ChatController@delInGroup');
    Route::post('/addUserTask', 'ChatController@addUserTask');
    Route::post('/updateUserTask', 'ChatController@updateUserTask');
    Route::post('/chatMessageSearch', 'ChatController@chatMessageSearch');
    Route::post('/clearGroup', 'ChatController@clearGroup');
    Route::post('/updateMessage', 'ChatController@updateMessage');
    Route::post('/createGroup', 'ChatController@createGroup');
    Route::post('/setVideoPic', 'ChatController@setVideoPic');
    Route::post('/setVideoPdf', 'ChatController@setVideoPdf');
    Route::post('/getPdfPage', 'ChatController@getPdfPage');
    Route::post('/updateLastRead', 'ChatController@updateLastRead');
    Route::get('/getNewChatList', 'ChatController@getNewChatList');
    Route::post('/getFileSize', 'ChatController@getFileSize');
    Route::post('/editChatTask', 'ChatController@editChatTask');
    Route::post('/pushChatCall', 'ChatController@pushChatCall');
    Route::post('/getChatFileSize', 'ChatController@getChatFileSize');
    Route::post('/chatLike', 'ChatController@chatLike');
    Route::post('/getTask', 'ChatController@getTask');
    Route::post('/downloadBatchSelectedChatFile', 'ChatController@downloadBatchSelectedChatFile');

    Route::post('/getFileFromDoc', 'DocController@getFileFromDoc');
    Route::get('/getDocFileIcon/{fileType}', 'DocController@getDocFileIcon');
    Route::get('/getDocFileThumb/{nodeId}/{revNo}', 'DocController@getDocFileThumb');
    Route::post('/moveFileToChat', 'DocController@moveFileToChat');

    // SkyWayのAPIキー認証処理(TODO アプリのAPIとマージした後削除)
    Route::post('/skyway/authenticate', 'SkywayController@authenticate');
    //招待協力会社
    Route::post('/getInviteList', 'InviteController@getList');
    Route::post('/delInvite', 'InviteController@delInvite');
    Route::post('/getInviteSearch', 'InviteController@search');
    Route::post('/sendEmail', 'InviteController@sendEmail');
    Route::post('/checkEmail', 'InviteController@checkEmail');
    Route::post('/inviteChatLink', 'InviteController@inviteChatLink');
    Route::post('/getInviteSearchSort', 'InviteController@detailSearch');
    // 仲間
    Route::post('/getFriendList', 'FriendController@getList');
    Route::post('/searchFriend', 'FriendController@searchFriend');
    Route::post('/sendFriendEmail', 'FriendController@sendEmail');
    Route::post('/delFriend', 'FriendController@delFriend');
    Route::post('/getFriendSearch', 'FriendController@search');
    Route::post('/checkFriendEmail', 'FriendController@checkEmail');
    Route::post('/getFriendDetailInformation', 'FriendController@getFriendDetailInformation');
    Route::post('/friendChatLink', 'FriendController@friendChatLink');
    Route::post('/getFriendSearchSort', 'FriendController@detailSearch');
    //ドキュメント
    Route::post('/getDocumentList', 'DocumentController@getDocumentList');
    Route::get('/getDocumentPdf', 'InformController@exportPdf');
     //contact
    Route::get('/getContactList', 'ContactController@getContactList');
    Route::post('/contactSearch', 'ContactController@contactSearch');
    Route::post('/contactSort', 'ContactController@contactSort');
    Route::post('/contactChatLink', 'ContactController@contactChatLink');
    //company
    Route::post('/setCompany', 'CompanyController@save');
    Route::post('/getCompanyObject', 'CompanyController@getCompany');
    Route::get('/getCompanyObject', 'CompanyController@getCompany');
    Route::post('/editCompany', 'CompanyController@edit');
    Route::get('/company', 'CompanyController@index');
    Route::post('/deleteCompany', 'CompanyController@deleteCompany');
    //Report
    Route::get('/report', 'ReportController@reportList');
    Route::get('/reportSearch', 'ReportController@reportSearch');
    Route::get('/report/detail', 'ReportController@reportDetail');
    Route::post('/deleteReport', 'ReportController@deteteReport');
    Route::post('/updateReport', 'ReportController@updateReport');
    Route::get('/getReportFile', 'ReportController@getReportFile');
    Route::get('/getReoprtProjectList', 'ReportController@proList');
    Route::post('/getReportPdf', 'ReportController@exportPdf');
    Route::get('/getProjectFiles', 'ReportController@getImageList');
    Route::get('/createReport', 'ReportController@createReportForm');
    Route::get('/getPDF', 'ReportController@getPDF');
    Route::post('/uploadReportImage', 'ReportController@uploadReportImage');
});

Route::middleware('auth','authentication')->namespace('Admin')->group(function () {
    //職人管理
    Route::post('/getWorkerList', 'WorkerController@getList');
    Route::post('/workerDetail', 'WorkerController@workerDetail');
    Route::post('/workerBlock', 'WorkerController@block');
    Route::get('/workerCsv', 'WorkerController@workerCsv');
    //契約者
    Route::get('/getContractList', 'ContractController@getList');
    Route::get('/getContractDetail', 'ContractController@getContractDetail');
    Route::post('/getContractOffice', 'ContractController@getOffice');
    Route::post('/ContractHistory', 'ContractController@history');
    Route::post('/projectCount', 'ContractController@projectCount');
    Route::post('/contractContain', 'ContractController@contractContain');
    Route::post('/enterpriseData', 'ContractController@enterpriseData');
    Route::post('/updateContractDetail', 'ContractController@updateContractDetail');
    Route::post('/deleteContract', 'ContractController@deleteContract');
    Route::get('/getBrowse', 'ContractController@getBrowse');
    Route::post('/getOperatorUsers', 'ContractController@getOperatorUsers');
    Route::post('/searchContract', 'ContractController@searchContract');
    Route::post('/addContractFriend', 'ContractController@addContractFriend');
    Route::post('/addContractEnterprise', 'ContractController@addContractEnterprise');
    Route::post('/contractAccount', 'ContractController@contractAccount');
    Route::get('/contractCsv', 'ContractController@contractCsv');
    //お知らせ
    Route::post('/getNoticeList', 'NoticeController@getNoticeList');
    Route::post('/createNotice', 'NoticeController@createNotice');
    Route::post('/getNoticeDetail', 'NoticeController@getNoticeDetail');
    Route::post('/editNotice', 'NoticeController@editNotice');
    Route::post('/searchNotice', 'NoticeController@searchNotice');
});
