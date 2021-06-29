export const chatCircle = {
  methods: {
    resizeImage() {
      Vue.nextTick(() => {
        let divPic = document.getElementById('divPic');
        let imgObj = this.$refs.videoImage;
        if (imgObj === undefined || imgObj.naturalHeight === undefined) {
          return;
        }
        // when rotate image, change the width & height
        // let imgWidth = !this.isTransverse ? divPic.offsetWidth : divPic.offsetHeight;
        // let imgHeight = !this.isTransverse ? divPic.offsetHeight : divPic.offsetWidth;

        let imgWidth = divPic.offsetWidth;
        let imgHeight = divPic.offsetHeight;
        let naturalWidth = imgObj.naturalWidth;
        let naturalHeight = imgObj.naturalHeight;
        let widthRatio = naturalWidth / imgWidth;
        let heightRatio = naturalHeight / imgHeight;
        // use the bigger ratio
        if (heightRatio > widthRatio) {
          imgWidth = naturalWidth / heightRatio;
        } else {
          imgHeight = naturalHeight / widthRatio;
        }
        // use the image's orign max width & height
        if (imgWidth > naturalWidth) {
          imgWidth = naturalWidth;
        }
        if (imgHeight > naturalHeight) {
          imgHeight = naturalHeight;
        }
        imgObj.style.width = imgWidth + 'px';
        imgObj.style.height = imgHeight + 'px';
      });
    },
    drawCircleOnClick(e) {
      let x = 0;
      let y = 0;
      let imgObj = this.$refs.videoImage;
      this.isdrawCircle = true;

      let imgWidth = imgObj.width;
      let imgHeight = imgObj.height;
      let naturalWidth = imgObj.naturalWidth;
      let naturalHeight = imgObj.naturalHeight;
      let widthRatio = naturalWidth / imgWidth;
      let heightRatio = naturalHeight / imgHeight;
      let ratio = widthRatio;
      // use the smaller ratio
      if (heightRatio < widthRatio) {
        ratio = heightRatio;
      }

      let domRect = imgObj.getBoundingClientRect();
      let enlarge = 0;
      if (this.isTransverse) {
        enlarge = 1;
        // when the image div rotate left, transfer the position
        x = (imgObj.height - e.y + domRect.y) * ratio;
        y = (e.x - domRect.x) * ratio;
        // when the image rotate right, transfer the position
        // x = (e.y - domRect.y) * ratio;
        // y = (imgObj.height - e.x + domRect.x) * ratio;
      } else {
        x = (e.x - domRect.x) * ratio;
        y = (e.y - domRect.y) * ratio;
      }
      this.setCirclePosition(e.x, e.y);

      let msg = {
        id: this.user.id,
        x: x,
        y: y,
        enlarge: enlarge
      };
      this.drawCircleX = x;
      this.drawCircleY = y;
      this.socket.emit("chat.send.touch.xy", msg);
    },
    drawCircleOnChange() {
      // wait for render
      Vue.nextTick(() => {
        if (this.isdrawCircle !== undefined && this.drawCircleX !== undefined && this.drawCircleY !== undefined
            && this.isdrawCircle && this.drawCircleX !== 0 && this.drawCircleY !== 0) {
          let imgObj = this.$refs.videoImage;
          this.transferPosition(imgObj, this.drawCircleX, this.drawCircleY);
        }
      });
    },
    drawCircleOnSocket(msgObj) {
      this.showPic = true;
      this.isdrawCircle = true;
      let imgObj = this.$refs.videoImage;
      this.transferPosition(imgObj, msgObj.x, msgObj.y);
      this.drawCircleX = msgObj.x;
      this.drawCircleY = msgObj.y;
    },
    transferPosition(imgObj, circleX, circleY) {
      if (imgObj === undefined || imgObj.naturalHeight === undefined) {
        return;
      }
      let naturalWidth = imgObj.naturalWidth;
      let naturalHeight = imgObj.naturalHeight;
      let imgWidth = imgObj.width;
      let imgHeight = imgObj.height;
      let widthRatio = naturalWidth / imgWidth;
      let heightRatio = naturalHeight / imgHeight;
      let ratio = widthRatio;
      if (heightRatio > widthRatio) {
        ratio = heightRatio;
      }
      let domRect = imgObj.getBoundingClientRect();
      let x = domRect.x;
      let y = domRect.y;
      let left = 0;
      let top = 0;
      if (this.isTransverse) {
        // when the image div rotate left, transfer the position
        left = circleY / ratio + x;
        top = (naturalHeight - circleX) / ratio + y;
        // when the image rotate right, transfer the position
        // left = (naturalHeight - circleY) / ratio + x;
        // top = circleX / ratio + y;
      } else {
        left = circleX / ratio + x;
        top = circleY / ratio + y;
      }

      this.setCirclePosition(left, top);
    },
    setCirclePosition(left, top) {
      let circleRadius = 20;
      if (this.isFullscreen) {
        circleRadius = 65;
      }
      this.leftCircle = left - circleRadius;
      this.topCircle = top - circleRadius;
    }
  }
}
