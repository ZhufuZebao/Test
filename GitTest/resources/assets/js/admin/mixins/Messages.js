const _common = {
  'confirm': {
    'warning': {
      'jump': 'ほかのページに移動すると、入力中のデータは保存されません。よろしいですか？',
      'refresh': '状態を変更し,データをロードするかどうか?'
    },
    'delete': {
      'func': function (key) {
        return '{0}を削除してよろしいですか？'.replace('{0}', key);
      },
      'message': '削除してよろしいですか？',
      'project': function (key) {
        return '本当に「{0}」を削除しますか？<br/>'.replace('{0}', key)
            + 'この案件のチャットグループ、ドキュメント管理内のデータも全て削除されます。<br/>'
            + '削除されたデータは二度と元に戻すことはできません';
      },
      'group': function (key) {
        return '本当に「{0}」を削除しますか？<br/>'.replace('{0}', key)
            + '自分だけではなく、メンバー全員のチャット一覧から削除されます。<br/>'
            + 'すべてのメッセージ、タスク、ファイル一覧のデータが削除されます。<br/>'
            + '削除されたデータは二度と元に戻すことはできません';
      },
      'account': 'このアカウントを削除しますか？',
      'scheduleWeek': 'このスケジュールを削除しますか？',
      'task': 'このタスクを削除しますか？',
    }
  },
  "query": {
    'result': function (key) {
      return '{0}件を見つけました'.replace('{0}', key);
    },
  },
  "warning": {
    'noPublic': 'プロフィール非公開',
    'noAgree': '未承認のため非表示',
    'agree': '上記の規約をチェックしてください',
    'auth': '認証キーは間違いました、ご確認してください',
    'groupName': 'グループ名を入力してください',
    'chargePerson': '担当者を選択してください',
    'noParticipant': '参加者を選択してください',
    'removeUserFromGroup': 'さんが退席されました。'
  },
  'success': {
    'insert': '登録しました',
    'update': '変更しました',
    'delete': '削除しました',
    'password': 'パスワードが変更されました',
  },
  'error': {
    'insert': '登録中にエラーが発生しました',
    'update': '変更中にエラーが発生しました',
    'delete': '削除中にエラーが発生しました',
    'system': 'システムエラーが発生しました',
    'office': '少なくとも1つの事業所情報が必要です',
    'officeBind': '事業所は案件情報に紐付けられています',
    'customerBind': '施主は案件情報に紐付けられています',
    'formValidFail': 'フォーム記入フィールドに誤りがあります',
    'permission': '権限ありません',
    'contract': '登録できるアカウント数を超えています',
    'storage': '利用可能のストレージ容量を超えています',
    'repeatWeekErrorMsg': '繰り返し曜日を選択してください',
    'customerList': '施主の取得に失敗しました',
    'customerCreate': '施主の登録に失敗しました',
    'customerEdit': '施主変更の保存に失敗しました',
    'already':'このメールアドレスは既に招待されています',
    'passwordReset':'パスワードの変更に失敗しました',
    'profileEdit':'プロフィールの変更保存に失敗しました',
    'accountList':'アカウントの取得に失敗しました',
    'accountDelete':'アカウントの削除に失敗しました',
    'accountCreate':'アカウントの追加に失敗しました',
    'switchIdentity':'アカウントの区分の変更に失敗しました',
    'projectList':'案件の取得に失敗しました',
    'participantsAdd':'共有者の追加に失敗しました',
    'participantsList':'共有者の取得に失敗しました',
    'projectChatMessage':'グループチャットの取得に失敗しました',
    'customerDetail':'施主情報の取得に失敗しました',
    'profileDetail':'プロフィールの取得失敗',
    'projectCreate':'案件の登録に失敗しました',
    'projectEdit':'案件変更の保存に失敗しました',
    'projectDetail':'案件詳細の取得に失敗しました',
    'projectShow':'案件情報の取得に失敗しました',
    'friendList':'職人ユーザの取得に失敗しました',
    'friendDelete':'職人ユーザの削除に失敗しました',
    'friendCreate':'職人ユーザの招待に失敗しました',
    'inviteCreate':'協力会社ユーザの招待に失敗しました',
    'inviteDelete':'協力会社ユーザの削除に失敗しました',
    'inviteList':'協力会社ユーザの取得に失敗しました',
    'contactList':'社内ユーザの取得に失敗しました',
    'scheduleList':'予定の取得に失敗しました',
    'scheduleEdit':'スケジュールの保存に失敗しました',
    'scheduleSelectAccount':'参加者の取得に失敗しました',
    'chatContactList':'連絡先の取得に失敗しました',
    'chatGroupCreate':'グループチャットの作成に失敗しました',
    'chatTaskList':'タスクを取得するのに失敗しました',
    'chatCompletedTask':'タスクを完了への変更に失敗しました',
    'chatNotCompletedTask':'タスクを未完了への変更に失敗しました',
    'chatTaskDelete':'タスクの削除に失敗しました',
    'chatTaskEdit':'タスクの変更に失敗しました',
    'chatList':'メッセージの取得に失敗しました',
    'chatMsgSend':'メッセージの送信に失敗しました',
    'chatMsgDelete':'メッセージの削除に失敗しました',
    'mediaDevice':'音声・ビデオ通話ができない',
    'wrongFormat':'フォーマットエラー:はサポートJPG/JPEG,GIF,PNG',
    'wrongFormatPdf':'フォーマットエラー:はサポートPDF',
    'chatListTaskCreate':'タスクの追加に失敗しました',
    'scheduleCreate':'スケジュールが既に変更されました。前の画面に戻ります。',
    'contractList':'契約者一覧の取得に失敗しました',
    'workerList':'職人一覧の取得に失敗しました',
    'workerDetail':'職人詳細の取得に失敗しました',
    'workerEdit':'職人の変更に失敗しました',
    'operatorList':'運営者一覧の取得に失敗しました',
  },
  'button': {
    'ok': 'はい',
    'cancel': 'いいえ',
  }
};

export default {
  data() {
    return {
      commonMessage: _common,
    }
  }
}
