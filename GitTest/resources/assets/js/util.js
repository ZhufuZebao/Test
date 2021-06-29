/**
 * クッキーの値を取得する
 * @param {String} searchKey 検索するキー
 * @returns {String} キーに対応する値
 */
export function getCookieValue (searchKey) {
    if (typeof searchKey === 'undefined') {
      return ''
    }
  
    let val = '';
  
    document.cookie.split(';').forEach(cookie => {
      const [key, value] = cookie.split('=')
      if (key && key.trim() === searchKey.trim()) {
        return val = value
      }
    });
  
    return val;
  }
/**
 * 【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 begin
 * 画像を圧縮する
 * @param file
 * @param imgQuality 画像の品質 取得範囲: 0 ~ 1
 * @returns {Promise<any>}
 */
export function compressImage(file, imgQuality) {
  return new Promise(resolve => {
    const reader = new FileReader()
    const image = new Image()
    image.onload = (imageEvent) => {
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');
      const width = image.width * imgQuality
      const height = image.height * imgQuality
      canvas.width = width;
      canvas.height = height;
      context.clearRect(0, 0, width, height);
      context.drawImage(image, 0, 0, width, height);
      const dataUrl = canvas.toDataURL(file.type);
      const blobData = dataURItoBlob(dataUrl, file.type);
      resolve(new File([blobData], file.name))
    }
    reader.onload = (e => { image.src = e.target.result; });
    reader.readAsDataURL(file);
  })
}
function dataURItoBlob(dataURI, type) {
  let binary = atob(dataURI.split(',')[1]);
  let array = [];
  for(let i = 0; i < binary.length; i++) {
    array.push(binary.charCodeAt(i));
  }
  return new Blob([new Uint8Array(array)], {type: type});
}
//【チャット】画像を送信時に圧縮の有無を選択させる #2648 変更 end