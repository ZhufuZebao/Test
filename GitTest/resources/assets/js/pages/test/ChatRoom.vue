<template>
  <!--container-->
  <div class="container">
    <table>
      <tr>
        <td colspan="2">
          <div style="border: solid 1px black;height: 200px;width: 100%" v-html="chatHistory1"></div>
        </td>
      </tr>
      <tr>
        <td>
          user:<input v-model="user_id">
        </td>
        <td>
          name:<input v-model="user_name">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          GroupId:<input v-model="group_id">
          <button @click="join">Join Room</button>
          <button @click="leave">Leave Room</button>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <textarea cols="50" v-model="message"></textarea>
          <button @click="send">send message</button>
        </td>
      </tr>
    </table>
  </div>
  <!--/container-->
</template>

<script>
  import SocketIO from 'socket.io-client';
  export default {
    name: "ChatRoom",
    data: function () {
      return {
        socket : null,
        chatHistory1: '',
        user_id: '1',
        user_name: 'song',
        group_id: '1',
        message: '',
      }
    },
    methods: {
      join: function () {
        // join する時は、user{room,id,name} だけでいいです。
        let user = {'room': 'group_' + this.group_id, 'id': this.user_id, 'name': this.user_name};
        this.socket.emit("join", user);
      },
      leave: function () {
        // join する時は、user{room,id,name} だけでいいです。
        let user = {'room': 'group_' + this.group_id, 'id': this.user_id, 'name': this.user_name};
        this.socket.emit("leave", user.room);
      },
      send: function () {
        // message{
        //   "msg" ,  メッセージ
        //   "nickname" ,  ユーザー名
        //   "id" ,  ユーザーID
        //   "mid" ,  メッセージ変更の時はメッセージID
        //   "file" ,  添付ファイル名
        //   "timeStamp" ,  タイムスタンプ Long
        //   "maxid" メッセージの最大ID
        // }
        let msg = {msg: this.message, gid: this.group_id, nickname: this.user_name, id: this.user_id};
        this.socket.emit("chat.send.message", msg);
        this.message = '';
      },
    },
    created() {
      const socketUri = process.env.MIX_SOCKETIO_SERVER;
      const attempts = process.env.MIX_SOCKETIO_ATTEMPTS;
      if (socketUri) {
        this.socket = SocketIO(socketUri, {
          'reconnection': true,
          'reconnectionDelay': 1000,
          'reconnectionDelayMax': 5000,
          'reconnectionAttempts': attempts ? attempts : 5
        });

        // this.socket.removeListener('chat.message');
        // this.socket.removeListener('chat.users');
        let $this = this;
        this.socket.on('chat.message', function (chatMsg) {
          let msgObj = JSON.parse(chatMsg);
          $this.chatHistory1 += '<br/>nickname:' + msgObj.nickname + "====>:" + msgObj.msg;
          console.log(msgObj);
        });
        this.socket.on('chat.users', function (nicknames) {
          console.log(nicknames);
        })
      }
    },
    destroyed() {
      if (this.socket) {
        this.socket.close();
      }
    }
  }
</script>