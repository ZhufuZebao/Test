import data from '../data/emoji-data.js'
let emojiData = {}
Object.values(data).forEach(item => {
  emojiData = { ...emojiData, ...item }
})

/**
 *
 *
 * @export
 * @param {string} value
 * @returns {string}
 */

export function emoji (value) {
  if (!value) return;
  Object.keys(emojiData).forEach(item => {
    if (item === '[icon:001]') {
      value = value.replace(/\[icon:001\]/g, createIcon(item));
    } else if (item === '[icon:002]') {
      value = value.replace(/\[icon:002\]/g, createIcon(item));
    } else if (item === '[icon:003]') {
      value = value.replace(/\[icon:003\]/g, createIcon(item));
    } else if (item === '[icon:004]') {
      value = value.replace(/\[icon:004\]/g, createIcon(item));
    } else if (item === '[icon:005]') {
      value = value.replace(/\[icon:005\]/g, createIcon(item));
    } else if (item === '[icon:006]') {
      value = value.replace(/\[icon:006\]/g, createIcon(item));
    } else if (item === '[icon:007]') {
      value = value.replace(/\[icon:007\]/g, createIcon(item));
    } else if (item === '[icon:008]') {
      value = value.replace(/\[icon:008\]/g, createIcon(item));
    } else if (item === '[icon:009]') {
      value = value.replace(/\[icon:009\]/g, createIcon(item));
    } else if (item === '[icon:010]') {
      value = value.replace(/\[icon:010\]/g, createIcon(item));
    } else if (item === '[icon:011]') {
      value = value.replace(/\[icon:011\]/g, createIcon(item));
    } else if (item === '[icon:012]') {
      value = value.replace(/\[icon:012\]/g, createIcon(item));
    } else if (item === '[icon:013]') {
      value = value.replace(/\[icon:013\]/g, createIcon(item));
    } else if (item === '[icon:014]') {
      value = value.replace(/\[icon:014\]/g, createIcon(item));
    } else if (item === '[icon:015]') {
      value = value.replace(/\[icon:015\]/g, createIcon(item));
    } else if (item === '[icon:016]') {
      value = value.replace(/\[icon:016\]/g, createIcon(item));
    } else if (item === '[icon:017]') {
      value = value.replace(/\[icon:017\]/g, createIcon(item));
    } else if (item === '[icon:018]') {
      value = value.replace(/\[icon:018\]/g, createIcon(item));
    } else if (item === '[icon:019]') {
      value = value.replace(/\[icon:019\]/g, createIcon(item));
    } else if (item === '[icon:020]') {
      value = value.replace(/\[icon:020\]/g, createIcon(item));
    } else if (item === '[icon:021]') {
      value = value.replace(/\[icon:021\]/g, createIcon(item));
    } else if (item === '[icon:022]') {
      value = value.replace(/\[icon:022\]/g, createIcon(item));
    } else if (item === '[icon:023]') {
      value = value.replace(/\[icon:023\]/g, createIcon(item));
    } else if (item === '[icon:024]') {
      value = value.replace(/\[icon:024\]/g, createIcon(item));
    } else if (item === '[icon:025]') {
      value = value.replace(/\[icon:025\]/g, createIcon(item));
    } else if (item === '[icon:026]') {
      value = value.replace(/\[icon:026\]/g, createIcon(item));
    } else if (item === '[icon:027]') {
      value = value.replace(/\[icon:027\]/g, createIcon(item));
    } else if (item === '[icon:028]') {
      value = value.replace(/\[icon:028\]/g, createIcon(item));
    } else if (item === '[icon:029]') {
      value = value.replace(/\[icon:029\]/g, createIcon(item));
    } else if (item === '[icon:030]') {
      value = value.replace(/\[icon:030\]/g, createIcon(item));
    } else if (item === '[icon:031]') {
      value = value.replace(/\[icon:031\]/g, createIcon(item));
    } else if (item === '[icon:032]') {
      value = value.replace(/\[icon:032\]/g, createIcon(item));
    } else if (item === '[icon:033]') {
      value = value.replace(/\[icon:033\]/g, createIcon(item));
    } else if (item === '[icon:034]') {
      value = value.replace(/\[icon:034\]/g, createIcon(item));
    } else if (item === '[icon:035]') {
      value = value.replace(/\[icon:035\]/g, createIcon(item));
    } else if (item === '[icon:036]') {
      value = value.replace(/\[icon:036\]/g, createIcon(item));
    } else if (item === '[icon:037]') {
      value = value.replace(/\[icon:037\]/g, createIcon(item));
    } else if (item === '[icon:038]') {
      value = value.replace(/\[icon:038\]/g, createIcon(item));
    } else if (item === '[icon:039]') {
      value = value.replace(/\[icon:039\]/g, createIcon(item));
    } else if (item === '[icon:040]') {
      value = value.replace(/\[icon:040\]/g, createIcon(item));
    } else if (item === '[icon:041]') {
      value = value.replace(/\[icon:041\]/g, createIcon(item));
    } else if (item === '[icon:042]') {
      value = value.replace(/\[icon:042\]/g, createIcon(item));
    } else if (item === '[icon:043]') {
      value = value.replace(/\[icon:043\]/g, createIcon(item));
    } else if (item === '[icon:044]') {
      value = value.replace(/\[icon:044\]/g, createIcon(item));
    } else if (item === '[icon:045]') {
      value = value.replace(/\[icon:045\]/g, createIcon(item));
    } else if (item === '[icon:046]') {
      value = value.replace(/\[icon:046\]/g, createIcon(item));
    } else if (item === '[icon:047]') {
      value = value.replace(/\[icon:047\]/g, createIcon(item));
    } else if (item === '[icon:048]') {
      value = value.replace(/\[icon:048\]/g, createIcon(item));
    } else if (item === '[icon:049]') {
      value = value.replace(/\[icon:049\]/g, createIcon(item));
    } else if (item === '[icon:050]') {
      value = value.replace(/\[icon:050\]/g, createIcon(item));
    } else if (item === '[icon:051]') {
      value = value.replace(/\[icon:051\]/g, createIcon(item));
    } else if (item === '[icon:052]') {
      value = value.replace(/\[icon:052\]/g, createIcon(item));
    } else if (item === '[icon:053]') {
      value = value.replace(/\[icon:053\]/g, createIcon(item));
    } else if (item === '[icon:054]') {
      value = value.replace(/\[icon:054\]/g, createIcon(item));
    } else if (item === '[icon:055]') {
      value = value.replace(/\[icon:055\]/g, createIcon(item));
    }
  })
  return value
}

function createIcon (item) {
  const value = emojiData[item]
  const path = './images/chat/'
  return `<img src=${path}${value} width="40px" height="40px">`
}
