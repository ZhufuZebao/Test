<template>
  <div class="chat-list-tabs">
    <el-tabs :tab-position="tabPosition" v-model="linkGroupCurrent" class="chat-list-tabs-left"
     v-infinite-scroll='loadContractScroll' infinite-scroll-distance="500" infinite-scroll-find="child" infinite-scroll-disabled="contactScrollDisble">
      <el-tab-pane v-for="u in chatPerson" :key="'chatPerson-'+u.id"
                   :name="'linkGroup-'+u.group_id">
        <span slot="label" @click.prevent="fetchMember(u,1)">
         <el-popover placement="top" trigger="manual" v-model="u.id===current && visibled">
           <el-row slot="reference">
             <div class="chat-group" v-if="u.group.kind">
               <img :src="'file/users/'+u.account.file" alt="" v-if="u.account && u.account.file!== 'null'&& u.account.file!== null" class="pro-photo">
               <img src="images/icon-chatperson.png" alt="" v-else class="pro-photo">
               <span class="chat-group-name" v-if="u.account">{{u.account.name}}</span>
               <span class="chat-msg-num" v-if="uc.group_id===u.group_id && uc.num>0" v-for="uc in UnreadCount"
                     :key="'UnreadCount'+uc.id+u.group.name">{{uc.num}}</span>
             </div>
             <div class="chat-group" v-else>
             <img :src="'file/groups/'+u.group.file" alt="" v-if="u.group && u.group.file && u.group.file !== 'null'&& u.group.file!== null" class="pro-photo">
               <img src="images/icon-chatgroup.png" alt="" v-else class="pro-photo">
               <span class="chat-group-name" v-if="u.group.parent_id">{{u.group.name}}（{{u.parent.name}}）</span>
               <span class="chat-group-name" v-else>{{u.group.name}}</span>
               <span class="chat-msg-num" v-if="uc.group_id===u.group_id && uc.num>0" v-for="uc in UnreadCount"
                     :key="'UnreadCount-'+uc.id+uc.name">
                 {{uc.num}}
               </span>
             </div>
           </el-row>
          </el-popover>

        </span>
      </el-tab-pane>

      <section class="chatcontent" v-on:mouseleave="mouseleave" v-if="showChatFlag">
        <ul class="rowfrist">
          <div class="talk-user-item-wrap clearfix" style="align-items: center;display: flex;"
          :class="[{'showjoinms':showJoinMsg && (showAudioType || showVideoType)}]">
            <img style="cursor: pointer;" class="talk-photo" src="images/icon-chatperson.png"
                 v-if="(!member.photo || member.photo===null||member.photo===''||member.photo==='null')&&member.kind===1" @click="showPersonDetail">
            <img style="cursor: pointer;" class="talk-photo" src="images/icon-chatgroup.png"
                 v-else-if="(!member.photo || member.photo===null||member.photo===''||member.photo==='null')&&member.kind===0" @click="showPersonDetail">
            <img style="cursor: pointer;" class="talk-photo" v-bind:src="'file/users/' + member.photo" v-else-if="member.kind===1" @click="showPersonDetail">
            <img style="cursor: pointer;" class="talk-photo" v-bind:src="'file/groups/' + member.photo" v-else-if="member.kind===0" @click="showPersonDetail">
            <div style="cursor: pointer;" class="talk-name" @click="showPersonDetail">{{member.userName}}</div>
            <el-popover placement="right-start" popper-class="popover-location" width="300" trigger="click"
                        :ref="'editGroup-'">
              <div class="popover-div">
                <li class="popover-li">
                  <el-row>
                    <img v-if="imageUrl" v-bind:src="imageUrl">
                    <img v-else-if="this.member.photo && this.member.kind===0&&this.member.photo!=='null'&&this.member.photo!==null"
                         v-bind:src="'file/groups/' + member.photo">
                    <img v-else src="images/icon-chatgroup.png">
                    <div class="el-form-item__error" v-if="validateMessage">
                      {{validateMessage}}
                    </div>
                    <el-upload
                            action="http"
                            class="avatar-uploader"
                            :auto-upload="false"
                            :show-file-list="false"
                            :on-change="imgChange">
                      <el-button>グループ写真を選択</el-button>
                    </el-upload>
                  </el-row>
                </li>

                <el-form ref="editForm" :model="editGroupName" :rules="chatGroup">
                  <el-form-item prop="inputPopover" class="chatListadd">
                    <el-input v-model="editGroupName.inputPopover" placeholder="名前" class="popoverto-input"
                              maxlength="191" onkeypress="if (event.keyCode === 13) return false;">
                    </el-input>
                  </el-form-item>
                </el-form>
                <li class="popover-li">
                  <el-button class="group-creat" @click="saveGroup(member)"
                             v-if="!member.kind &&  member.userClearFlag">保存
                  </el-button>
                </li>
              </div>
              <div class="talk-name edit-photo chatlist-newicon" slot="reference" @click="editGroup">
                <img src="images/edit@2x.png" alt="" v-if="member.kind ===0 && member.userClearFlag"/>
              </div>
            </el-popover>
            <div class="talk-contents talk-contents-address">{{member.enterpriseName}}</div>
            <div class="talk-contents" v-if="!member.kind">{{groupUsers.length}}人</div>
          </div>
          <p>
            <span v-if="showJoinMsg && !member.kind">
              <span v-if="showVideoType">ビデオ通話を開始しました。</span>
              <span v-else-if="showAudioType">音声通話を開始しました。</span>
              <el-button type="text" @click.prevent="joinVideoAction()" style="margin-left: 0;">{{joinMsg}}</el-button>
            </span>
            <el-button type="text">
              <img src="images/icon-file2.png" @click.prevent="showFiles">
            </el-button>
            <el-button type="text">
              <img src="images/icon-task.png" @click.prevent="showTask('')">
            </el-button>

            <el-popover placement="left" v-model="showVideoTabFlag" class="chat-model" :class="[isTransverse?'is-transverse':'no-transverse']">
              <div class="chat-popover" :class="[isFullscreen?'is-fullscreen':'no-fullscreen']">
                <div class="popover-header" v-if="!member.kind">{{member.userName+' '+member.enterpriseName+'('+(videoArr.length+1)+'/'+groupUsers.length+')'}}</div>
                <div class="popover-header" v-else>{{member.userName+' '+member.enterpriseName}}</div>
                <div class="popover-full-icon" @click.prevent="fullScreenModal"><img src="images/chat592.png"/></div>
                <div class="popover-transverse-icon" @click.prevent="transverseModal" v-if="!showVideo"><i class="el-icon-refresh-right"></i></div>
                <div v-if="showVideoTabFlag && !member.kind">
                  <el-popover popper-class="model-joiner"
                              placement="bottom"
                              width="20"
                              trigger="click">
                    <div slot="reference" class="chat-model-joiner"><img src="images/icon_menu.png"></div>
                    <div>
                      <li class="chat-model-joiner-li" v-for="u in joinerList(videoArr)">
                        <div v-if="u.user && u.user.file"><img
                                :src="'file/users/'+u.user.file"><span>{{u.user.name}}</span></div>
                        <div v-else><img src="images/icon-chatperson.png"><span>{{u.user.name}}</span></div>
                      </li>
                      <!--person data add-->
                      <li class="chat-model-joiner-li" v-if="mineData">
                        <div v-if="mineData && mineData.file"><img :src="'file/users/'+mineData.file"><span>{{mineData.name}}</span></div>
                        <div v-else><img src="images/icon-chatperson.png"><span>{{mineData.name}}</span></div>
                      </li>
                    </div>
                  </el-popover>
                </div>

                <div class="popover-tabs-content">
                  <div class="popover-content-img">
                    <div v-show="showVideoFlag">
                      <div class="addvideo" :data="datadiv" id="remote-videos" v-show="videoArr.length>1"></div>
                      <video id="onlyOneVideo" autoplay playsinline class="imghost" v-show="videoArr.length<=1"></video>
                      <div class="imgguests">
                        <video id="my-video" autoplay playsinline class="imghost"></video>
                      </div>
                    </div>
                    <div v-show="showPic" class="divPic" ref="divPic" id="divPic">
                      <img ref="videoImage" id="videoImage" style="height: 100%;width: 100%" :src="this.videoPicName"
                           @click="drawCircle"/>
                      <div :class="[isdrawCircle?'drawCircle':'no-drawCircle']"
                           v-bind:style="{top:topCircle + 'px', left: leftCircle + 'px' }"></div>
                    </div>
                  </div>
                  <div class="popover-content-button">
                    <el-button class="icon-phone" @click="leaveRoom(2)"></el-button>
                    <el-button :class="[showSound?'icon-microphone':'changed-icon-microphone']"
                               @click.prevent="closeSoundModal" :disabled="soundIsDisabled"></el-button>
                    <el-button :class="[showVideo?'icon-video-camera':'changed-icon-video-camera']"
                               :disabled="videoIsDisabled" @click.prevent="closeVideoModal"></el-button>
                    <el-upload
                            :disabled="picModalIsDisabled"
                            ref="videoPic"
                            class="upload-demo"
                            action="http"
                            :on-change="uploadVideoPic"
                            :on-remove="videoPicRemove"
                            :auto-upload="false"
                            list-type="picture"
                            :show-file-list="false"
                            accept=".jpg,.jpeg,.png,.gif">
                      <el-button class="icon-picture-outline" :disabled="picModalIsDisabled"></el-button>
                    </el-upload>

                    <el-upload
                            :disabled="picModalIsDisabled"
                            ref="videoPic"
                            class="upload-demo-file"
                            action="http"
                            :on-change="uploadVideoPdf"
                            :on-remove="videoPicRemove"
                            :auto-upload="false"
                            list-type="picture"
                            :show-file-list="false"
                            accept=".pdf">
                      <el-button circle icon="el-icon-tickets" style="font-size: 28px;"
                                 :disabled="picModalIsDisabled"></el-button>
                    </el-upload>

                    <el-button circle class="icon-download" icon="el-icon-download" :disabled="hasShowFile" @click.prevent="downloadChatFileOp"></el-button>

                  </div>
                  <div class="chatnextpage" v-if="pdfPageTotal > 0">
                    <el-button icon="el-icon-caret-left" v-if="pdfPagePrev"
                               @click="changePdfPage(pdfPageNow - 1)"></el-button>
                    <el-button icon="el-icon-caret-left" v-else disabled></el-button>
                    <el-button icon="el-icon-caret-right" v-if="pdfPageNext"
                               @click="changePdfPage(pdfPageNow + 1)"></el-button>
                    <el-button icon="el-icon-caret-right" v-else disabled></el-button>
                  </div>
                </div>
              </div>
            </el-popover>
            <el-button type="text" v-if="!showVideoTabFlag && !showVideoType">
              <img src="images/icon-phone.png" @click.prevent="showVideoTab(1,1)" v-if="showAudioType">
              <img src="images/icon-phone.png" @click.prevent="showVideoTab(1)" v-else>
            </el-button>
            <el-button type="text" v-else disabled>
              <img src="images/icon-phone.png">
            </el-button>
            <el-button type="text" v-if="!showVideoTabFlag && !showAudioType">
              <img src="images/icon-video2.png" @click.prevent="showVideoTab(2,1)" v-if="showVideoType">
              <img src="images/icon-video2.png" @click.prevent="showVideoTab(2)" v-else>
            </el-button>
            <el-button type="text" v-else disabled>
              <img src="images/icon-video2.png">
            </el-button>

            <el-popover popper-class="group-popover" v-model="groupMemberShowFlag">
              <div class="group-person">
                <div class="group-person-header">{{member.enterpriseName}}</div>
                <el-popover v-model="addToGroupFlag">
                  <div>
                    <el-tabs type="border-card" class="popoverto-tabs">
                      <el-input class="popoverto-input" placeholder="名前で検索"
                                suffix-icon="searchForm-submit" v-model="friendSearchWord"
                                @change="searchFriend(2)"></el-input>
                      <el-tab-pane class="tab-enterprise" label="社内">
                        <el-checkbox-group v-model="enterpriseArr">
                          <li v-for="user in friendArr.enterpriseArr" :key="'enterpriseArr-'+user.id"
                              v-if="user.id && user">
                            <el-checkbox :label="user">
                              <img src="images/icon-chatperson.png" alt=""
                                   v-if="!user.file || user.file===null||user.file===''">
                              <img :src="'file/users/'+ user.file" alt="" v-else-if="member.groupId!==''">
                              {{user.name}}
                            </el-checkbox>
                          </li>
                        </el-checkbox-group>
                      </el-tab-pane>
                      <el-tab-pane class="tab-enterprise" label="協力会社">
                        <el-checkbox-group v-model="parterArr">
                          <ul>
                            <li v-for="user in friendArr.parterArr" :key="'parterArr-'+user.id" v-if="user.id && user">
                              <el-checkbox :label="user">
                                <img src="images/icon-chatperson.png" alt=""
                                     v-if="!user.file || user.file===null||user.file===''">
                                <img :src="'file/users/'+ user.file" alt="" v-else-if="member.groupId!==''">
                                {{user.name}}
                              </el-checkbox>
                            </li>
                          </ul>
                        </el-checkbox-group>
                      </el-tab-pane>
                      <el-tab-pane class="tab-enterprise" label="職人">
                        <el-checkbox-group v-model="contactArr">
                          <ul>
                            <li v-for="user in friendArr.contactArr" :key="'contactArr-'+user.id"
                                v-if="user.id && user">
                              <el-checkbox :label="user">
                                <img src="images/icon-chatperson.png" alt=""
                                     v-if="!user.file || user.file===null||user.file===''">
                                <img :src="'file/users/'+ user.file" alt="" v-else-if="member.groupId!==''">
                                {{user.name}}
                              </el-checkbox>
                            </li>
                          </ul>
                        </el-checkbox-group>
                      </el-tab-pane>
                    </el-tabs>
                    <el-button @click.prevent="cancelAddToGroup">閉じる</el-button>
                    <el-button @click.prevent="addFriendToGroup()">追加する</el-button>
                  </div>
                </el-popover>
                <div class="group-person-add">
                  <el-button @click.prevent="searchFriend(1)" v-if="member.userClearFlag && adminArea">+</el-button>
                  <div>
                    <ul class="group-person-box" style="height:180px;overflow-x: auto">
                      <li v-for="(user,index) in groupUsers" :key="'groupUsers'+index">
                        <img src="images/icon-chatperson.png" alt=""
                             v-if="(user.user.file===null || user.user.file==='' || !user.user)">
                        <img :src="'file/users/' + user.user.file" alt="" v-else-if="member.groupId!=='' && user.user">
                        <span class="el-icon-error"
                              v-if="memberObj.owner_id!==user.user.id"
                              @click.prevent="delInGroup(user,index)"></span>
                        <div>{{user.user.name}}</div>
                      </li>
                    </ul>
                    <el-button @click="closeGroupPopover">閉じる</el-button>
                    <el-button v-if="member.userClearFlag && adminArea" @click="clearGroup">グループを解散</el-button>
                  </div>
                </div>
              </div>
            </el-popover>
            <el-button @click.prevent="showGroupUser(1)" class="chat-memeber-add"
                       v-if="member.userClearFlag && adminArea">+
            </el-button>
          </p>
        </ul>
        <ul class="rowsecond" id="chatLists" ref="chatLists" :style="{height: 'calc( 100% - 244px - ' + (updated_filecount)*36+'px)' }">
          <li v-for="(chatItem,index) in chatLists" v-on:mouseover="mouseover(index)" v-if="chatItem"
              :id="'li'+chatItem.id">
            <div v-if="chatItem.showDate" class="demo_line_03"><b></b>
              <span>{{chatItem.created_at.split(' ')[0].replace('-','年').replace('-','月')+'日'}}</span>
              <b></b>
            </div>
            <h4 style="align-items: center;display: flex;">
              <el-popover placement="left">
                <div>
                  <img src="images/icon-chatperson.png"
                       style="height: 40px;width: 40px;margin-right: 20px;border-radius: 100%;"
                       v-if="!chatItem.user.file || chatItem.user.file===null||chatItem.user.file===''">
                  <img v-bind:src="'file/users/' + chatItem.user.file"
                       style="height: 40px;width: 40px;margin-right: 20px;border-radius: 100%;"
                       v-else-if="member.groupId!==''">
                  <div>{{chatItem.user.name}}</div>
                  <div>{{chatItem.user.email}}</div>
                  <div v-if="chatItem.user.enterprise">{{chatItem.user.enterprise.name}}</div>
                  <div v-else-if="chatItem.user.coop_enterprise">{{chatItem.user.coop_enterprise.name}}</div>
                  <div v-else></div>
                  <el-button @click.prevent="toAddFriend(chatItem)"
                             v-if="!isFriend&&user.id!==chatItem.from_user_id&&chatItem.user.worker==='1'">仲間に追加
                  </el-button>
                  <el-button @click.prevent="chatToUser(chatItem)" v-else-if="user.id!==chatItem.from_user_id&&chatItem.del!=='delete'">
                    チャットする
                  </el-button>
                </div>
                <img class="chat-avatar" slot="reference" src="images/icon-chatperson.png"
                     v-if="chatItem.user && !chatItem.user.file">
                <img class="chat-avatar" slot="reference" v-bind:src="'file/users/' + chatItem.user.file"
                      v-else-if="member.groupId!==''">
              </el-popover>
              <div class="talk-name" style="margin-right: 20px">{{chatItem.user.name}}</div>
              <div class="talk-contents" style="margin-right: 20px" v-if="chatItem.user.enterprise">
                {{chatItem.user.enterprise.name}}
              </div>
              <div class="talk-contents" style="margin-right: 20px" v-else-if="chatItem.user.coop_enterprise">
                {{chatItem.user.coop_enterprise.name}}
              </div>
              <div class="talk-contents" style="margin-right: 20px" v-else></div>
              <!--add read status-->
              <div class="talk-contents-read" v-if="chatItem.readUsers && chatItem.readUsers.length > 0">
                <el-dropdown trigger="click" v-if="groupUsers.length > 2">
                  <span class="el-dropdown-link">
                    既読 {{chatItem.readUsers.length}}<i class="el-icon-caret-bottom el-icon--right"></i>
                  </span>
                  <el-dropdown-menu slot="dropdown"  class="talk-contents-read-dropdown">
                    <el-dropdown-item class="talk-contents-read"  v-for="(avatarItem,index) in chatItem.readUsers" :key=index>
                        <el-avatar  :src="'file/users/' + avatarItem.userFile"></el-avatar>
                        <span>{{avatarItem.userName}}</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
                <el-dropdown trigger="click" v-else>
                  <span class="el-dropdown-link">
                    既読
                  </span>
                  <el-dropdown-menu slot="dropdown"  class="talk-contents-read-dropdown">
                    <el-dropdown-item class="talk-contents-read"  v-for="(avatarItem,index) in chatItem.readUsers" :key=index>
                        <el-avatar  :src="'file/users/' + avatarItem.userFile"></el-avatar>
                        <span>{{avatarItem.userName}}</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
              </div>
              <!--add read status finished-->
              <!--add like start-->
              <div class="talk-contents-read chat-like">
                <el-dropdown trigger="click">
                  <span class="el-dropdown-link" v-if="chatItem.like && chatItem.like.length">
                    &nbsp;&nbsp;&nbsp;<img class="" src="images/icon-chatgood-gray.png"> {{chatItem.like.length}}<i class="el-icon-caret-bottom el-icon--right"></i>
                  </span>
                  <span class="el-dropdown-link" v-else>
                    &nbsp;&nbsp;&nbsp;<img class="" src="images/icon-chatgood-gray.png">0<i class="el-icon-caret-bottom el-icon--right"></i>
                  </span>
                  <el-dropdown-menu slot="dropdown"  class="talk-contents-read-dropdown">
                    <el-dropdown-item class="talk-contents-read"  v-for="(avatarItem,index) in chatItem.like" :key=index>
                      <el-avatar  :src="'file/users/' + avatarItem.file"></el-avatar>
                      <span>{{avatarItem.name}}</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
              </div>
              <!--add like end-->
              <div class="talk-contents">{{getChatTime(chatItem)}}</div>
            </h4>
            <p class="text-wrapper" v-html="showMessage(chatItem)"></p>
            <p class="text-wrapper" v-html="showImg(chatItem)" v-if="chatItem.file_name"></p>
            <div class="button-group" v-show="chatItem.isClicked">
              <el-button-group>
                <el-button type="primary" v-if="user.id!==chatItem.from_user_id"
                           @click="backMsg(chatItem)">
                  <img class="icon-groupbtn" src="images/icon-groupbtn1.png" alt="">
                  <span>返信</span>
                </el-button>
                <el-button @click="useMsg(chatItem)" type="primary">
                  <img class="icon-groupbtn" src="images/icon-groupbtn2.png" alt="">
                  <span>引用</span>
                </el-button>

                <el-button type="primary" @click.prevent="showTask(chatItem)">
                  <img class="icon-groupbtn" src="images/icon-groupbtn3.png" alt="">
                  <span>タスク</span>
                </el-button>
                <el-button type="primary" @click="updateMsg(chatItem,index)" v-if="user.id===chatItem.from_user_id">
                  <img class="icon-groupbtn" src="images/icon-groupbtn4.png" alt="">
                  <span>編集</span>
                </el-button>
                <el-button type="primary" v-if="user.id===chatItem.from_user_id"
                           @click="delMsg(chatItem,index)">
                  <img class="icon-groupbtn" src="images/icon-groupbtn5.png" alt="">
                  <span>削除</span>
                </el-button>
                <el-button type="primary" @click="forwardMsg(chatItem)" v-if="!member.project">
                  <img class="icon-groupbtn" src="images/icon-groupbtn6.png" alt="">
                  <span>転送</span>
                </el-button>
                <el-button type="primary" @click="like(chatItem)">
                  <img class="icon-groupbtn" src="images/icon-chatgood-white.png" alt="">
                  <span>いいね</span>
                </el-button>
              </el-button-group>
            </div>
          </li>
        </ul>
        <ul class="rowlast">
          <li class="chatcontentbuttom">
            <div class="chat-contants-wrap add-chat-contants-wrap">
              <div class="talk-item-wrap clearfix">
                <el-button type="primary" class="el-button--primary-left">

                  <el-popover placement="bottom" ref="emojiPopover">
                    <el-button slot="reference" type="primary">
                      <img src="images/icon-emjio.png" @click.prevent="showEmoji">
                    </el-button>
                    <div class="emoji" style="width: 450px">
                      <ul class="emoji-controller">
                        <li v-for="(pannel,index) in pannels"
                            :key="'pannel'+index"
                            @click="changeActive(index)"
                            :class="{'active': index === activeIndex}">
                          {{ pannel }}
                        </li>
                      </ul>
                      <ul  class="emoji-controller-no">
                        <li v-for="(emojiGroup, index) in emojis"
                            style="padding: 0"
                            :key="'emojiGroup'+index"
                            v-if="index === activeIndex">
                          <a href="javascript:"
                             v-for="(emoji, index) in emojiGroup"
                             :key="'emoji'+index" @click="selectItem(emoji)">
                              <span>
                                <img style="width: 80px;height: 80px;padding:5px"
                                     :src="'./images/chat/'+getEmojiPath(index)+'.png'">
                              </span>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </el-popover>

                  <el-popover placement="top" ref="showGroupUserTo">
                    <div>
                      <el-input class="popoverto-input" placeholder="検索"
                                suffix-icon="searchForm-submit" v-model="groupSearchWord"
                                @change="showGroupUser"></el-input>
                      <el-tabs type="border-card" class="popoverto-tabs">
                        <el-button @click="toAllUser" type="primary" class="toChoicebutton">すべて</el-button>
                        <div class="toChoice">
                          <p v-for="(groupUser,index) in groupUsers" class="center-style"
                             v-if="groupUser.user_id !== user.id"
                             @click.prevent="toUser(groupUser.user)"
                             :key="'groupUsers-'+groupUser.user.id+groupUser.user.name+index">
                            <img src="images/icon-chatperson.png"
                                 v-if="!groupUser.user.file || groupUser.user.file===null||groupUser.user.file===''"
                                 alt="" style="height: 30px">
                            <img :src="'file/users/' + groupUser.user.file" alt="" v-else-if="member.groupId!==''"
                                 style="height: 30px">
                            <span>{{groupUser.user.name}}</span>
                          </p>
                        </div>
                      </el-tabs>
                    </div>
                    <el-button slot="reference" type="primary">
                      <img src="images/icon-to.png" @click="showGroupUser">
                    </el-button>
                  </el-popover>

                  <el-button>
                    <el-upload
                            ref="uploadImg"
                            class="upload-demo"
                            action="http"
                            :before-upload="beforeAvatarUpload"
                            :on-change="handleChange"
                            :on-remove="handleRemove"
                            :auto-upload="false"
                            :file-list="fileList"
                            multiple
                            :limit="100"
                            list-type="text">
                      <img src="images/icon-postfile.png" alt="">
                    </el-upload>
                  </el-button>
                  <el-button @click="selectFilesFromDoc" type="primary" v-if="member.kind === 1"><img src="img/icon/doc.png" /></el-button>
                  Ctrl + Enterで送信
                </el-button>
                <el-button type="primary" class="el-button--primary-right" @click="sendMessage(1)">
                  送信
                </el-button>
              </div>

              <div class="talk-contents" :style="{marginTop: (updated_filecount)*36+'px'}">
                <!--#2243のため、v-model.lazyを利用 -->
                <textarea autocomplete="off"
                    id="msgTextareaChatList"
                    :autosize="{ minRows: 5, maxRows: 10}"
                    ref="message" class="el-textarea__inner"
                    style="resize: none; min-height: 117px; height: 117px;"
                    @keyup.ctrl.enter="sendMessage(0)"
                    v-model.lazy="sendMsg" maxlength="65535">
                </textarea>
              </div>
            </div>
          </li>
        </ul>
      </section>

      <el-popover placement="right-start" popper-class="popover-location" width="300" trigger="click"
                  :ref="'popover-' + current">
        <div class="popover-div">
          <li class="popover-li">
            <el-row>
              <img v-if="imageUrl" v-bind:src="imageUrl">
              <img v-else src="images/icon-chatgroup.png">
              <div class="el-form-item__error" v-if="validateMessage">
                {{validateMessage}}
              </div>
              <el-upload
                      action="http"
                      class="avatar-uploader"
                      :auto-upload="false"
                      :show-file-list="false"
                      :on-change="imgChange">
                <el-button>グループ写真を選択</el-button>
              </el-upload>
            </el-row>

          </li>
          <el-form ref="form" :model="chatSetGroup" :rules="chatGroup">
            <el-form-item prop="inputPopover" class="chatListadd">
              <el-input v-model="chatSetGroup.inputPopover" placeholder="名前" class="popoverto-input"
                        maxlength="191" onkeypress="if (event.keyCode === 13) return false;">
              </el-input>
            </el-form-item>
          </el-form>
          <li class="popover-li">メンバーを選択</li>
          <el-tabs type="border-card" class="popoverto-tabs">
            <el-input class="popoverto-input" placeholder="名前で検索" v-model="searchGroupPerson"
                      suffix-icon="searchForm-submit"></el-input>
            <el-tab-pane label="社内">
              <div class="popover-checkbox" v-if="searchGroupPerson">
                <el-checkbox-group v-model="checked.checkGroupPerson">
                  <li v-for="u in searchEnterpriseRes" :key="'searchEnterprise-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
              <div class="popover-checkbox" v-else>
                <el-checkbox-group v-model="checked.checkGroupPerson">
                  <li v-for="u in searchPerson.searchEnterprise" :key="'searchEnterprise-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
            </el-tab-pane>
            <el-tab-pane label="協力会社">
              <div class="popover-checkbox" v-if="searchGroupPerson">
                <el-checkbox-group v-model="checked.checkParticipant">
                  <li v-for="u in searchParticipantRes" :key="'searchParticipant-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
              <div class="popover-checkbox" v-else>
                <el-checkbox-group v-model="checked.checkParticipant">
                  <li v-for="u in searchPerson.searchParticipant" :key="'searchParticipant-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
            </el-tab-pane>
            <el-tab-pane label="職人">
              <div class="popover-checkbox" v-if="searchGroupPerson">
                <el-checkbox-group v-model="checked.checkGroup">
                  <li v-for="u in searchGroupRes" :key="'searchGroup-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
              <div class="popover-checkbox" v-else>
                <el-checkbox-group v-model="checked.checkGroup">
                  <li v-for="u in searchPerson.searchGroup" :key="'searchGroup-'+u.name+u.id">
                    <el-checkbox :label="u.id">
                      <P v-if="u.file"><img :src="'file/users/'+u.file">{{u.name}}</P>
                      <P v-else><img src="images/icon-chatperson.png">{{u.name}}</P>
                    </el-checkbox>
                  </li>
                </el-checkbox-group>
              </div>
            </el-tab-pane>
          </el-tabs>
          <li class="popover-li">
            <el-button class="group-creat" @click="setGroup(current,0)">グループチャット作成</el-button>
          </li>
        </div>
        <div class="chat-add" slot="reference" @click.prevent="fetchSearchPerson">
          <img src="images/add@2x.png"/>
        </div>
      </el-popover>
    </el-tabs>
    <ChatFileList v-if="showChatFileListFlag" @closeFileShow="closeFileListShow"
                  :groupId="member.groupId"></ChatFileList>
    <ChatListTask v-if="showTaskFlag" :taskGroupUser="groupUsers" :chatClickMessage="clickMessage"
                  @taskBack="taskCancel" :groupId="member.groupId" :chatJumpIdFn="chatJumpId"></ChatListTask>
    <MailModal @closeMailModal="closeMailModal" @fetchFriends="fetchFriends" v-if="showMailModal"
               :chatEmail="chatEmail"></MailModal>
    <ForwardModel v-if="showForwardModal" @closeForwardModel="closeForwardModal" :chatGroupNow="member.groupId"></ForwardModel>
    <ChatPersonList v-if="showPersonDetailModal" @closePersonDetailModel="closePersonDetailModel"
                    :member="member" ref="ChatPersonListModel" :groupId="member.groupId"></ChatPersonList>
    <FileSelector v-if="fileSelector" :nodes="filesDoc" :groupId="member.groupId" @closeFileSelector="closeFileSelector"
      @closeFileSelectorWithSelected="closeFileSelectorWithSelected"></FileSelector>
  </div>
</template>

<script>
  import messages from "../../mixins/Messages";
  import reportValidation from '../../validations/report';
  import data from '../../../data/emoji-data.js';
  import SocketIO from 'socket.io-client';
  import Peer from 'skyway-js';
  import ChatFileList from "./ChatFileList";
  import ChatListTask from "./ChatListTask";
  import MailModal from '../../components/friend/MailModal';
  import {getCookieValue, compressImage} from '../../util';//【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更
  import chatValidation from '../../validations/chat.js';
  import formatChatMsg from '../../mixins/FormatChatMsg.js';
  import {chatCircle} from '../../mixins/ChatDrawCircle.js';
  import ForwardModel from './ForwardModel';
  import ChatPersonList from './ChatPersonList';
  import FileSelector from './FileSelector';

  export default {
    name: "ChatList",
    components: {ChatFileList, ChatListTask, MailModal, ForwardModel, ChatPersonList, FileSelector},
    mixins: [
      reportValidation,
      messages,
      formatChatMsg,
      ChatFileList,
      ChatListTask,
      chatValidation,
      chatCircle
    ],
    props: ['chatMessageSearchWord', 'searchChatListFlag', 'leaveToChatFlag', 'chatJumpObj', 'activeFlag'],
    data: function () {
      return {
        isTransverse :false,
        contactScrollIndex: 0,
        contactScrollLoading: false,
        contactNoMoreData: false,
        updated_filecount: '',
        joinMsg:'',
        showJoinMsg :false,
        showVideoType :false,
        showAudioType :false,
        showPersonDetailModal:false,
        editGroupName: {
          inputPopover: '',
        },
        chatJumpId: '',
        memberFlag: true,
        adminArea: false,
        datadiv: [],
        topCirclediv: '',
        leftCirclediv: '',
        topCircle: '',
        leftCircle: '',
        isdrawCircle: false,
        showVideo: true,
        showSound: true,
        showPic: false,
        isFullscreen: false,
        dialogVisible: false,
        videoIsDisabled: true,
        soundIsDisabled: true,
        picModalIsDisabled: false,
        isVideoChat: false,
        //chatLeft
        UnreadCount: '',
        searchPerson: {
          searchEnterprise: [],
          searchParticipant: [],
          searchGroup: [],
        },
        tabPositionpopover: 'bottom',
        checked: {
          checkGroup: [],
          checkParticipant: [],
          checkGroupPerson: [],
        },
        current: '',
        imageUrl: '',
        validateMessage: '',
        searchGroupPerson: '',
        chatSetGroup: {
          inputPopover: '',
        },
        tabPosition: 'left',
        value1: '',
        activeName: 'first',
        visibled: false,
        linkGroupCurrent: '',
        radioNo: 3,
        radioYes: 6,
        textarea: '',
        member: {
          id: '',
          userName: '',
          photo: '',
          enterpriseName: '',
          groupId: '',
          kind: '',
          userClearFlag: false,
        },
        chatLists: [],
        sendMsg: '',
        groupMember: [],
        rawFile: null,
        fileList: [],
        chatPerson: {},
        showEmojiFlg: false,
        data: [],
        emojiData: data,
        pannels: [''],
        activeIndex: 0,
        toUserId: '',
        toMessageId: '',
        isToOrRe: 2,

        showInformationFlag: false,
        isFriend: false,
        groupUsers: [],
        groupFriends: [],
        friendSearchWord: '',
        task: {
          taskContent: '',
          taskDate: '',
          userId: '',
          groupId: '',
          userName: '',
          userFile: '',
        },
        showTaskFlag: false,
        groupSearchWord: '',
        addToGroupFlag: false,
        user: {
          room: '',
          id: '',
          name: '',
        },
        userSelf: {},
        socket: null,
        updateMsgFlag: false,
        clickChatItem: {},
        clickChatIndex: '',

        room: null,
        roomName: 'mesh_video_group_',
        // SkyWayのpeer
        peer: null,
        // 自分のID
        peerId: '',
        // ローカル画面
        localStream: null,
        messages: '',
        audios: [],
        videos: [],
        showChatFileListFlag: false,
        clickMessage: '',
        oldChatClickIndex: 0,
        drawCircleX: 0,
        drawCircleY: 0,
        showMailModal: false,
        chatEmail: '',
        chatFileName: '',
        chatDate: '',
        openVideoFirst: true,
        videoPicName: '',
        hasShowFile: true,
        downloadChatFile: '',
        pdfFileName: '',
        pdfPageTotal: 0,
        pdfPageNow: 0,
        pdfPageNext: false,
        pdfPagePrev: false,
        showVideoFlag: false,
        chatSearchWordChanged: false,
        showVideoTabFlag: false,
        msgIsQuote: false,
        showChatFlag: false,
        videoArr: [],
        oneVideoObj: '',
        groupMemberShowFlag: false,
        toIdArr: [],
        toNameArr: [],
        friendArr: {
          enterpriseArr: [],
          contactArr: [],
          parterArr: []
        },
        enterpriseArr: [],
        contactArr: [],
        parterArr: [],
        startVideoGroupIdArr: [],
        startVideoFlag: false,
        showForwardModal: false,
        forwardGroupId: 0,
        sendMsgPure: '',
        quoteFileName:'',
        fileArr:[],
        rejectCount: 0,
        mineData:{},
        msgCursortPositionChatList:'',
        minId:0,
        scrollId:0,
        setId:0,
        nextPage:false,
        lastOffset: 0,
        showPosition: 0,
        //【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 begin
        imgQuality: 0.6,//画像の圧縮品質 0~1
        imageLimitSize: 1,//画像の圧縮制限は、1 Mを超えて圧縮されます。
        isChoice: false,
        isCompress: true,
        //【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 end
        lastReads: [], // store users' last read information
        chatlike: [], // store users' like
        filesDoc: [], //files from docs
        showDoc: [], //files be shown
        fileSelector: false, //fileSelectorComponent display status
        originWidth: 0, //origin image's width pixels
        originHeight: 0, //origin image's height pixels
        originRatio: 1.00, //origin images's ratio (width / height)
        containerWidth: 0, //container's width pixels
        containerHeight: 0, //container's height pixels
        containerRatio: 1.00, //container's ratio (width /height)
        mirrored: 0, //origin image mirrored (rotate 180)
      }
    },
    methods: {
      closeGroupPopover(){
        this.groupMemberShowFlag = false;
      },
      transverseModal() {
        this.isTransverse= !this.isTransverse;
        //if in room,send transverse event
        this.sendTransverseFlag();
      },
      // ローディングチャットリストデータ
      loadContractScroll($state) {
        let url = "/api/getChatPersonList";
        // 一回の取得数量
        let pageSize = process.env.MIX_PAGE_SIZE_CHAT_CONTACT? process.env.MIX_PAGE_SIZE_CHAT_CONTACT : 100;
        if (this.contactScrollIndex == 0) {
            this.contactScrollIndex = pageSize;
        } else {
            this.contactScrollIndex += pageSize;
        }
        let data = {'size': this.contactScrollIndex};
        // 処理中
        this.contactScrollLoading = true;
        axios.post(url, data).then(res => {
          if (res.data.chatList && res.data.chatList.length > 0) {
            this.chatPerson = res.data.chatList;
            this.UnreadCount = res.data.lastReadMsg;
            // 最後のデータではない
            this.contactNoMoreData = false;
          } else {
            // 最後のデータ
            this.contactNoMoreData = true;
          }
          // 処理完了
          this.contactScrollLoading = false;
        }).catch(err => {
          console.warn(err);
        });
      },
      showPersonDetail(){
        this.member.name = this.member.userName;
        this.member.parentName = '';
        this.showPersonDetailModal = true;
      },
      closePersonDetailModel(){
        this.showPersonDetailModal = false;
      },
      saveGroup() {
        this.$refs['editForm'].validate((valid) => {
          if (valid && !this.validateMessage) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let data = new FormData();
            let errMessage = this.commonMessage.error.insert;
            const headers = {headers: {"Content-Type": "multipart/form-data"}};
            if (this.imgFile) {
              data.append("file", this.imgFile);
            }
            data.append("name", this.editGroupName.inputPopover);
            data.append("id", this.member.groupId);
            if (this.member.photo) {
              data.append("img", this.member.photo);
            }
            let url = '/api/editGroup';
            axios.post(url, data, headers).then(res => {
              if (res.data.result === 0) {
                this.member.userName = this.editGroupName.inputPopover;
                if (res.data.params.img) {
                  this.member.photo = res.data.params.img + "?" +Date.now();
                }
                this.chatPerson = this.chatPerson.filter(data => {
                  if (data.group_id === this.member.groupId) {
                    if (this.member.photo) {
                      data.group.file = this.member.photo;
                    }
                    data.group.name = this.editGroupName.inputPopover;
                  }
                  return data;
                });
              }
              this.editGroupName.inputPopover = null;
              this.imgFile = null;
            }).catch(err => {
              console.warn(err);
              loading.close();
              this.$alert(errMessage, {showClose: false});
            })
            this.$refs['editGroup-'].doClose();
            loading.close();
          }
        })
      },
      editGroup() {
        this.$refs['editForm'].resetFields();
        this.imgFile = null;
        this.validateMessage = '';
        this.imageUrl = null;
        this.editGroupName.inputPopover = this.member.userName;
      },
      showImg(chatItem){
        let msg = '';
        if (chatItem.file_name){
          let file_name = chatItem.file_name.substring(chatItem.file_name.lastIndexOf('/') + 1, chatItem.file_name.length);
          msg= formatChatMsg.formatImg(file_name,chatItem.group_id);
        }
        return this.emoji(msg);
      },
      showMessage(chatItem, type) {
        if (chatItem.message) {
          chatItem.message = chatItem.message.replace(/\[icon:101\]/g, `<img src="./images/chat/101.png" width="16px" height="16px">`);
          chatItem.message = chatItem.message.replace(/\[icon:102\]/g, `<img src="./images/chat/102.png" width="16px" height="16px">`);
          if (chatItem.message.indexOf('[time:') !== -1) {
            let msg = formatChatMsg.quote(this.HTMLEncode(chatItem.message),chatItem.file_name,chatItem.group_id);
            return this.emoji(msg);
          } else {
            let msg = null;
            if (chatItem.message.indexOf('[To:') !== -1) {
              chatItem.message = formatChatMsg.formatToMsg(chatItem.message,chatItem.group_id);
            }
            if (chatItem.message.indexOf('[mid:') !== -1) {
              msg = formatChatMsg.formatReMsg(chatItem.message,chatItem.group_id);
              return this.emoji(msg);
            } else {
              return this.emoji(chatItem.message);
            }
          }
        }
      },
      getChatTime(chatItem) {
        let arr = chatItem.created_at.split(' ');
        if (arr.length === 1) {
          return arr[0];
        } else if (arr.length === 2) {
          let timeArr = arr[1].split(':');
          return timeArr[0] + ':' + timeArr[1];
        }
      },
      jumpToChatMsg() {
        $(".is-left").removeClass("is-active");
        $("#tab-linkGroup-" + this.member.groupId).addClass("is-active");
        if (!this.timer) {
          this.timer = true;
          setTimeout(() => {
            this.timer = false;
            let msgId = this.chatJumpObj.messageId;
            if (!msgId) {
              let length = this.chatLists.length - 1 >= 0 ? this.chatLists.length - 1 : 0; //fix undefined error;
              if(this.chatLists && this.chatLists.length) {
                msgId = this.chatLists[length].id;
              }else{
                msgId = 0;
              }
            }
            if (msgId && document.getElementById("li" + msgId)) {
              document.getElementById("li" + msgId).scrollIntoView();
            }
          }, 100)
        }
      },
      closeMailModal() {
        this.showMailModal = false;
      },
      fetchFriends() {
        this.showMailModal = false;
      },
      clearMessage() {
        this.fileArr = [];
        this.sendMsg = '';
        this.rawFile = null;
        this.quoteFileName = null;
        this.isToOrRe = 2;
        this.chatFileName = '';
        let imgObj = this.$refs.uploadImg;
        if (imgObj !== undefined) {
          imgObj.clearFiles();
        }
      },
      reDrawCircle() {
        let imgObj = this.$refs.videoImage;
        let divPic = document.getElementById('divPic');
        let naturalWidth = 0;
        let naturalHeight = 0;
        let showWidth = 0;
        let showHeight = 0;
        let proportionWidth = 0;
        let proportionHeight = 0;
        if (imgObj === undefined || imgObj.naturalHeight === undefined) {
          return;
        }
        naturalWidth = imgObj.naturalWidth;
        naturalHeight = imgObj.naturalHeight;
        showWidth = divPic.offsetWidth;
        showHeight = divPic.offsetHeight;
        proportionWidth = showWidth / naturalWidth;
        proportionHeight = showHeight / naturalHeight;
        if (naturalHeight * proportionWidth <= showHeight) {
          showHeight = naturalHeight * proportionWidth;
        } else {
          showWidth = naturalWidth * proportionHeight;
        }
        document.getElementById('videoImage').style.width = showWidth + 'px';
        document.getElementById('videoImage').style.height = showHeight + 'px';

        if (this.isdrawCircle !== undefined && this.drawCircleX !== undefined && this.drawCircleY !== undefined
            && this.isdrawCircle && this.drawCircleX !== 0 && this.drawCircleY !== 0) {
          proportionWidth = naturalWidth / showWidth;
          proportionHeight = naturalHeight / showHeight;
          let x = 0;
          let y = 0;
          let userAgent = navigator.userAgent;
          if (userAgent.indexOf("Firefox") > -1) {
            let domRect = document.getElementById('videoImage').getBoundingClientRect();
            x = domRect.x;
            y = domRect.y;
          } else {
            x = imgObj.x;
            y = imgObj.y;
          }
          showWidth = this.drawCircleX / proportionWidth + x;
          showHeight = this.drawCircleY / proportionHeight + y;
          if (this.isFullscreen) {
            this.topCircle = showHeight - 65;
            this.leftCircle = showWidth - 65;
          } else {
            this.topCircle = showHeight - 20;
            this.leftCircle = showWidth - 20;
          }
        }
        this.resizeImage();
        this.drawCircleOnChange();
      },
      checkImg(fileName) {
        let length = fileName.length;
        let ext =null;
        if (length && fileName){
          let file = fileName.split(':');
          if (file.length){
            ext = file[0].substring(file[0].length - 3, file[0].length);
          }
        }
        if (ext === 'jpg' || ext === 'png' || ext === 'gif' || ext === 'JPG' || ext === 'PNG' || ext === 'GIF') {
          return true;
        }
        return false;
      },
      checkPdf(fileName) {
        let length = fileName.split(':')[0].length;
        let ext = fileName.split(':')[0].substring(length - 3, length);
        if (ext === 'pdf' || ext === 'PDF') {
          return true;
        }
        return false;
      },
      mouseleave() {
        //#3616  チャットメッセージは両側で繰り返されます
        //document.getElementById('msgTextareaChatList').blur();//デフォーカス
        this.sendMsg = document.getElementById('msgTextareaChatList').value;
        if (this.chatLists.length > 0 && this.chatLists[this.oldChatClickIndex]) {
          this.chatLists[this.oldChatClickIndex].isClicked = false;
        }
      },
      mouseover(index) {
        if (this.oldChatClickIndex !== index && this.oldChatClickIndex < this.chatLists.length
            && this.chatLists && this.chatLists[this.oldChatClickIndex]) {
          this.chatLists[this.oldChatClickIndex].isClicked = false;
        }
        this.clickChatMessage(index);
      },
      closeFileListShow() {
        this.showChatFileListFlag = false;
      },
      joinVideoAction() {
        if (!this.showVideoTabFlag) {
          if (this.joinMsg === '終了') {
            this.showAudioType = false;
            this.showVideoType = false;
            this.showJoinMsg = false;
            this.joinMsg = '';
            return;
          }
          if (this.showAudioType) {
            this.showVideoTab(1, 1)
          } else if (this.showVideoType) {
            this.showVideoTab(2, 1)
          } else {
            this.showAudioType = false;
            this.showVideoType = false;
            this.showJoinMsg = false;
            this.joinMsg = '';
          }
        }
      },
      showFiles() {
        this.showChatFileListFlag = true;
      },
      closeFiles() {
        this.showChatFileListFlag = false;
      },
      showVideoTab(type,linkFlag = 0) {
        this.videoArr = [];
        this.startVideoFlag = true;
        this.showSound = true;
        this.showVideo = true;
        this.videoIsDisabled = true;
        this.soundIsDisabled = true;
        this.hasShowFile = true;
        this.downloadChatFile = '';
        this.pdfFileName = '';
        this.pdfPageTotal = 0;
        this.pdfPageNow = 0;
        this.pdfPageNext = false;
        this.pdfPagePrev = false;
        this.isTransverse=0;
        let videoImage = document.getElementById("videoImage");
        videoImage.style.transform = 'rotate(0deg)'; //default
        this.getUserMedia(type).then(res => {
          if (type === 1) {
            this.picModalIsDisabled = false;
            if (this.startVideoGroupIdArr.indexOf(this.member.groupId) === -1) {
              if (!linkFlag) {
                this.sendMsg = '[icon:101]';
                this.sendMessage(1);
              }
              this.socket.emit("chat.call", "audio");
            }
          } else {
            this.picModalIsDisabled = true;
            this.showPic = false;
            if (this.startVideoGroupIdArr.indexOf(this.member.groupId) === -1) {
              if (!linkFlag) {
                this.sendMsg = '[icon:102]';
                this.sendMessage(1);
              }
              // let mineRoomName = "group_video_" + this.member.groupId;
              // this.joinVideo(mineRoomName);
              this.socket.emit("chat.call", "vedio");
            }
          }
          if (!this.showVideoTabFlag) {
            this.videoConn(type,linkFlag);
            this.showVideoTabFlag = true;
          }
        }).catch(err => {
          let errMessage = this.commonMessage.error.mediaDevice;
          console.warn(errMessage);
        });
      },
      clickChatMessage(index) {
        this.oldChatClickIndex = index;
        if (this.chatLists && this.chatLists[index]) {
          //#3616  チャットメッセージは両側で繰り返されます
          //document.getElementById('msgTextareaChatList').blur();
          this.sendMsg = document.getElementById('msgTextareaChatList').value;
          this.chatLists[index].isClicked = true;
        }
      },
      drawCircle(e) {
        this.drawCircleOnClick(e);
      },
      closeVideoModal() {
        if (this.showVideo) {
          this.disableVideo();
        } else {
          this.enableVideo();
        }
        this.showVideo = !this.showVideo;
      },
      closeSoundModal() {
        if (this.showSound) {
          this.disableMic();
        } else {
          this.enableMic();
        }
        this.showSound = !this.showSound;
      },
      openPicModal() {
        this.showPic = !this.showPic;
        this.picModalIsDisabled = false;
      },
      videoPicRemove(file, fileList) {
      },
      uploadVideoPic(file) {
        // reset image info when upload new file
        this.isTransverse = false;
        this.showPic = false;
        this.isdrawCircle = false;
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        data.append('fileName', JSON.stringify(file.raw.name));
        data.append("file", file.raw);
        data.append("groupId", this.member.groupId);
        axios.post("/api/setVideoPic", data, headers).then(res => {
          if (res.data.result === 0) {
            if (res.data.params) {
              this.videoPicName = 'file/' + this.member.groupId + '/' + encodeURIComponent(res.data.params);
              this.hasShowFile = false;
              this.downloadChatFile = this.member.groupId + '/' + encodeURIComponent(res.data.params);
              let msg = {
                nickname: this.user.name,
                id: this.user.id,
                file: res.data.params,
                pageCount: 0,
                pdfFile: '',
                pageNow: 0,
                timeStamp: (new Date()).getTime(),
              };
              this.socket.emit("chat.send.file", msg);
              this.showPic = true;
              //remove pdf flag
              this.pdfPageNow = 0;
              this.pdfPageTotal = 0;
              this.pdfPageNext = false;
              this.pdfPagePrev = false;
            }
          } else {
            let errMessage = this.commonMessage.error.wrongFormat;
            this.$alert(errMessage, {showClose: false});
          }
        }).catch(err => {
          console.warn(err);
        })
      },
      uploadVideoPdf(file) {
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        data.append('fileName', JSON.stringify(file.raw.name));
        data.append("file", file.raw);
        data.append("groupId", this.member.groupId);
        axios.post("/api/setVideoPdf", data, headers).then(res => {
          if (res.data.result === 0) {
            if (res.data.params) {
              this.pdfFileName = res.data.params.uploadedFileName;
              this.videoPicName = 'file/' + this.member.groupId + '/' + encodeURIComponent(res.data.params.imagePath);
              this.hasShowFile = false;
              this.downloadChatFile = this.member.groupId + '/' + encodeURIComponent(this.pdfFileName);
              this.pdfPageTotal = res.data.params.maxPage;
              this.pdfPageNow = 1;
              if (this.pdfPageTotal > 1) {
                this.pdfPageNext = true;
              }
              this.pdfPagePrev = false;
              let msg = {
                nickname: this.user.name,
                id: this.user.id,
                file: res.data.params.imagePath,
                pageCount: this.pdfPageTotal,
                pdfFile: this.pdfFileName,
                pageNow: this.pdfPageNow,
                timeStamp: (new Date()).getTime(),
              };
              this.socket.emit("chat.send.file", msg);
              this.showPic = true;
            }
          } else {
            let errMessage = this.commonMessage.error.wrongFormatPdf;
            this.$alert(errMessage, {showClose: false});
          }
        }).catch(err => {
          console.warn(err);
        })
      },
      changePdfPage(pageNum) {
        if (pageNum > this.pdfPageTotal || pageNum < 1) {
          console.log('PageError');
          return false;
        }

        let data = new FormData();
        data.append("groupId", this.member.groupId);
        data.append("pdfFileName", this.pdfFileName);
        data.append("page", pageNum);
        axios.post("/api/getPdfPage", data).then(res => {
          if (res.data.result === 0) {
            if (res.data.params) {
              this.videoPicName = 'file/' + this.member.groupId + '/' + encodeURIComponent(res.data.params);
              this.pdfPageNow = pageNum;
              let msg = {
                nickname: this.user.name,
                id: this.user.id,
                file: res.data.params,
                pdfFile: this.pdfFileName,
                pageCount: this.pdfPageTotal,
                pageNow: this.pdfPageNow,
                timeStamp: (new Date()).getTime(),
              };
              this.socket.emit("chat.send.file", msg);
              this.showPic = true;

              if (this.pdfPageNow > 1) {
                this.pdfPagePrev = true;
              } else {
                this.pdfPagePrev = false;
              }

              if (this.pdfPageNow < this.pdfPageTotal) {
                this.pdfPageNext = true;
              } else {
                this.pdfPageNext = false;
              }
            }
          }
        }).catch(err => {
          console.warn(err);
        })
      },
      downloadChatFileOp() {
        const link = document.createElement('a');
        link.href = 'download/' + this.downloadChatFile;

        link.download = true;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      },
      fullScreenModal() {
        this.isFullscreen = !this.isFullscreen;
        this.reDrawCircle();
      },
      changeClickChatItem(groupId) {
        this.showChatFlag = true;
        this.fetchChatPersonList(groupId);
      },
      chatToUser(chatItem) {
        let flag = true;
        for (let i = 0; i < this.chatPerson.length; i++) {
          if (this.chatPerson[i].group.kind === 1 && this.chatPerson[i].user_id === chatItem.from_user_id) {
            flag = false;
            this.fetchMember(this.chatPerson[i]);
            break;
          }
        }
        if (flag) {
          let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post('/api/createGroup', {
            userId: chatItem.from_user_id
          }).then(res => {
            if (res.data.result === 0) {
              this.chatPerson.push(res.data.params);
              this.linkGroupCurrent = 'linkGroup-'+res.data.params.group_id;
              this.fetchMember(res.data.params);
            }
            loading.close()
          }).catch(err => {
            console.warn(err);
            loading.close()
          });
        }
      },
      toAddFriend(chatItem) {
        this.showMailModal = true;
        this.chatEmail = chatItem.user.email;
      },
      joinVideo(roomName){
        //leaveRoom
        this.socket.emit("leave", this.user.room);
        //joinRoom
        this.user.room = roomName;
        this.socket.emit("join", this.user);
      },
      join: function () {
        this.user.room = 'group_' + this.member.groupId;
        this.socket.emit("join", this.user);
      },
      leave: function () {
        this.socket.emit("leave", this.user.room);
      },
      send: function (flag, data) {
        //   "msg" ,  メッセージ
        //   "nickname" ,  ユーザー名
        //   "id" ,  ユーザーID
        //   "mid" ,  メッセージ変更の時はメッセージID
        //   "file" ,  添付ファイル名
        //   "timeStamp" ,  タイムスタンプ Long
        //   "maxid" メッセージの最大ID
        if (flag === 0) {
          let msg = {
            msg: this.sendMsg,
            gid: this.member.groupId,
            nickname: this.user.name,
            id: this.user.id,
            maxid: data.id,
            file: data.file_name,
            fileSize: data.fileSize,
            mid: 0,
            vedio: this.startVideoFlag,
            timeStamp: (new Date()).getTime(),
            task_id: data.task_id > 0 ? data.task_id : 0,
          };
          if(data.removeId && data.removeId > 0){
            msg.removeId = data.removeId;
          }
          this.socket.emit("chat.send.message", msg);
        } else if (flag === 1) {
          let msg = {
            nickname: this.user.name,
            file: data.file_name,
            maxid: data.id,
            msg: this.sendMsg,
            id: this.user.id,
            mid: data.id,
            timeStamp: (new Date()).getTime(),
            task_id: data.task_id > 0 ? data.task_id : 0,
          };
          if(data.removeId && data.removeId > 0){
            msg.removeId = data.removeId;
          }
          this.socket.emit("chat.send.message", msg);
        }
        this.clearMessage();
      },
      delete: function (id) {
        let msg = {
          gid: this.member.groupId,
          nickname: this.user.name,
          id: this.user.id,
          mid: id,
          timeStamp: (new Date()).getTime(),
        };
        this.socket.emit("chat.delete.message", msg);
      },
      deleteGroup: function () {
        let msg = {
          gid: this.member.groupId,
          timeStamp: (new Date()).getTime(),
        };
        this.socket.emit("chat.delete.user", msg);
        this.$emit('deleteGroup', msg);
      },
      updateMsg(chatItem, index) {
        this.updateMsgFlag = true;
        this.sendMsg = this.HTMLDecode(chatItem.message);
        this.clickChatIndex = index;
        this.clickChatItem = chatItem;
      },
      clearGroup() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$confirm(this.commonMessage.confirm.delete.group(this.memberObj.group.name),
            {
              dangerouslyUseHTMLString: true,
              showCancelButton: true,
              customClass: 'group-delete-confirm',
              confirmButtonText: this.commonMessage.button.ok,
              cancelButtonText: this.commonMessage.button.cancel
            }).then(() => {
          this.groupMemberShowFlag = false;
          axios.post("/api/clearGroup", {
            groupId: this.member.groupId,
          }).then(res => {
            this.deleteGroup();
            this.showChatFlag = false;
            this.delPersonComment(this.member.groupId);
            loading.close();
          }).catch(err => {
            console.warn(err);
            loading.close();
          });
        }).catch(action => {
          console.warn(action);
          loading.close();
        });
      },
      choose(index) {
        this.groupFriends[index].isChecked = !this.groupFriends[index].isChecked;
      },
      chatMessageSearch() {
        let errMessage = this.commonMessage.error.chatList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/getChatList", {
          groupId: this.member.groupId,
          word: this.chatMessageSearchWord,
        }).then(res => {
          if (res.data.result === 0) {
            this.chatLists = res.data.params.models;
            //#2645 turn url into hyperLinks
            for(let i = 0;i < this.chatLists.length; i++) {
              this.chatLists[i].like = [];
              this.chatLists[i].message =  this.hyperLinks(this.chatLists[i].message);
              this.chatLists[i].readUsers = this.checkReadStatus(this.chatLists[i].id , this.lastReads, this.groupUsers, this.chatLists[i].from_user_id, this.userSelf.id);
            }
            this.chatlike = res.data.params.chatlike;
            //chatlike push --------start----------
            for (let k = 0; k < this.chatLists.length;k++) {
              for(let f = 0; f< this.chatlike.length; f++){
                let messageIdArray = this.chatlike[f].message_id.split(",");
                for (let s = 0; s < messageIdArray.length; s++) {
                  if (messageIdArray[s] == this.chatLists[k].id) {
                    let user= [];
                    user.user_id=this.chatlike[f].user_id;
                    user.name=this.chatlike[f].name;
                    user.file=this.chatlike[f].file;
                    this.chatLists[k].like.push(user);
                  }
                }
              }
            }
            //chatlike push --------end----------
            this.adminArea = res.data.params.adminArea;
            this.chatSearchWordChanged = false;
          }
          loading.close();
        }).catch(err => {
          console.warn(err);
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      addTaskUser(user) {
        this.task.groupId = user.group_id;
        this.task.userId = user.user_id;
        this.task.userName = user.user.name;
        this.task.userFile = user.user.file;
      },
      showTask(chatItem,id = '') {
        this.clickMessage='';
        if (!chatItem){
          this.showTaskFlag = true;
          return false;
        }
        if(id){
          this.chatJumpId = id;
        }
        this.showTaskFlag = true;
        let file_name = null;
        if (chatItem.file_name) {
          this.quoteFileName = chatItem.file_name.substring(chatItem.file_name.lastIndexOf('/') + 1, chatItem.file_name.length);
        }
        this.clickMessage = formatChatMsg.insertQuote(chatItem.id, chatItem.user.id,
            chatItem.created_at, this.HTMLDecode(chatItem.message), file_name, 1);
      },
      taskCancel(data) {
        this.showTaskFlag = !this.showTaskFlag;
        if (data && data.id) {
          this.sendMsg = '■タスクを追加しました。'+'\n'+this.HTMLDecode(data.msg);
          this.sendMessage(1, data.id);
          this.$alert('タスクを追加しました。', {showClose: false});
        }else{
          this.quoteFileName = '';
        }
      },
      delInGroup(user, index) {
        let _user_info = sessionStorage.getItem('_user_info');
        let user_id=JSON.parse(_user_info).id;
        if(user.user_id == user_id){
            this.sendRemoveUserMessage(user.user_id,user.user.name);
            this.$emit('delInGroup', user.user_id, user.group_id);
            axios.post('/api/delInGroup', {
                userId: user.user_id,
                groupId: user.group_id,
            }).then((res) => {
                if (res.data.params <= 1) {
                    this.memberFlag = false;
                }
                if(res.data.errors){
                    this.$alert(res.data.errors[0], {showClose: false});
                }else{
                    this.groupUsers.splice(index, 1);
                    this.groupFriends.push(user.user);
                }
            }).catch(err => {
                console.warn(err);
            });
        }else{
            axios.post('/api/delInGroup', {
                userId: user.user_id,
                groupId: user.group_id,
            }).then((res) => {
                if (res.data.params <= 1) {
                    this.memberFlag = false;
                }
                if(res.data.errors){
                    this.$alert(res.data.errors[0], {showClose: false});
                }else{
                    this.groupUsers.splice(index, 1);
                    this.groupFriends.push(user.user);
                    this.sendRemoveUserMessage(user.user_id,user.user.name);
                    this.$emit('delInGroup', user.user_id, user.group_id);
                }
            }).catch(err => {
                console.warn(err);
            });
        }
      },
      useMsg(chatItem) {
        this.msgIsQuote = true;
        let file_name = null;
        if (chatItem.file_name) {
          this.quoteFileName = chatItem.file_name.substring(chatItem.file_name.lastIndexOf('/') + 1, chatItem.file_name.length);
        }
        this.sendMsg = formatChatMsg.insertQuote(chatItem.id, chatItem.user.id,
            chatItem.created_at, this.HTMLDecode(chatItem.message), file_name);
      },
      delMsg(chatItem, index) {
        let errMessage = this.commonMessage.error.chatMsgDelete;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        // このメッセージを削除しますか？
        this.$confirm(this.commonMessage.confirm.delete.chatMessage).then(() => {
          axios.post("/api/delChatMessage", {
            id: chatItem.id,
          }).then(res => {
            this.delete(chatItem.id);
            loading.close();
          }).catch(err => {
            console.warn(err);
            this.$alert(errMessage, {showClose: false});
            loading.close();
          })
        }).catch(action => {
          loading.close();
        });
      },
      backMsg(chatItem) {
        this.isToOrRe = 2;
        this.sendMsg = '[Re]' + chatItem.user.name + '\n';
        this.toMessageId = chatItem.id;
        this.toUserId = chatItem.user.id;
      },
      //HTMLタグを処理
      HTMLDecode(text) {
        let temp = document.createElement("div");
        temp.innerHTML = text;
        let output = temp.innerText || temp.textContent;
        temp = null;
        return output;
      },
      //HTMLタグを処理
      HTMLEncode(html) {
        let temp = document.createElement("div");
        (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
        let output = temp.innerHTML;
        temp = null;
        return output;
      },
      checkSocketConnect(){
        if(!this.socket || (this.socket && (this.socket.connected == false))){
          alert('Soket接続に失敗しました');
        }
      },
      sendMessage(data, taskId = 0) {
        if (data === 1 || (event.ctrlKey && event.keyCode === 13)) {
          // v-mode.lazyの方法で、blurとfocusで値を更新
          this.$refs.message.blur();
          this.$refs.message.focus();
          if (this.sendMsg.replace(/\n*$/g, '').replace(/\n/g, '') === '' && !this.fileArr.length) {
            this.sendMsg = '';
            return false;
          } else {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            if (this.updateMsgFlag) {
              this.updateMsgFlag = false;
              axios.post("/api/updateMessage", {
                id: this.clickChatItem.id,
                message: this.sendMsg,
              }).then(res => {
                if (res.data.result === 0 && res.data.params) {
                  this.send(1, res.data.params);
                }
                loading.close();
                this.checkSocketConnect();
              }).catch(err => {
                console.warn(err);
                loading.close();
              })
            } else {
              let data = new FormData();
              let headers = {headers: {"Content-Type": "multipart/form-data"}};
              data.append('group_id', JSON.stringify(this.member.groupId));
              if (this.fileArr.length) {
                data.append('fileName', JSON.stringify(this.fileArr));
              }
              if(this.sendMsg && this.sendMsg.indexOf('[qt][qt]') === 0
                  && this.sendMsg.substring(this.sendMsg.length-5) === '[/qt]'){
                this.sendMsg = this.sendMsg.substring(4,this.sendMsg.length-5)
              }
              data.append('message', JSON.stringify(this.sendMsg));
              data.append('toUserId', JSON.stringify(this.toUserId));
              data.append('toMessageId', JSON.stringify(this.toMessageId));
              data.append('isToOrRe', JSON.stringify(this.isToOrRe));
              data.append('toIdArr', JSON.stringify(this.toIdArr));
              data.append('toNameArr', JSON.stringify(this.toNameArr));
              data.append('forwardFileName', JSON.stringify(this.chatFileName));
              data.append('quoteFileName', JSON.stringify(this.quoteFileName));
              data.append('task_id', taskId);
              let errMessage = this.commonMessage.error.chatMsgSend;
              axios.post("/api/setChatMessage", data, headers)
                  .then(res => {
                    if (res.data.result === 0) {
                      window.messageNewGroup = this.member.groupId;
                      //#2645 turn url into hyperLinks
                      res.data.params.message = this.hyperLinks(res.data.params.message);
                      this.send(0, res.data.params);
                      if (taskId !== 0 && !this.chatJumpId) {
                        axios.post('/api/updateUserTask', {
                          id: taskId,
                          messageId: res.data.params.id
                        }).then((r) => {
                        }).catch(err => {
                          console.warn(err);
                          this.$alert(errMessage, {showClose: false});
                        });
                      }
                      this.chatJumpId = '';
                    }else{
                        this.$alert(res.data.errors, {showClose: false});
                    }
                    this.fileArr = [];
                    this.toNameArr = [];
                    this.toIdArr = [];
                    this.fileList = [];
                    loading.close();
                    this.checkSocketConnect();
                  }).catch(err => {
                console.warn(err);
                this.$alert(errMessage, {showClose: false});
                loading.close();
              })
            }
          }
        }
      },
      sendRemoveUserMessage(removeId , name){
        let tipMessage = this.commonMessage.warning.removeUserFromGroup;
        let removeUserMessage = name + tipMessage;
        this.sendMsg = removeUserMessage;
        let dataFrom = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        dataFrom.append('group_id', JSON.stringify(this.member.groupId));
        dataFrom.append('message', JSON.stringify(removeUserMessage));
        dataFrom.append('toUserId', JSON.stringify(this.toUserId));
        dataFrom.append('toMessageId', JSON.stringify(this.toMessageId));
        dataFrom.append('isToOrRe', JSON.stringify(this.isToOrRe));
        dataFrom.append('toIdArr', JSON.stringify(this.toIdArr));
        dataFrom.append('toNameArr', JSON.stringify(this.toNameArr));
        dataFrom.append("file", this.rawFile);
        let errMessage = this.commonMessage.error.chatMsgSend;
        axios.post("/api/setChatMessage", dataFrom, headers)
            .then(res => {
              if (res.data.result === 0) {
                res.data.params.removeId = removeId;
                this.send(0, res.data.params);
                this.sendMsg = '';
              }
            }).catch(err => {
          console.warn(err);
          this.$alert(errMessage, {showClose: false});
        })
      },
      submit() {
        this.data.push(this.sendMsg);
        this.sendMsg = ''
      },
      changeActive(index) {
        this.activeIndex = index
      },
      selectItem(emoji) {
        this.showEmojiFlg = false;
        this.sendMsg += emoji;
        this.$refs['emojiPopover'].doClose();
      },
      showEmoji() {
        this.showEmojiFlg = !this.showEmojiFlg;
      },
      //指定した位置に文字列を挿入する
      insertStr(soure, start, newStr) {
        return soure.slice(0, start) + newStr + soure.slice(start);
      },
      toUser(user) {
        this.isToOrRe = 1;
        if (this.sendMsg === '') {
          this.sendMsg = '[To]' + user.name;
        } else {
          //マウスカーソルに挿入'[To]'
          this.msgCursortPositionChatList = $('#msgTextareaChatList')[0].selectionStart;
          this.sendMsg = this.insertStr(this.sendMsg,this.msgCursortPositionChatList,'[To]' + user.name);
        }
        this.toIdArr.push(user.id);
        this.toNameArr.push(user.name);
        this.toUserId = user.id;
      },
      toAllUser() {
        this.isToOrRe = 1;
        for (let i = 0; i < this.groupUsers.length; i++) {
          this.toIdArr.push(this.groupUsers[i].user.id);
          this.toNameArr.push(this.groupUsers[i].user.name);
        }
        if (this.sendMsg === '') {
          this.sendMsg = '[To]All';
        } else {
          //マウスカーソルに挿入'[To]'
          this.msgCursortPositionChatList = $('#msgTextareaChatList')[0].selectionStart;
          this.sendMsg = this.insertStr(this.sendMsg,this.msgCursortPositionChatList,'[To]All');
        }
        this.$refs['showGroupUserTo'].doClose();
      },
      getUser(data) {
        this.fetchMember(data);
      },
      cancelAddToGroup() {
        this.addToGroupFlag = false;
        this.friendSearchWord = '';
        this.enterpriseArr = [];
        this.contactArr = [];
        this.parterArr = [];
      },
      addFriendToGroup() {
        let idArr = [];
        for (let i = 0; i < this.enterpriseArr.length; i++) {
          this.enterpriseArr[i].user = {};
          this.enterpriseArr[i].user.file = this.enterpriseArr[i].file;
          this.enterpriseArr[i].group_id= this.member.groupId;
          this.enterpriseArr[i].user_id= this.enterpriseArr[i].id;
          this.enterpriseArr[i].user.name = this.enterpriseArr[i].name;
          this.enterpriseArr[i].user.id = this.enterpriseArr[i].id;
          let index = this.friendArr.enterpriseArr.indexOf(this.enterpriseArr[i]);
          this.enterpriseArr[i].user.isBoss = 2;
          if (index >= 0) {
            this.friendArr.enterpriseArr.splice(index, 1);
          }
          let userArr=[];
          for (let i = 0; i < this.groupUsers.length; i++) {
              userArr[i] = this.groupUsers[i].user_id
          }
          if (idArr.indexOf(this.enterpriseArr[i].id) < 0) {
              if(!userArr.includes(this.enterpriseArr[i].user.id)){
                  this.groupUsers.push(this.enterpriseArr[i]);
              }
            idArr.push(this.enterpriseArr[i].id)
          }
        }
        for (let i = 0; i < this.contactArr.length; i++) {
          this.contactArr[i].user = {};
          this.contactArr[i].user.file = this.contactArr[i].file;
          this.contactArr[i].group_id= this.member.groupId;
          this.contactArr[i].user_id= this.contactArr[i].id;
          this.contactArr[i].user.name = this.contactArr[i].name;
          this.contactArr[i].user.isBoss = 2;
          this.contactArr[i].user.id = this.contactArr[i].id;
          let index = this.friendArr.contactArr.indexOf(this.contactArr[i]);
          if (index >= 0) {
            this.friendArr.contactArr.splice(index, 1);
          }
          let userArr=[];
          for (let i = 0; i < this.groupUsers.length; i++) {
              userArr[i] = this.groupUsers[i].user_id
          }
          if (idArr.indexOf(this.contactArr[i].id) < 0) {
            if(!userArr.includes(this.contactArr[i].user.id)){
                this.groupUsers.push(this.contactArr[i]);
            }
            idArr.push(this.contactArr[i].id);
          }
        }
        for (let i = 0; i < this.parterArr.length; i++) {
          this.parterArr[i].user = {};
          this.parterArr[i].user.file = this.parterArr[i].file;
          this.parterArr[i].group_id= this.member.groupId;
          this.parterArr[i].user_id= this.parterArr[i].id;
          this.parterArr[i].user.name = this.parterArr[i].name;
          this.parterArr[i].user.isBoss = 2;
          this.parterArr[i].user.id = this.parterArr[i].id;
          let index = this.friendArr.parterArr.indexOf(this.parterArr[i]);
          if (index >= 0) {
            this.friendArr.parterArr.splice(index, 1);
          }
          let userArr=[];
          for (let i = 0; i < this.groupUsers.length; i++) {
              userArr[i] = this.groupUsers[i].user_id
          }
          if (idArr.indexOf(this.parterArr[i].user.id) < 0){
            if(!userArr.includes(this.parterArr[i])){
              this.groupUsers.push(this.parterArr[i]);
            }
            idArr.push(this.parterArr[i].id);
          }
        }
        this.cancelAddToGroup();
        axios.post('/api/setFriendToGroup', {
          userIdArr: idArr,
          groupId: this.member.groupId,
        }).then((res) => {
        }).catch(err => {
          console.warn(err);
        });
      },
      searchFriend(index) {
        if (index === 1) {
          this.addToGroupFlag = !this.addToGroupFlag;
        }
        axios.get('/api/getGroupFriend', {
          params: {
            words: this.friendSearchWord,
            groupId: this.member.groupId,
            parentId: this.member.parentId
          }
        }).then((res) => {
          this.friendArr.enterpriseArr = res.data.enterpriseArr;
          this.friendArr.contactArr = res.data.contactArr;
          this.friendArr.parterArr = res.data.participantsArr;
        }).catch(err => {
          console.warn(err);
        });
      },
      showGroupUser(index) {
        if (index === 1) {
          this.groupMemberShowFlag = !this.groupMemberShowFlag;
        }
        this.addToGroupFlag = false;
        if (this.groupUsers.length > 0) {
          if (index === 1 && this.groupUsers[0].group_id === this.member.groupId) {
            return;
          }
        }
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        axios.get('/api/getGroupUser', {
          params: {
            id: this.member.groupId,
            words: this.groupSearchWord,
          }
        }, headers).then((res) => {
          if (res.data.result === 0) {
            this.groupUsers = res.data.params;
            //this.groupSearchWord = '';
            for(let i=0;i<this.chatLists.length;i++) {
              this.chatLists[i].readUsers = this.checkReadStatus(this.chatLists[i].id , this.lastReads, this.groupUsers, this.chatLists[i].from_user_id, this.userSelf.id);
            }
          }
        }).catch(err => {
          console.warn(err);
        });
      },
      //#2645 turn url into hyperLinks
      hyperLinks(data) {
        let result = '';
        let re = /((http|ftp|https):\/\/)?([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?/g;

        return result = data.replace(re,function($url){
          if ($url.indexOf('https://') == -1 && $url.indexOf('http://') == -1){
            let tmp = $url;
            $url = 'https://'+$url;
            return "<a style='text-decoration: underline; color: #00aced;' href='" + $url + "' target='_blank'>" + tmp + "</a>";

          }
          return "<a style='text-decoration: underline; color: #00aced;' href='" + $url + "' target='_blank'>" + $url + "</a>";
        });
      },
      receiveMemberMsg(groupId, parentId) {
        let errMessage = this.commonMessage.error.chatList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        //add jump-to message tag
        let jumpTag = 0;
        if (this.chatJumpObj) {
          jumpTag = this.chatJumpObj.messageId;
        }
        //attempt to get file data if exists
        if(window.groupId === groupId) {
          let fileListFromDoc = window.filesSent;
          if(fileListFromDoc) {
            for(let i=0;i<fileListFromDoc.length;i++) {
              this.fileArr.push(fileListFromDoc[i].name);
              let arr = new Array();
              arr['name'] =fileListFromDoc[i].name.substring(14)+"\xa0\xa0\xa0\xa0("+fileListFromDoc[i].size.toLocaleString()+"KB)";
              this.fileList.push(arr);
            }
            if(this.fileList.length > 4){
              this.updated_filecount = 4;
            }else{
              this.updated_filecount = this.fileList.length;
            }
            //release tmp var
            window.groupId = 0;
            window.filesSent = null;
          }
          fileListFromDoc = [];
        }
        axios.post("/api/getChatList", {
          'parentId': parentId,
          'groupId': groupId,
          'lastOffset': 0,
          'jumpTag': jumpTag,
        }).then(res => {
          if (res.data.result === 0) {
            this.setId=0;
            this.chatLists = res.data.params.models;
            this.chatlike = res.data.params.chatlike;
            for(let i = 0;i < this.chatLists.length; i++) {
              this.chatLists[i].like = [];
              this.chatLists[i].message =  this.hyperLinks(this.chatLists[i].message)

            }
            //chatlike push --------start----------
            for (let k = 0; k < this.chatLists.length;k++) {
              for(let f = 0; f< this.chatlike.length; f++){
                let messageIdArray = this.chatlike[f].message_id.split(",");
                for (let s = 0; s < messageIdArray.length; s++) {
                  if (messageIdArray[s] == this.chatLists[k].id) {
                    let user= [];
                    user.user_id=this.chatlike[f].user_id;
                    user.name=this.chatlike[f].name;
                    user.file=this.chatlike[f].file;
                    this.chatLists[k].like.push(user);
                  }
                }
              }
            }
            //chatlike push --------end----------
            let readStatus = [];
            for(let i = 0; i< res.data.params.lastReads.length; i++) {
              let statusTmp = [];
              statusTmp['userId'] = res.data.params.lastReads[i].user_id;
              statusTmp['lastMessageId'] = res.data.params.lastReads[i].message_id;
              readStatus.push(statusTmp);
            }
            this.lastReads = readStatus; //users last read informationreceive
            if(res.data.params.models.length > 0 ){
              this.lastOffset = res.data.params.models[0].id;
            }
            this.adminArea = res.data.params.adminArea;
            if(this.chatLists.length > 0){
              this.scrollId=this.chatLists[this.chatLists.length - 1].id;
            }
            this.minId =res.data.params.min_id;
            if (!this.memberObj.group.parent_id) {
              this.adminArea = true;
            }
            if (this.chatJumpObj) {
              this.jumpToChatMsg();
            }
            if (this.user.room) {
              this.user.room = '';
              this.leave();
            }
            this.join();
            //when fetch messages,send read status message
            if(this.chatLists.length > 0) {
              let readStatusMessage = {
                user_id: this.userSelf.id,
                message_id: this.chatLists[this.chatLists.length -1].id,
                file: this.userSelf.file ? this.userSelf.file : '',
                name: this.userSelf.name
              };
              //send ack event
              this.socket.emit("chat.send.message.ack", readStatusMessage);
            }
            this.showGroupUser();
          }
          loading.close();
        }).catch(err => {
          console.warn(err);
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      handleScrollx() {
        if(this.activeFlag !== 'activeFirst'){
          return;
        }

        if(this.chatLists.length == 0 || this.chatLists[0].id<=this.minId){
          return;
        }
        if(document.getElementById('chatLists').scrollTop > this.showPosition){
          return;
        }
        //trigger when reaching the specified height
        if((document.getElementById('chatLists').scrollTop < document.getElementById("li"+this.scrollId).scrollHeight) && !this.nextPage){
          document.getElementById('chatLists').removeEventListener('scroll', this.handleScrollx);
          this.showPosition = document.getElementById("chatLists").scrollHeight;
          this.nextPage=true;
          let errMessage = this.commonMessage.error.chatList;
          let loadingTips = this.commonMessage.query.getMore;
          const loading = this.$loading({text: loadingTips, lock: true, background: 'rgba(0, 0, 0, 0)', customClass: 'getMoreLoading'});
          let parentId = 0;
          if (data.parent) {
            parentId = data.parent.id;
          }
          axios.post("/api/getChatList", {
            'parentId': parentId,
            'groupId': this.memberObj.group_id,
            'lastOffset':this.chatLists[0].id,
          }).then(res => {
            if (res.data.result === 0) {
              // this.setId=this.chatLists[0].id;
              this.nextPage=false;
              if(res.data.params.models.length > 0) {
                let chatLists = res.data.params.models;
                this.setId=chatLists[chatLists.length-1].id;
                //refresh the date line
                for(let l = 0; l < chatLists.length; l++){
                  chatLists[l].like = [];
                  if(chatLists[l]['showDate']){
                    for(let i = 0;i < this.chatLists.length; i++) {
                      if(this.chatLists[i]['showDate'] && chatLists[l]['dateSpan'] == this.chatLists[i]['dateSpan']) {
                        this.chatLists[i]['showDate'] = false;
                        break;
                      }
                    }
                  }
                }
                this.chatLists=chatLists.concat(this.chatLists);
                this.chatlike = res.data.params.chatlike;
                //chatlike push --------start----------
                for (let k = 0; k < this.chatLists.length;k++) {
                  for(let f = 0; f< this.chatlike.length; f++){
                    let messageIdArray = this.chatlike[f].message_id.split(",");
                    for (let s = 0; s < messageIdArray.length; s++) {
                      if (messageIdArray[s] == this.chatLists[k].id) {
                        if(!this.userInLikeList(this.chatlike[f].user_id,this.chatLists[k])){
                          let user= [];
                          user.user_id=this.chatlike[f].user_id;
                          user.name=this.chatlike[f].name;
                          user.file=this.chatlike[f].file;
                          this.chatLists[k].like.push(user);
                        }
                        break;
                      }
                    }
                  }
                }
                //chatlike push --------end----------
                this.adminArea = res.data.params.adminArea;
                if(this.chatLists.length > 0){
                  this.scrollId=this.chatLists[chatLists.length - 1].id;
                  document.getElementById('chatLists').addEventListener('scroll', this.handleScrollx);
                }
                if (!this.memberObj.group.parent_id) {
                  this.adminArea = true;
                }
                if (this.chatJumpObj) {
                  this.jumpToChatMsg();
                }
                if (this.user.room) {
                  this.user.room = '';
                  this.leave();
                }
              this.join();
              this.showGroupUser();
            }
          }
          loading.close();
        }).catch(err => {
          console.warn(err);
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      }
      },
      userInLikeList(userId,chatListsItem){
        if(chatListsItem.like){
          for (let i = 0; i < chatListsItem.like.length; i++) {
            if(chatListsItem.like[i].user_id === userId){
              return true;
            }
          }
        }
        return false;
      },
      fetchChatPersonList(groupId = 0) {
        if (this.user.room) {
          this.user.room = '';
          this.member.groupId = '';
          this.leave();
        }
        
        this.chatLists = [];
        this.UnreadCount = null;
        this.chatPerson = null;
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        let url = "/api/getChatPersonList";
        // 一回の取得数量
        this.contactScrollIndex = process.env.MIX_PAGE_SIZE_CHAT_CONTACT? process.env.MIX_PAGE_SIZE_CHAT_CONTACT : 100;
        // グループある場合、全部のデータ取得
        if (groupId) {
          this.contactScrollIndex = 0;
        }
        let data = {'size': this.contactScrollIndex};
        this.contactScrollLoading = true;
        axios.post(url, data).then(res => {
          if (res.data.chatList && res.data.chatList.length > 0) {
            this.chatPerson = res.data.chatList;
            this.UnreadCount = res.data.lastReadMsg;
            // 最後のデータではない
            this.contactNoMoreData = false;
          } else {
              // 最後のデータ
              this.contactNoMoreData = true;
          }
          if (groupId) {
            // グループ指定の場合、全部のデータ取得のため
            this.contactNoMoreData = true;
            for (let i = 0; i < this.chatPerson.length; i++) {
              let person = this.chatPerson[i];
              if (person.group_id == groupId) {
                this.fetchMember(person);
              }
            }
          } else {
            this.linkGroupCurrent = null;
          }

          this.contactScrollLoading = false;
          loading.close();
        }).catch(err => {
          console.warn(err);
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });

      },
      fetchMember(data,type) {
        this.$emit('clearAllSearchWord');
        if (data.group_id === this.member.groupId) {
          if (this.chatJumpObj) {
            this.jumpToChatMsg();
          }
          return;
        }
        this.fileArr = [];
        this.fileList = [];
        if (!window.videoChat){
          this.showVideoType=false;
          this.showAudioType=false;
          this.showJoinMsg = false;
          this.joinMsg = '';
        }
        this.updateMsgFlag = false;
        if (type){
          // this.chatJumpObj = null;
          this.$emit('chatJumpObj',null);
        }
        this.delLastRead(data.group_id);
        this.showChatFlag = true;
        this.member.project=data.project;
        if (this.showVideoTabFlag) {
          this.leaveRoom(2);
          this.showVideoTabFlag = false;
        }
        this.showChatFlag = false;
        $("#tab-linkGroup-" + data.group_id).addClass("is-active");
        if (this.member.groupId !== '') {
          $("#tab-linkGroup-" + this.member.groupId).removeClass("is-active");
        }
        this.clearMessage();
        this.startVideoFlag = false;
        this.groupMemberShowFlag = false;
        this.videoPicName = '';
        this.showChatFlag = true;
        this.msgIsQuote = false;
        this.oldChatClickIndex = 0;
        this.openVideoFirst = true;
        this.showTaskFlag = false;

        this.memberObj = data;
        let parentId = 0;
        if (data.parent) {
          parentId = data.parent.id;
        }
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        if (this.memberObj.group.kind === 1) {
          this.member.userClearFlag = false;
          axios.get("/api/getEnterpriseName", {
            params: {
              enterpriseId: this.memberObj.account.enterprise_id
            }
          }, headers).then(res => {
            if (res.data.result === 0) {
              this.member.id = this.memberObj.user_id;
              this.member.userName = this.memberObj.account.name;
              this.member.photo = this.memberObj.account.file;
              if (res.data.model) {
                this.member.enterpriseName = res.data.params.name;
              } else {
                this.member.enterpriseName = '';
              }
              this.member.groupId = this.memberObj.group_id;
              this.member.kind = 1;
              this.member.parentId = null;
              this.receiveMemberMsg(this.memberObj.group_id, parentId);
            }
          }).catch(err => {
            console.warn(err);
          });
        } else if (this.memberObj.group.kind === 0) {
          this.member.userName = this.memberObj.group.name;
          this.member.photo = this.memberObj.group.file;
          this.member.enterpriseName = "";
          this.member.groupId = this.memberObj.group_id;
          this.member.kind = 0;
          this.member.parentId = this.memberObj.group.parent_id;
          if (this.memberObj.group.parent_id == null) {
            axios.get('/api/checkUserClearGroup', {
              params: {
                groupId: this.member.groupId,
              }
            }, headers).then(res => {
              if (res.data.result === 0) {
                if (res.data.params === 1) {
                  this.member.userClearFlag = true
                } else if (res.data.params === 2) {
                  this.member.userClearFlag = false;
                }
              }
            }).catch(err => {
              console.warn(err);
            });
          } else {
            this.member.userClearFlag = true
          }
          this.receiveMemberMsg(this.memberObj.group_id, parentId);
        }
      },
      //空かどうかを確認
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
      },
      imgChange(file) {
        this.validateMessage = this.imageValidate(file);
        if (!this.validateMessage) {
          this.imgFile = file.raw;
          this.imageUrl = URL.createObjectURL(file.raw);
        }
      },
      imageValidate: function (file, type = 0) {
        let error = '';
        if (!type){
          error = this.imageType(file);
          if (error) {
            return error;
          }
          error = this.imageSize(file, APP_IMAGE_SIZE_LIMIT);
          if (error) {
            return error;
          }
        }else {
          error = this.fileSize(file, APP_IMAGE_SIZE_LIMIT);
          if (error) {
            return error;
          }
        }
      },
      beforeAvatarUpload(file){
        let error = this.imageValidate(file,1);
        this.$alert(error, {showClose: false});
        return false;
      },
      handleRemove(file, fileList) {
          for (let i=0;i<this.fileList.length;i++){
            if(this.fileList[i].name === file.name){
                this.fileArr.splice(i,1);
                this.fileList.splice(i,1);
                break;
            }
        }
        this.quoteFileName = null;
        this.rawFile = null;

        if(fileList.length > 4){
          this.updated_filecount = 4;
        }else{
          this.updated_filecount = fileList.length;
        }
      },
      //【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 begin
      handleChange(file, fileList) {
        if (file.raw.type.indexOf("image") === -1) {
          file.url = 'images/icon-file40.png';
        }
        if (fileList.length){
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let data = new FormData();
          let headers = {headers: {"Content-Type": "multipart/form-data"}};
          data.append('group_id', this.memberObj.group_id);
          // #3112 HEIC画像を圧縮せずに送信してください
          var fileName = file.raw.name;
          var fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1);
          fileExtension = fileExtension.toUpperCase();
          if (file.raw.type.indexOf("image") !== -1 && file.size / 1024 / 1024 > this.imageLimitSize && fileExtension != 'HEIC') {
            if(!this.isChoice){
              this.isCompress = confirm(this.commonMessage.compressImage.confirmMessage);
              this.isChoice = true
            }
            if(this.isCompress){
              data.append('file', file.raw, this.formatFileName(file.raw.name));
              this.uploadFileToServer(data, headers, loading, fileList)
            }else{
              compressImage(file.raw, this.imgQuality).then((resfile) => {
                data.append('file', resfile, this.formatFileName(file.raw.name));
              }).then(() => {
                this.uploadFileToServer(data, headers, loading, fileList)
              })
            }
          }else{
            data.append('file', file.raw, this.formatFileName(file.raw.name));
            this.uploadFileToServer(data, headers, loading, fileList)
          }
        }
      },
      uploadFileToServer(data, headers, loading, fileList){
        axios.post("/api/uploadFiles", data, headers)
              .then(res => {
                if (!res.data.result && res.data.params) {
                  this.fileArr.push(res.data.params.fileName);
                  let arr = new Array();
                  arr['name'] =res.data.params.fileName.substring(14)+"\xa0\xa0\xa0\xa0("+res.data.params.fileSize.toLocaleString()+"KB)";
                  this.fileList.push(arr);
                  if(this.updated_filecount < 4){
                    this.updated_filecount++;
                  }
                }else{
                  loading.close();
                  let errMessage = this.commonMessage.error.upload;
                  this.$alert(errMessage, {showClose: false});
                }
                if (this.fileArr.length === fileList.length){
                  loading.close();
                  this.isChoice = false
                }
              }).catch(err => {
            console.warn(err);
            loading.close();
            let errMessage = this.commonMessage.error.upload;
            this.$alert(errMessage, {showClose: false});
          })
      },
      //【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 end
      formatFileName(fileName){
        fileName = fileName.replace(new RegExp(/[,]/, "mg"), '_');
        return fileName;
      },
      delPerson(id) {
        if (this.user.room) {
          this.user.room = '';
          this.leave();
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.delete;
        axios.post('/api/delChatList', {
          id: id
        }).then(res => {
          this.visibled = false;
          this.delPersonComment(id);
          loading.close();
          this.showChatFlag = false;
        }).catch(err => {
          console.warn(err);
          loading.close();
          this.$alert(errMessage, {showClose: false});
        })
      },
      delPersonComment(groupId) {
        if (groupId) {
          for (let i = 0; i < this.chatPerson.length; i++) {
            let person = this.chatPerson[i];
            if (person.group_id == groupId) {
              this.chatPerson.splice(i, 1);
            }
          }
        }
      },
      topPerson(id) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        let url = '/api/topChatList';
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        data.append('id', id);
        axios.post(url, data, headers).then(res => {
          this.visibled = false;
          this.fetchChatPersonList();
          loading.close();
        }).catch(err => {
          console.warn(err);
          loading.close();
          this.$alert(errMessage, {showClose: false});
        })
      },
      showRightModel(index) {
        //ポップアップイベント
        if (!this.visibled) this.visibled = true;
        this.current = index;
      },
      setGroup: function (id, g) {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$emit('setGroupFn', id, g, this.chatSetGroup.inputPopover, this.imgFile);
            this.$refs['form'].resetFields();
            this.$refs['popover-' + id].doClose();
            this.chatSetGroup.inputPopover = null;
            this.checked.checkGroup = [];
            this.checked.checkParticipant = [];
            this.checked.checkGroupPerson = [];
            this.searchGroupPerson = null;
            this.validateMessage = '';
          } else {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        })
      },
      //ユーザー検索
      fetchSearchPerson: function () {
        this.$refs['form'].resetFields();
        this.imgFile = null;
        this.imageUrl = null;
        this.chatSetGroup.inputPopover = null;
        this.checked.checkGroup = [];
        this.checked.checkParticipant = [];
        this.checked.checkGroupPerson = [];
        this.searchGroupPerson = null;
        this.validateMessage = '';
        if (!this.visibled) this.visibled = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        axios.post('/api/getSearchPersonList').then((res) => {
          this.searchPerson.searchEnterprise = res.data.enterprise;
          this.searchPerson.searchParticipant = res.data.participants;
          this.searchPerson.searchGroup = res.data.group;
        }).then(res => {
          loading.close();
        }).catch(err => {
          console.warn(err);
          loading.close();
          this.$alert(errMessage, {showClose: false});
        })
      },
      delLastRead(gId) {
        for (let v = 0; v < this.UnreadCount.length; v++) {
          if (this.UnreadCount[v].group_id === gId) {
            this.UnreadCount[v].num = 0;
          }
        }
      },
      async doConnect() {
        let user = {};
        let _user_info = sessionStorage.getItem('_user_info');
        if (_user_info) {
          user = JSON.parse(_user_info);
        }else{
          //#3614 Solve the problem of missing user information in asynchronous loading

          if (!this.user || !this.user.name) {
            const res = await axios.get('/api/getEnterprisesList')
            let data = res.data.user[0];
            user = data;

            this.user.name = data.name;
            this.user.noticeCount = res.data.noticeCount;
            if (data.file) {


              this.user.file = 'file/users/' + data.file;
              this.user.localFile = data.file;
            }
            this.user.id = data.id;
            this.user.enterprise_admin = data.enterprise_admin;
            this.user.enterprise_id = data.enterprise_id;
            this.user.enterpriseName = '';
            if (data.enterprise_id) {
              this.user.enterpriseName = data.enterprise.name
            } else {
              this.user.enterpriseName = data.enterprise_coop.name
            }
            sessionStorage.setItem('_user_info', JSON.stringify(this.user));

          }
        }
        this.mineData = user;
        this.userSelf = user;
        this.userSelf.file = user.localFile;
        this.user = {
          room: '',
          id: this.mineData.id,
          name: this.mineData.name,
        };
        let name = this.mineData.enterpriseName;
        const socketUri = process.env.MIX_SOCKETIO_SERVER;
        let self = this;
        if (socketUri) {
          this.socket = window.socket;
          setTimeout(function () {
            if(!this.socket || (this.socket && (this.socket.connected == false))){
              alert('Soket接続に失敗しました');
            }
          }, 1000);
          let $this = this;
          // メッセージ受信
          this.socket.on('chat.message', function (chatMsg) {
            // socket chatとchat案件判断
            if(self.activeFlag !== 'activeFirst'){
              return;
            }

            let msgObj = JSON.parse(chatMsg);

            if (msgObj.from_group_id && msgObj.from_group_id === self.member.groupId) {
              return false;
            }
            for (let i = 0; i < self.groupUsers.length; i++) {
                if(self.groupUsers[i].user_id == msgObj.removeId) {
                    self.groupUsers.splice(i,1);
                }
            }
            //チャットから離れる
            if (msgObj.removeId && msgObj.removeId === self.userSelf.id) {
              //チャットを閉じる
              self.leave();//leave this chat room
              //remove this group from list
              self.deleteGroupUnused(self.member.groupId);
              self.showChatFlag = false;//if this par is setting to false,the chat list will be closed.
            }
            //remove somebody else and refresh readstatus
            if (msgObj.removeId && msgObj.removeId !== self.userSelf.id) {
              //remove user from this group
              for(let i = 0; i < self.groupUsers.length; i++) {
                if(self.groupUsers[i].user_id === msgObj.removeId) {
                  self.groupUsers.slice(i, 1);
                }
              }
              //remove this user from message's readStatusArr
              for(let i = 0; i < self.lastReads.length; i++) {
                if(self.lastReads[i]['userId'] === msgObj.removeId) {
                  self.lastReads.slice(i, 1);
                }
              }
              //refresh readstatus
              for(let i=0;i<self.chatLists.length;i++) {
                  self.chatLists[i].readUsers = self.checkReadStatus(self.chatLists[i].id , self.lastReads, self.groupUsers, self.chatLists[i].from_user_id);
              }

              //remove chatlike
              for(let i = 0;i < self.chatLists.length; i++){
                for (let k = 0; k < self.chatLists[i].like.length; k++) {
                  if (self.chatLists[i].like[k].user_id == msgObj.removeId) {
                    self.chatLists[i].like.splice(k,1);
                  }
                }
              }
            }
            if (msgObj.msg !== undefined) {
              //HTMLタグの処理
              msgObj.msg = self.HTMLEncode(msgObj.msg);
              //#2645 turn url into hyperLinks
              msgObj.msg = self.hyperLinks(msgObj.msg);
            }
            if (msgObj.mid === 0) {
              let date = new Date();
              let length = self.chatLists.length;
              let dateShowFlag = false;
              let nowDate = date.getFullYear() + "-" + (Array(2).join('0') + (date.getMonth() + 1)).slice(-2) + "-" + (Array(2).join('0') + (date.getDate())).slice(-2);
              let createdAt = nowDate + ' ' + (Array(2).join('0') + date.getHours()).slice(-2) + ':' + (Array(2).join('0') + date.getMinutes()).slice(-2);
              if (length === 0) {
                dateShowFlag = true;
              } else {
                let beforeDate = self.chatLists[length - 1].created_at.split(' ')[0];
                if (beforeDate !== nowDate) {
                  dateShowFlag = true;
                }
              }
              if (msgObj.vedio) {
                if (self.startVideoGroupIdArr.indexOf(self.member.groupId) === -1) {
                  self.startVideoGroupIdArr.push(self.member.groupId);
                }
              }
              let imgName = null;
              if (msgObj.file) {
                imgName = msgObj.file.split(',')[0];
              }
              if (self.user.id === msgObj.id) {
                let date = new Date();
                let chatItem = {
                  created_at: createdAt,
                  file_name: msgObj.file,
                  from_user_id: self.user.id,
                  group_id: self.member.groupId,
                  id: msgObj.maxid,
                  fileSize: '',
                  message: msgObj.msg,
                  readUsers: [],
                  isClicked: false,
                  isQuote: self.msgIsQuote,
                  updated_at: createdAt,
                  showDate: dateShowFlag,
                  user: {
                    id: self.userSelf.id,
                    name: self.userSelf.name,
                    file: self.userSelf.file,
                    enterprise: {
                      name: name,
                    }
                  },
                  like:[],
                };
                if(msgObj.id == self.userSelf.id){
                  self.setId = 0;
                }
                self.chatLists.push(chatItem);
              } else {
                axios.get("/api/getUser", {
                  params: {
                    id: msgObj.id,
                  }
                }).then(res => {
                  let user = res.data;
                  let chatItem = {
                    created_at: createdAt,
                    file_name: msgObj.file,
                    fileSize: '',
                    from_user_id: user.id,
                    group_id: self.member.groupId,
                    id: msgObj.maxid,
                    message: msgObj.msg,
                    readUsers: [],
                    isClicked: false,
                    isQuote: self.msgIsQuote,
                    updated_at: createdAt,
                    showDate: dateShowFlag,
                    user: {
                      id: user.id,
                      name: user.name,
                      file: user.file,
                      enterprise: {
                        name: name,
                      }
                    },
                    like:[],
                  };
                  if(msgObj.id == self.userSelf.id){
                    self.setId = 0;
                  }
                  self.chatLists.push(chatItem);
                  axios.post("/api/updateLastRead", {
                    id: self.user.id,
                    groupId: self.member.groupId,
                    messageId: msgObj.maxid,
                  }).then(res => {
                    //send readStatus socket message
                    //chat.send.message.ack
                    let readStatusTmp = {
                      user_id: self.userSelf.id,
                      message_id: res.data.messageId,
                      file: self.userSelf.file ? self.userSelf.file : '',
                      name: self.userSelf.name
                    };
                    self.socket.emit("chat.send.message.ack", readStatusTmp);
                  }).catch(err => {
                    console.warn(err);
                  });
                }).catch(err => {
                  console.warn(err);
                });
              }
              self.msgIsQuote = false;
            } else {
              if(msgObj.id == self.userSelf.id){
                self.setId = 0;
              }
              self.chatLists = self.chatLists.filter(item => {
                if (item.id === msgObj.mid) {
                  item.message = msgObj.msg
                }
                return item;
              });
            }
          });
          this.socket.on('chat.touch.xy', function (chatMsg) {
            if(self.activeFlag !== 'activeFirst'){
              return;
            }
            let msgObj = JSON.parse(chatMsg);
            if (self.user.id !== msgObj.id) {
              let videoImage = document.getElementById("videoImage");
              //check if transverse event
              if (msgObj.x < 0 && msgObj.y < 0) {
                //transverse event
                if (msgObj.enlarge) {
                  self.isTransverse = true; //transverse mode
                  self.isdrawCircle=false;
                } else {
                  self.isdrawCircle=false;
                  self.isTransverse = false; //exit transverse mode
                }
                self.resizeImage();
              } else {
                self.drawCircleOnSocket(msgObj);
              }
            }
          });
          this.socket.on('chat.file', function (chatMsg) {
            if(self.activeFlag !== 'activeFirst'){
              return;
            }
            let msgObj = JSON.parse(chatMsg);
            self.showPic = false;
            self.isdrawCircle = false;
            // reset image info when upload new file
            self.isTransverse = false;
            self.videoPicName = 'file/' + self.member.groupId + '/' + encodeURIComponent(msgObj.file);
            self.downloadChatFile = self.member.groupId + '/' + encodeURIComponent(msgObj.file);
            if (msgObj.pdfFile) {
              self.downloadChatFile = self.member.groupId + '/' + encodeURIComponent(msgObj.pdfFile);
            }
            self.hasShowFile = false; //active the download button
            self.pdfFileName = msgObj.pdfFile;
            self.pdfPageTotal = msgObj.pageCount;

            let pdfPageCount = self.pdfPageTotal;
            let pageNow = msgObj.pageNow;
            self.pdfPageNow = pageNow;
            if (pageNow < pdfPageCount) {
              self.pdfPageNext = true;
            } else {
              self.pdfPageNext = false;
            }
            if (pageNow > 1) {
              self.pdfPagePrev = true;
            } else {
              self.pdfPagePrev = false;
            }

            let imgObj = self.$refs.videoImage;
            imgObj.onload = function () {
              self.showPic = true;
              self.resizeImage();
            }
          });
          // 更新ユーザー情報
          this.socket.on('chat.users', function (nicknames) {
            let joinFlag = false;
            for (let i = 0;i < Object.keys(nicknames).length;i++){
              if(Object.keys(nicknames)[i] !== self.member.id &&
                nicknames[Object.keys(nicknames)[i]].status){
                if (nicknames[Object.keys(nicknames)[i]].status === 'audio'){
                  // #2808 1対1のチャットボタンが初期状態に戻ります
                  if(self.member.kind == 1) {
                    self.showAudioType = false;
                  }else{
                    self.showAudioType = true;
                  }
                  self.showVideoType = false;
                  self.joinMsg = '参加';
                  joinFlag = true;
                  self.showJoinMsg = true;
                } else if(nicknames[Object.keys(nicknames)[i]].status === 'vedio'){
                  //#2808  1対1のチャットボタンが初期状態に戻ります
                  if(self.member.kind == 1) {
                    self.showVideoType = false;
                  }else{
                    self.showVideoType = true;
                  }
                  self.showAudioType = false;
                  self.joinMsg = '参加';
                  joinFlag = true;
                  self.showJoinMsg = true;
                }
              }
            }
            if (!joinFlag && self.joinMsg === '参加'){
              self.joinMsg = '終了';
              self.showJoinMsg = true;
            }
          });
          this.socket.on('chat.receive.call', function (nicknames){
            if (!self.member.kind){
              if (nicknames === 'audio'){
                self.showAudioType = true;
                self.showVideoType = false;
                self.joinMsg = '参加';
                self.showJoinMsg = true;
              } else if(nicknames === 'vedio'){
                self.showVideoType = true;
                self.showAudioType = false;
                self.joinMsg = '参加';
                self.showJoinMsg = true;
              }
            }
          });
          // メッセージ削除受信
          this.socket.on('chat.delete', function (chatMsg) {
            let msgObj = JSON.parse(chatMsg);
            self.chatLists = self.chatLists.filter(item => item.id !== msgObj.mid);
          });
          // ユーザー削除受信
          this.socket.on('chat.deleteUser', function (chatMsg) {
            let msgObj = JSON.parse(chatMsg);
            let length = self.chatPerson.length;
            for (let i = 0; i < length; i++) {
              if (self.chatPerson[i].group_id === self.member.groupId) {
                self.chatPerson.splice(i, 1);
                break;
              }
            }
            self.chatLists.splice(msgObj.mid, 1);
          });
          // rejection
          this.socket.on('chat.reject', function (chatMsg) {
            let msgObj = JSON.parse(chatMsg);
          });
          // read status changed
          // event chat.message.ack
          this.socket.on('chat.message.ack', function (chatMsg) {
            let msgObj = JSON.parse(chatMsg);
            let fromUserId = msgObj.user_id;
            let lastRead = msgObj.message_id;
            let userFile = msgObj.file;
            let userName = msgObj.name;
            // refresh read status
            let newUser = true; //check if new user (lastRead Status check)
            for(let i = 0; i < self.lastReads.length; i++) {
              if(self.lastReads[i]['userId'] === fromUserId) {
                self.lastReads[i]['lastMessageId'] = lastRead; //update lastRead record
                newUser = false;
              }
            }
            // new user will be inserted
            if(newUser) {
              //insert
              let statusTmp = [];
              statusTmp['userId'] = fromUserId;
              statusTmp['lastMessageId'] = lastRead;
              self.lastReads.push(statusTmp);
            }
            //refresh chatMessage read status
            for(let i=0;i<self.chatLists.length;i++) {
              self.chatLists[i].readUsers = self.checkReadStatus(self.chatLists[i].id , self.lastReads, self.groupUsers, self.chatLists[i].from_user_id, self.userSelf.id);
            }
          });
          this.socket.on('chat.like', function (chatMsg){
            let msgObj = JSON.parse(chatMsg);
            if (msgObj.cancel == 1) {
              //success
              for(let i = 0;i < self.chatLists.length; i++){
                if (self.chatLists[i].id == msgObj.message_id) {
                  let userArr=[];
                  userArr.user_id = msgObj.user_id;
                  userArr.name=msgObj.name;
                  userArr.file=msgObj.file;

                  self.chatLists[i].like.push(userArr);
                  if(self.chatLists[i].isClicked){
                      self.chatLists[i].isClicked=false;
                  }else{
                      self.chatLists[i].isClicked=true;
                      self.chatLists[i].isClicked=false;
                  }
                }
              }
            } else {
              //cancel === 0
              for(let i = 0;i < self.chatLists.length; i++){
                if (self.chatLists[i].id == msgObj.message_id) {
                  for (let j = 0; j< self.chatLists[i].like.length;j++) {
                    if (self.chatLists[i].like[j].user_id == msgObj.user_id) {
                      self.chatLists[i].like.splice(j,1);
                      if(self.chatLists[i].isClicked){
                          self.chatLists[i].isClicked=false;
                      }else{
                          self.chatLists[i].isClicked=true;
                          self.chatLists[i].isClicked=false;
                      }
                      break;
                    }
                  }
                }
              }
            }
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
      },
      videoConn(type,linkFlag = 0) {
        this.getUserMedia(type);
        let peerId = 'peerID_' + this.member.groupId + '_' + this.user.id + '_' + this.getUuid();
        let data = {peerId: peerId, sessionToken: getCookieValue('XSRF-TOKEN')};
        axios.post('/api/skyway/authenticate', data).then((res) => {
          this.peer = new Peer(peerId, {
            key: process.env.MIX_APP_SKYWAY_KEY,
            credential: res.data
          });
          // シグナリングサーバーに正常に接続できた時
          this.peer.on('open', () => {
            this.peerId = this.peer.id;
            this.joinRoom();
            // CALLのpush通知
            if (!this.isVideoChat) {
              if (!linkFlag) {
                this.pushCall('join');
              }
              this.socket.emit("chat.call", "audio");
            } else {
              if (!linkFlag) {
                this.pushCall('video_join');
              }
              this.socket.emit("chat.call", "vedio");
            }
          });
          // Peerに対する全ての接続を終了した時
          this.peer.on('close', () => console.log('peer close'));

          this.peer.on('error', err => console.log(err));
        }).catch(err => {
          console.warn(err);
          let errMessage = this.commonMessage.error.system;
          this.$alert(errMessage, {showClose: false});
        });
        this.peerId = null;
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
        }).catch(err => {
          console.warn(err);
        });
      },
      pushCall: function (status) {
        let data = {group_id: this.member.groupId, call_status: status};
        axios.post('/api/pushChatCall', data).then((res) => {
        }).catch(err => {
          console.warn(err);
          let errMessage = this.commonMessage.error.networkError;
          this.$alert(errMessage, {showClose: false});
        });
      },
      joinRoom: function () {
        if (!this.peer.open) {
          return;
        }
        const remoteVideos = document.getElementById('remote-videos');
        const onlyOneVideo = document.getElementById('onlyOneVideo');
        this.room = this.peer.joinRoom(this.roomName + this.member.groupId, {
          mode: 'mesh',
          stream: this.localStream,
          videoBandwidth: 10240,
          audioBandwidth: 320,
        });
        this.room.once('open', () => {
          this.messages += '=== You joined ===<br/>';
        });
        this.room.on('peerJoin', peerId => {
          this.messages += `=== ${peerId} joined ===<br/>`;
        });

        // Render remote stream for new peer join in the room
        this.room.on('stream', async stream => {
          this.videoArr.push(stream);
          this.messages += '=== stream joined ===<br/>';
          let length = this.videoArr.length;
          const newVideo = document.createElement('video');
          newVideo.srcObject = stream;
          newVideo.playsInline = true;
          newVideo.autoplay = true;
          newVideo.id = stream.peerId;
          remoteVideos.append(newVideo);
          onlyOneVideo.srcObject = null;
          if (length <= 1) {
            this.oneVideoObj = stream;
            onlyOneVideo.srcObject = stream;
          }
        });

        // for closing room members
        this.room.on('peerLeave', peerId => {
          for (let i = 0; i < this.videoArr.length; i++) {
            if (this.videoArr[i].peerId === peerId) {
              this.videoArr.splice(i, 1);
              break;
            }
          }
          const remoteVideo = document.getElementById(peerId);
          if (remoteVideo) {
            remoteVideo.srcObject.getTracks().forEach(track => track.stop());
            remoteVideo.srcObject = null;
            remoteVideo.remove();
          }
          if (this.videoArr.length <= 1) {
            onlyOneVideo.srcObject = this.videoArr[0];
          }
          if (this.videoArr.length < 1) {
            //ビデオ通話はユーザーがいない時に自動的に終了します
            this.leaveRoom(2);
          }
          this.messages += `=== ${peerId} left ===<br/>`;
        });

        // for closing myself
        this.room.once('close', () => {
          this.videoArr = [];
          this.messages += `== You left ===<br/>`;
          Array.from(remoteVideos.children).forEach(remoteVideo => {
            if (remoteVideo) {
              remoteVideo.srcObject.getTracks().forEach(track => track.stop());
              remoteVideo.srcObject = null;
              remoteVideo.remove();
            }
          });
        });
      },
      leaveRoom(type) {
        if (type === 2) {
          this.startVideoFlag = false;
          let index = this.startVideoGroupIdArr.indexOf(this.member.groupId);
          if (index > -1) {
            this.startVideoGroupIdArr.splice(index, 1);
          }
        }
        this.closeCamera();
        this.showVideoTabFlag = false;
        this.openVideoFirst = true;
        this.showPic = false;
        this.isdrawCircle = false;
        this.pdfFileName = '';
        this.pdfPageTotal = 0;
        this.pdfPageNow = 0;
        this.pdfPageNext = false;
        this.pdfPagePrev = false;
        this.socket.emit("chat.call.hangup", this.user.id);
        // web→アプリで二回目の通話をかけた際に繋がらない　＃3197 start
        this.socket.emit("leave", 'leaveTest', (response) => { // add callBack function
          this.join(); //No need to check status,re-join room operation is required
        });
        // #3197 end
        if (this.room) {
          this.room.close();
          this.room = null;
          // push離脱通知送信
          if (!this.isVideoChat) {
            this.pushCall('leave');
          } else {
            this.pushCall('video_leave');
          }
        }
      },
      getUserMedia(type) {
        return new Promise((resolve, reject) => {
          if (type === 1) {
            this.isVideoChat = false;
          } else {
            this.isVideoChat = true;
          }
          Vue.nextTick(() => {
            const constraints = {
              audio: true,
              video: this.isVideoChat,
            };
            let localVideo = document.getElementById('my-video');
            if (localVideo&& !localVideo.srcObject) {
              navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
                localVideo.muted = true; // mute
                localVideo.srcObject = stream;
                this.localStream = stream;
                if (type === 1) {
                  this.showSound = true;
                  this.showVideo = false;
                  this.videoIsDisabled = true;
                  this.soundIsDisabled = false;
                  this.picModalIsDisabled = false;
                  this.showPic = false;
                  this.showVideoFlag = false;
                } else {
                  this.showSound = true;
                  this.showVideo = true;
                  this.videoIsDisabled = false;
                  this.soundIsDisabled = false;
                  this.picModalIsDisabled = true;
                  this.showPic = false;
                  this.showVideoFlag = true;
                }
                resolve();
              }).catch((err) => {
                //close chat modal
                this.showVideoTabFlag = false;
                reject();
                // if (err.name === 'NotFoundError') {
                //   let errMessage = this.commonMessage.error.mediaDevice;
                //   this.$alert(errMessage + 'NotFoundError', {showClose: false});
                // } else if (err.name === 'NotAllowedError') {
                //   let errMessage = this.commonMessage.error.mediaDevice;
                //   this.$alert(errMessage + 'NotAllowedError', {showClose: false});
                // } else if (err.name === 'SecurityError') {
                //   let errMessage = this.commonMessage.error.mediaDevice;
                //   this.$alert(errMessage + 'SecurityError', {showClose: false});
                // } else {
                //   let errMessage = this.commonMessage.error.mediaDevice;
                //   this.$alert(errMessage + 'default', {showClose: false});
                // }
                let errMessage = this.commonMessage.error.mediaDevice;
                this.$alert(errMessage, {showClose: false});
              })
            }
          });
        });
      },
      closeCamera() {
        if (this.localStream) {
          this.localStream.getTracks().forEach(track => track.stop());
        }
        let localVideo = document.getElementById('my-video');
        if (localVideo && localVideo.srcObject) {
          localVideo.srcObject.getTracks().forEach(track => track.stop());
          localVideo.srcObject = null;
          this.localStream = null;
        }
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

      getEmojiPath(index) {
        let emojiIndex = index + 1;
        if (emojiIndex.toString().length < 2) {
          emojiIndex = '00' + emojiIndex;
        } else {
          emojiIndex = '0' + emojiIndex;
        }
        return emojiIndex;
      },
      forwardMsg(chatItem){
        let file_name = null;
        if (chatItem.file_name) {
          file_name = chatItem.file_name.substring(chatItem.file_name.lastIndexOf('/') + 1, chatItem.file_name.length);
        }
        this.chatFileName = file_name;
        this.sendMsg = this.HTMLDecode(chatItem.message);
        this.sendMsgPure = chatItem.message;
        this.showForwardModal = true;
      },
      like(chatItem){
        let errMessage = this.commonMessage.error.system;
        // const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/chatLike', {
          message_id: chatItem.id,
          group_id: chatItem.group_id,
        }).then((res) => {
          let _user_info = sessionStorage.getItem('_user_info');
          let user = JSON.parse(_user_info);
          let userTmp={};
          userTmp.message_id=chatItem.id;
          userTmp.user_id=user.id;
          userTmp.name=user.name;
          if (user.localFile) {
            userTmp.file=user.localFile;
          } else {
            userTmp.file='';
          }

          if (res.data == 'success' || res.data == 1) {
            //いいね
            userTmp.cancel = 1;
          } else {
            //いいねを取り消す
            userTmp.cancel = 0;
          }
          this.socket.emit("chat.send.like", userTmp);
        }).catch((err) => {
          this.$alert(errMessage, {showClose: false});
        });
      },
      closeForwardModal(groupId = 0, groupName = '') {
        let msgPrev = this.sendMsg;
        let msgPure = this.sendMsgPure;
        let groupIdPrev = this.member.groupId;
        let chatFileNameTmp = this.chatFileName;
        if (groupId > 0) {
          this.forwardGroupId = groupId;
          //send to another group
          let forwardPrefix = '▼ From:' + this.member.userName + '\n';
          this.sendMsg = this.HTMLDecode(forwardPrefix + msgPure);
          this.member.groupId = groupId;
          let data = new FormData();
          let headers = {headers: {"Content-Type": "multipart/form-data"}};
          data.append('group_id', JSON.stringify(this.member.groupId));
          if (this.rawFile) {
            data.append('fileName',
                this.rawFile,this.formatFileName(this.rawFile.name));
            data.append("file", this.rawFile,this.formatFileName(this.rawFile.name));
          }
          if (this.chatFileName) {
            data.append('fileName', JSON.stringify(this.chatFileName));
          }
          data.append('message', JSON.stringify(this.sendMsg));
          data.append('toUserId', JSON.stringify(this.toUserId));
          data.append('toMessageId', JSON.stringify(this.toMessageId));
          data.append('isToOrRe', JSON.stringify(this.isToOrRe));
          data.append('toIdArr', JSON.stringify(this.toIdArr));
          data.append('toNameArr', JSON.stringify(this.toNameArr));
          data.append('from_group_id', JSON.stringify(groupIdPrev));
          data.append('forwardFileName', JSON.stringify(this.chatFileName));
          let errMessage = this.commonMessage.error.chatMsgSend;
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post("/api/setChatMessage", data, headers)
              .then(res => {
                if (res.data.result === 0) {
                  //leaveRoom
                  this.socket.emit("leave", this.user.room);
                  //joinRoom
                  this.user.room = 'group_' + this.member.groupId;
                  this.socket.emit("join", this.user);
                  this.sendMsg = forwardPrefix + msgPrev;
                  this.sendForwardMsg(res.data.params, groupIdPrev, this.member.groupId);
                  //leaveRoom
                  this.socket.emit("leave", this.user.room);
                }else{
                    this.$alert(res.data.errors, {showClose: false});
                }
              }).then(res => {
            //send to this group
            //joinRoom
            this.member.groupId = groupIdPrev;
            this.user.room = 'group_' + this.member.groupId;
            this.socket.emit("join", this.user);
            let forwardSelfPrefix = '▼ Fw:' + groupName + '\n';
            this.sendMsg = forwardSelfPrefix + msgPrev;
            this.chatFileName = chatFileNameTmp;
            this.sendMessage(1);
            loading.close();
          }).catch(err => {
            console.warn(err);
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
        }
        this.sendMsg = '';
        this.sendMsgPure = '';
        this.chatFileName = '';
        this.showForwardModal = false;
      },
      sendForwardMsg: function (data, fromGroupId, toGroupId) {
        let msg = {
          msg: this.sendMsgPure,
          gid: this.member.groupId,
          nickname: this.user.name,
          id: this.user.id,
          maxid: data.id,
          file: data.file_name,
          fileSize: data.fileSize,
          mid: 0,
          vedio: this.startVideoFlag,
          timeStamp: (new Date()).getTime(),
          from_group_id: fromGroupId,
          to_group_id: toGroupId
        };
        this.socket.emit("chat.send.message.transfer", msg);
        this.clearMessage();
      },
      deleteGroupUnused(groupId) {
        for (let i = 0; i < this.chatPerson.length; i++) {
          if (this.chatPerson[i]['group_id'] === groupId) {
            this.chatPerson.splice(i, 1);
          }
        }
      },

      checkMessageUpdate() {
        //set window var
        if (this.member && this.member.groupId) {
          window.chatGroupNow = this.member.groupId;
        } else {
          window.chatGroupNow = 0;
        }
        if (this.member && this.member.groupId &&window.messageNewGroup == this.member.groupId) {
          this.moveItem2ArrFirst(this.chatPerson,window.messageNewGroup);
          window.messageNewGroup = 0;
        }
        //check unread messages
        if (window.messageNewGroup > 0) {
          //change this group's unread number
          let UnreadIndex = this.UnreadCount.findIndex(function (item) {
            return item.group_id === window.messageNewGroup;
          });

          if(UnreadIndex >= 0){
            this.UnreadCount[UnreadIndex].num = this.UnreadCount[UnreadIndex].num + 1;
          }else{
            let tmp = {group_id:window.messageNewGroup,num:parseInt(1)};
            this.UnreadCount.push(tmp);
          }
          //メッセージを受け取ったばかりの人やグループを第一位にします
          this.moveItem2ArrFirst(this.chatPerson,window.messageNewGroup);
          window.messageNewGroup = 0;
        }
        if (window.videoChat) {
          if (window.videoChat.callInFalg){
            this.$emit('conversion');
            return false;
          }
          if (this.chatPerson && this.chatPerson.length) {
            let middleArr = [];
            middleArr = this.chatPerson;
            this.chatPerson = [];
            this.chatPerson = middleArr;
          }
          if (this.chatLists && this.chatLists.length) {
            let middleArr = [];
            middleArr = this.chatLists;
            this.chatLists = [];
            this.chatLists = middleArr;
          }
        }
      },

      //メッセージを受け取ったばかりの人やグループを第一位にします
      moveItem2ArrFirst(arr,key){
        if (arr){
          for (let i = 0; i < arr.length; i++) {
              if (arr[i].group_id === key) {
                let array = arr[i];
                arr.splice(i, 1);
                arr.unshift(array);
                break;
              }
          }
        }
        document.getElementsByClassName("el-tabs__nav-scroll")[1].scrollTop = 0;
      },
      joinerList(videoArr){
        let arrData = videoArr;
        let arrRes = [];
        let userIdRes = []; //現在参加userのuser_id
        let res = [];

        if (arrData) {
          for (let i = 0;i<arrData.length;i++) {
            arrRes.push(arrData[i].peerId);
          }

          for (let j = 0;j<arrRes.length;j++) {
            let tmpData = arrRes[j].split("_");
            //現在参加userのuser_id add
            userIdRes.push(tmpData.splice(2,1));
          }
        }
        let groupUsersArray = Array.from(this.groupUsers);
        for (let e = 0;e<groupUsersArray.length;e++){
           for (let i = 0;i<userIdRes.length;i++) {
             if (userIdRes[i] == groupUsersArray[e].user_id){
               res.push(groupUsersArray[e]);
             }
           }
        }
        return res;
      },
      checkReadStatus(messageId, readStatus, groupUsers, fromUserId, selfId) {
        //return users read and unread
        //return data is array
        //can be count and shown with user information
        let userReadStatus = [];
        for(let i=0;i < groupUsers.length; i++) {
          for(let l=0;l < readStatus.length; l++) {
            //self's message do not count self
            if(fromUserId === selfId) {
              if(parseInt(readStatus[l]['userId']) === parseInt(groupUsers[i].user_id) && parseInt(readStatus[l]['lastMessageId']) >= parseInt(messageId) && parseInt(readStatus[l]['userId']) !== selfId) {
                let userTmp = [];
                let userArr=[];
                if(!userArr.includes(groupUsers[i].user_id)){
                    userTmp['userId'] = groupUsers[i].user_id;
                    userTmp['userName'] = groupUsers[i].user.name;
                    userTmp['userFile'] = groupUsers[i].user.file;
                    userReadStatus.push(userTmp);
                }
              }
            }else{
              if(parseInt(readStatus[l]['userId']) === parseInt(groupUsers[i].user_id) && parseInt(readStatus[l]['lastMessageId']) >= parseInt(messageId) && fromUserId !== parseInt(groupUsers[i].user_id)) {
                let userTmp = [];
                let userArr=[];
                if(!userArr.includes(groupUsers[i].user_id)) {
                    userTmp['userId'] = groupUsers[i].user_id;
                    userTmp['userName'] = groupUsers[i].user.name;
                    userTmp['userFile'] = groupUsers[i].user.file;
                    userReadStatus.push(userTmp);
                }
              }
            }
          }
        }
        return userReadStatus;
      },
      selectFilesFromDoc() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/getFileFromDoc", {groupId:this.member.groupId})
          .then(res => {
            if (res.data.flag > 0) {
              loading.close();
              for(let i =0; i<res.data.data.length; i++) {
                res.data.data[i]['fileType'] = res.data.data[i]['fileType'] ? res.data.data[i]['fileType'] : '';
                res.data.data[i]['size'] = res.data.data[i]['size'] ? res.data.data[i]['size'] : '';
              }
              this.filesDoc = res.data.data;
              this.fileSelector = true;
            }else{
              console.log('errror');
            }
          }).then(res => {

          loading.close();
        }).catch(err => {
          loading.close();
        });
      },
      closeFileSelector() {
        this.fileSelector = false;
      },
      closeFileSelectorWithSelected(filesSelected) {
        this.fileSelector = false;
        for(let i=0;i<filesSelected.length;i++) {
          this.fileArr.push(filesSelected[i].name);
          let arr = new Array();
          arr['name'] =filesSelected[i].name.substring(14)+"\xa0\xa0\xa0\xa0("+filesSelected[i].size.toLocaleString()+"KB)";
          this.fileList.push(arr);
        }
        if(this.fileList.length > 4){
          this.updated_filecount = 4;
        }else{
          this.updated_filecount = this.fileList.length;
        }
      },
      sendTransverseFlag() {
        if (this.room) {
          let transverse = 0;
          if (this.isTransverse) {
            transverse = 1;
            this.isdrawCircle=false;
          }else{
            this.isdrawCircle=false;
          }
          let msg = {
            id: this.user.id,
            x: -1,
            y: -1,
            enlarge: transverse
          };
          this.socket.emit("chat.send.touch.xy", msg);
          this.reDrawCircle();
        }
      },
      changePosition(x,y) {
        let newPosition = [y, this.originWidth - x];
        return newPosition;
      }
    },
    created() {
      let that = this;
      window.onresize = () => {
        this.reDrawCircle();
      };
      this.doConnect();
    },
    mounted() {
      this.timer1 = setInterval(this.checkMessageUpdate, 500);
    },
    beforeDestroy() {
      clearInterval(this.timer1);
    },
    destroyed() {
      this.showChatFlag = false;
      this.chatPerson = [];
      this.chatLists = [];
      this.leaveRoom(1);
      if (this.peer) {
        this.peer = null;
      }
    },
    computed: {
      // スクロールデータ処理中
      contactScrollDisble() {
          return this.contactScrollLoading || this.contactNoMoreData;
      },
      //あいまい検索
      searchEnterpriseRes() {
        if (this.searchGroupPerson) {
          let search = this.searchGroupPerson.toLowerCase();
          if (this.searchPerson && this.searchPerson.searchEnterprise) {
            return this.searchPerson.searchEnterprise.filter(data => {
              return Object.keys(data).some(key => {
                if (key === 'name' && data[key]) {
                  return String(data[key]).toLowerCase().indexOf(search) > -1
                }
              })
            });
          }
        }
      },
      searchParticipantRes() {
        if (this.searchGroupPerson) {
          let search = this.searchGroupPerson.toLowerCase();
          if (this.searchPerson && this.searchPerson.searchParticipant) {
            return this.searchPerson.searchParticipant.filter(data => {
              return Object.keys(data).some(key => {
                if (key === 'name' && data[key]) {
                  return String(data[key]).toLowerCase().indexOf(search) > -1
                }
              })
            });
          }
        }
      },
      searchGroupRes() {
        if (this.searchGroupPerson) {
          let search = this.searchGroupPerson.toLowerCase();
          if (this.searchPerson && this.searchPerson.searchGroup) {
            return this.searchPerson.searchGroup.filter(data => {
              return Object.keys(data).some(key => {
                if (key === 'name' && data[key]) {
                  return String(data[key]).toLowerCase().indexOf(search) > -1
                }
              })
            });
          }
        }
      },
      emojis() {
        return this.pannels.map(item => {
          return Object.keys(this.emojiData[item])
        })
      }
    },
    watch: {
      fileArr(newMsg){
        if(!newMsg.length){
          this.updated_filecount = 0;
        }
      },
      chatPerson(newMsg){
        //音声通話
        if (window.videoChat && !window.rejectFlag && newMsg && newMsg.length) {
          if (!this.showVideoTabFlag){
            for (let i = 0; i < newMsg.length; i++) {
              let person = newMsg[i];
              if (person.group_id == window.videoChat.group_id) {
                this.linkGroupCurrent = 'linkGroup-' + person.group_id;
                this.fetchMember(person);
              }
            }
          }
        } else if (window.videoChat && window.videoChat.call_status && newMsg && newMsg.length) {
          if (window.videoChat.call_status === 'reject') {
            //if duplicate user sent reject flag via different device
            //just ignore this flag
            for(let a = 0;a<this.groupUsers.length;a++){
              if(parseInt(this.groupUsers[a].user_id) === parseInt(window.videoChat.user_id)){
                window.videoChat = null;
                window.rejectFlag = null;
                return;
              }
            }

            if (window.videoChat.group_id === this.member.groupId) {
              //拒否人数+1
              this.rejectCount++;
              //if(拒否人数 = group人数-1) -> leaveRoom()
              if (this.rejectCount === this.groupUsers.length-1) {
                this.rejectCount = 0;
                this.leaveRoom(1);
              }
            } else {
              return;
            }
            window.videoChat = null;
            window.rejectFlag = null;
          } else if (window.videoChat.call_status === 'video_reject') {
            //if duplicate user sent reject flag via different device
            //just ignore this flag
            for(let a = 0;a<this.groupUsers.length;a++){
              if(parseInt(this.groupUsers[a].user_id) === parseInt(window.videoChat.user_id)){
                window.videoChat = null;
                window.rejectFlag = null;
                return;
              }
            }
            if (window.videoChat.group_id == this.member.groupId) {
              this.rejectCount++;
              if (this.rejectCount === this.groupUsers.length-1) {
                this.rejectCount = 0;
                this.leaveRoom(2);
              }
            } else {
              return;
            }
            window.videoChat = null;
            window.rejectFlag = null;
          }
        }
      },
      friendArr(newDate) {
        this.friendArr = newDate;
      },
      leaveToChatFlag() {
        if (this.chatJumpObj) {
          this.changeClickChatItem(this.chatJumpObj.groupId);
        }
      },
      searchChatListFlag(val) {
        if (this.chatSearchWordChanged) {
          this.chatMessageSearch();
        }
      },
      chatMessageSearchWord() {
        if (this.msgIsQuote && (this.chatMessageSearchWord === '' || this.chatMessageSearchWord.indexOf('--------------------') === -1)) {
          this.msgIsQuote = false;
        }
        this.chatSearchWordChanged = true;
      },
      chatLists: function () {
        this.$nextTick(() => {
          if (document.getElementById("videoImage")) {
            if (this.showChatFlag) {
              if(this.setId){
                document.getElementById("chatLists").scrollTop = document.getElementById("chatLists").scrollHeight - this.showPosition;
              }else{
                document.getElementById('chatLists').addEventListener('scroll', this.handleScrollx);
                document.getElementById("chatLists").scrollTop = document.getElementById("chatLists").scrollHeight;
              }
            }
            if (window.videoChat && window.videoChat.callInFalg && !window.rejectFlag && !window.videoChat.group_type){
              let windowData = window.videoChat;

              if (windowData.call_status === 'join' && this.member ){
                this.showVideoTab(1,1);
              }else if (windowData.call_status === 'video_join' && this.member ) {
                this.showVideoTab(2,1);
              }
              if (this.member.groupId == window.videoChat.group_id){
                window.videoChat = null;
                window.rejectFlag = null;
              }
            }
          }
        });
      },
      activeFlag: function () {
        this.groupMemberShowFlag = false;
        this.addToGroupFlag = false;
      },
    },
  }
</script>

<style lang="scss" scoped>
  @import '../../../scss/emoji-sprite';

  .pro-photo {
    width: 40px;
    height: 40px;
    left: 0;
    border-radius: 100%;
  }

  .popoverto-btngroup {
    text-align: center;
  }

  .popoverto-input {
    display: block;
    margin: 5px 0;
  }

  .group-person {
    width: 300px;
  }

  .group-person .group-person-header {
    padding-bottom: 5px;
    margin-bottom: 10px;
    border-bottom: 1px solid #aaa;
  }

  .group-person .group-person-add {
    display: flex;
    align-items: center;
  }
  .chat .chatcontent .rowfrist .group-person .group-person-add button {
    height: 40px;
    margin-left: 0;
  }

  .group-person .group-person-box {
    font-size: 12px;
    margin: 10px;
    text-align: left;
  }

  .group-person .group-person-box li {
    display: inline-block;
    width: 60px;
    position: relative;
    margin: 10px 5px;
    text-align: center;
    height: 50px;
  }

  .group-person .group-person-box li div {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: center;
  }

  .group-person .group-person-box li img:first-child {
    height: 30px;
    width: 30px;
    margin-right: 10px;
    border-radius: 100%;
    margin-left: 0px;
  }

  .group-person .group-person-box li span {
    font-size: 16px;
    color: #ea0000;
    position: absolute;
    right: 5px;
    top: -5px;
    cursor: pointer;
  }

  .group-person .group-person-friend {
    padding: 10px;
    display: flex;
    align-items: center;
  }

  .group-person .group-person-friend input, .group-person .group-person-friend img {
    margin-right: 10px;
  }

  .group-person .group-person-friend img {
    -webkit-border-radius: 100%;
    -moz-border-radius: 100%;
    border-radius: 100%;
  }

  .text-wrapper {
    white-space: pre-wrap;
  }

  .chat-group .chat-group-name {
    width: 80%;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  .chat-group .chat-msg-num {
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    border-radius: 50%;
    width: 30px;
    line-height: 30px;
    height: 30px;
    text-align: center;
    background: #ea0000;
  }

  .chat-add img {
    cursor: pointer;
    /*position: fixed;*//*---Mac--Safari--*/
    position: absolute;
    bottom: 30px;
    /*left: 280px;*//*---Mac--Safari--*/
    left: 180px;
  }

  .chat-add:hover {
    text-decoration: none;
    opacity: 0.8;
  }

  .group-creat {
    width: 100%;
  }


  ul {
    margin: 0;
    padding: 0;
    list-style: none;
  }

  /*------孫追加------*/
  .rowfrist .talk-contents-address {
    font-size: 14px;
    color: #A9A9AB;
  }

  .rowfrist .talk-name {
    font-size: 18px;
  }

  .rowsecond h4 span {
    display: inline-flex;
  }
  .rowsecond h4 >span:first-of-type {
    line-height: 20px;
  }
  .rowsecond h4 > .talk-contents-read {
    margin-right: 20px;
  }
  .talk-contents-read .el-icon--right{
    margin-left: 0;
  }
  .chat-like{
    height: 60px;
    line-height: 60px;
    margin-right: 20px;
  }
  .chat-like img{
    border: none;
    width: 16px;
    height: 16px;
    margin-top: 20px;
    margin-right: 10px;
  }
  .button-group .el-button-group .el-button > span .icon-groupbtn {
    margin-right: 5px;
  }

  #app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
    margin: 60px auto;
    width: 500px;

    .icon {
      position: relative;
      margin-top: 20px;

      .iconfont {
        cursor: pointer;
        color: #F7BA2A;
      }

      .emoji-box {
        position: absolute;
        z-index: 10;
        left: -10px;
        top: 24px;
        box-shadow: 0 4px 20px 1px rgba(0, 0, 0, 0.2);
        background: white;

        .el-button {
          position: absolute;
          border: none;
          color: #FF4949;
          right: 12px;
          top: 12px;
          z-index: 10;
        }

        .arrow {
          left: 10px;
        }
      }

      .submit {
        float: right;
      }
    }

    .comment {
      margin-top: 20px;

      .item {
        margin-top: 20px;
        padding: 10px;
        margin: 0;
        border-top: 1px solid #bfcbd9;
      }
    }
  }

  .clearfix {
    &:after {
      content: '';
      display: block;
      height: 0;
      clear: both;
      visibility: hidden;
    }
  }

  .fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
  }

  .fade-enter, .fade-leave-active {
    opacity: 0;
  }

  .fade-move {
    transition: transform .4s;
  }

  .list-enter-active, .list-leave-active {
    transition: all .5s;
  }

  .list-enter, .list-leave-active {
    opacity: 0;
    transform: translateX(30px);
  }

  .list-leave-active {
    position: absolute !important;
  }

  .list-move {
    transition: all .5s;
  }

  .emoji {
    width: 380px;
    height: 326px;
    bottom: 30px;
    background: #fff;
    z-index: 10;
    padding: 10px;
    margin-right: 10px;

    .emoji-controller {
      height: 36px;
      overflow: hidden;
      margin-bottom: 0;

      li {
        float: left;
        width: 76px;
        font-size: 12px;
        line-height: 36px;
        cursor: pointer;
        text-align: center;
        position: relative;

        &.active::after {
          content: '';
          width: 100%;
          height: 1px;
          background: #0689dd;
          left: 0;
          bottom: 4px;
          position: absolute;
        }
      }
    }

    .emoji-container {
      height: 140px;
      overflow-y: auto;
      overflow-x: hidden;
      position: relative;

      li {
        font-size: 0;
        padding: 5px;

        a {
          float: left;
          overflow: hidden;
          height: 35px;
          transition: all ease-out .2s;
          border-radius: 4px;

          &:hover {
            background-color: #d8d8d8;
            border-color: #d8d8d8;
          }

          span {
            width: 25px;
            height: 25px;
            display: inline-block;
            border: 1px solid transparent;
            cursor: pointer;
          }
        }
      }
    }
  }

  .el-tabs--left .el-tabs__nav-wrap.is-left.is-scrollable {
    padding: 0px !important;
  }

  #remote-videos video {
    width: 200px;
  }

  .date-title {
    text-align: center;
    position: relative;
    height: 50px;
    line-height: 50px;
    width: 100%;
  }

  .date-title:after, .date-title:before {
    content: "";
    position: absolute;
    top: 50%;
    background: #e5e5e5;
    height: 1px;
    width: calc(50% - 65px);
    z-index: -1;
  }

  .date-title:after {
    left: 0;
  }

  .date-title:before {
    right: 0;
  }

  .date-title span {
    position: absolute;
    width: 130px;
    right: calc(50% - 65px);
    z-index: -1;
  }

  .group-person-num {
    max-height: 200px;
    overflow-y: auto;
  }

  .tab-enterprise {
    height: 140px;
    overflow-y: auto;
  }

  .group-person .group-person-box .tab-enterprise li {
    width: 100%;
  }

  .tab-enterprise li > label {
    display: flex;
    align-items: center;
  }

  .tab-enterprise li {
    height: 35px;
  }

  .tab-enterprise img {
    width: 30px;
    margin-right: 10px;
  }

  .edit-photo {
    margin-left: 5px;
    width: 30px;
  }

  .edit-photo > img {
    width: 30px;
  }
</style>
