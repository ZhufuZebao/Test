<template>
  <!--container-->
  <div class="container">
    <h1 class="heading">Room example</h1>
    <div class="room">
      <div style="border: 1px solid indianred">
        <video id="my-video" autoplay playsinline></video>
      </div>
      <input v-model="roomName" placeholder="Room Name">
      <button @click="closeCamera">closeCamera</button>
      <button @click="getUserMedia">openCamera</button>
      <button @click="joinRoom">Join</button>
      <button @click="leaveRoom">Leave</button>
      <button @click="enableMic">enableMic</button>
      <button @click="disableMic">disableMic</button>
      <button @click="enableVideo">enableVideo</button>
      <button @click="disableVideo">disableVideo</button>
      <div class="remote-streams" id="remote-videos" style="border: 1px solid royalblue;">
      </div>
    </div>
    <!--<el-select v-model="selectedAudio" placeholder="Select Audio" @change="onChange">-->
    <!--<el-option v-for="audio in audios" :key="audio.value"-->
    <!--:label="audio.text" :value="audio.value">-->
    <!--</el-option>-->
    <!--</el-select>-->
    <!--<el-select v-model="selectedVideo" placeholder="Select Video" @change="onChange">-->
    <!--<el-option v-for="video in videos" :key="video.value"-->
    <!--:label="video.text" :value="video.value">-->
    <!--</el-option>-->
    <!--</el-select>-->
    <div v-html="messages" style="border: solid 1px red;height: 50px;overflow-y: auto">&nbsp;</div>
  </div>
  <!--/container-->
</template>

<script>
  import Peer from 'skyway-js'
  import { getCookieValue } from '../../util'

  export default {
    name: "VideoChatRoom",
    data: function () {
      return {
        // 接続しているroomオブジェクト
        room: null,
        roomName: 'mesh_video_group_1',
        // SkyWayのpeer
        peer: null,
        // 自分のID
        peerId: '',
        // ローカル画面
        localStream: null,
        messages: '',
        audios: [],
        videos: [],
        // selectedAudio: '',
        // selectedVideo: '',
      }
    },
    methods: {
      joinRoom: function () {
        if (!this.peer.open) {
          return;
        }
        const remoteVideos = document.getElementById('remote-videos');
        this.room = this.peer.joinRoom(this.roomName, {
          mode: 'mesh',
          stream: this.localStream,
        });
        this.room.once('open', () => {
          this.messages += '=== You joined ===<br/>';
        });
        this.room.on('peerJoin', peerId => {
          this.messages += `=== ${peerId} joined ===<br/>`;
        });

        // Render remote stream for new peer join in the room
        this.room.on('stream', async stream => {
          this.messages += '=== stream joined ===<br/>';
          const newVideo = document.createElement('video');
          newVideo.srcObject = stream;
          newVideo.playsInline = true;
          newVideo.autoplay = true;
          // mark peerId to find it later at peerLeave event
          newVideo.id = stream.peerId;

          remoteVideos.append(newVideo);
          // await newVideo.play().catch(console.error);
        });

        // for closing room members
        this.room.on('peerLeave', peerId => {
          const remoteVideo = document.getElementById(peerId);
          remoteVideo.srcObject.getTracks().forEach(track => track.stop());
          remoteVideo.srcObject = null;
          remoteVideo.remove();

          this.messages += `=== ${peerId} left ===<br/>`;
        });

        // for closing myself
        this.room.once('close', () => {
          this.messages += `== You left ===<br/>`;
          Array.from(remoteVideos.children).forEach(remoteVideo => {
            remoteVideo.srcObject.getTracks().forEach(track => track.stop());
            remoteVideo.srcObject = null;
            remoteVideo.remove();
          });
        });
      },
      leaveRoom() {
        if (this.room) {
          this.room.close();
          this.room = null;
        }
      },
      closeCamera() {
        let localVideo = document.getElementById('my-video');
        if (localVideo.srcObject) {
          localVideo.srcObject.getTracks().forEach(track => track.stop());
          localVideo.srcObject = null;
          this.localStream = null;
        }
      },
      getUserMedia() {
        Vue.nextTick(() => {
          // const constraints = {
          //   audio: this.selectedAudio ? {deviceId: {exact: this.selectedAudio}} : false,
          //   video: this.selectedVideo ? {deviceId: {exact: this.selectedVideo}} : false,
          // };
          const constraints = {
            audio: true,
            video: true,
          };
          let localVideo = document.getElementById('my-video');
          if (!localVideo.srcObject) {
            navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
              localVideo.srcObject = stream;
              this.localStream = stream;
            }).catch((err) => {
              this.messages += err + '<br/>';
            })
          }
        })
      },
      enableMic() {
        if (this.localStream) {
          this.localStream.getAudioTracks().forEach(function (track) {
            track.enabled = true;
          });
        }
      },
      disableMic() {
        if (this.localStream) {
          this.localStream.getAudioTracks().forEach(function (track) {
            track.enabled = false;
          });
        }
      },
      enableVideo() {
        if (this.localStream) {
          this.localStream.getVideoTracks().forEach(function (track) {
            track.enabled = true;
          });
        }
      },
      disableVideo() {
        if (this.localStream) {
          this.localStream.getVideoTracks().forEach(function (track) {
            track.enabled = false;
          });
        }
      },
      getUuid() {
        let len = 32;
        let radix = 16;
        let chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
        let uuid = [], i;
        radix = radix || chars.length;
        if (len) {
          for (i = 0; i < len; i++) uuid[i] = chars[0 | Math.random() * radix];
        } else {
          var r;
          uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
          uuid[14] = '4';
          for (i = 0; i < 36; i++) {
            if (!uuid[i]) {
              r = 0 | Math.random() * 16;
              uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r];
            }
          }
        }
        return uuid.join('');
      }
    },
    created() {
      this.getUserMedia();
      let peerId = this.getUuid();
      let data = {peerId: peerId, sessionToken: getCookieValue('XSRF-TOKEN')};
      axios.post('/api/skyway/authenticate', data).then((res) => {
        this.peer = new Peer(peerId, {
          key: process.env.MIX_APP_SKYWAY_KEY,
          credential: res.data
        });
        // シグナリングサーバーに正常に接続できた時
        this.peer.on('open', () => {
          this.peerId = this.peer.id;
        });
        // Peerに対する全ての接続を終了した時
        this.peer.on('close', () => console.log('peer close'));

        this.peer.on('error', err => console.log(err));
      }).catch(error => {
        this.$alert(error, {showClose: false});
      });

      // コンピューターの設備を取得
      navigator.mediaDevices.enumerateDevices().then((deviceInfos) => {
        deviceInfos.forEach((deviceInfo) => {
          if (deviceInfo.kind === 'audioinput') {
            this.audios.push({
              text: deviceInfo.label || `Microphone ${this.audios.length + 1}`,
              value: deviceInfo.deviceId,
            })
          } else if (deviceInfo.kind === 'videoinput') {
            this.videos.push({
              text: deviceInfo.label || `Camera  ${this.videos.length - 1}`,
              value: deviceInfo.deviceId,
            })
          }
        })
      });
    },
    destroyed() {
      this.leaveRoom();
      if (this.peer) {
        this.peer.close();
      }
    }
  }
</script>
<style scoped>
  #my-video {
    width: 300px;
  }
  #remote-videos video {
    width: 200px;
  }
</style>