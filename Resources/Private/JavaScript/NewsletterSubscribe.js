import '../Scss/Styles.scss'

// Polyfill IE
// from: https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/before()/before().md
(function (arr) {
  arr.forEach(function (item) {
    if (item.hasOwnProperty('before')) {
      return;
    }
    Object.defineProperty(item, 'before', {
      configurable: true,
      enumerable: true,
      writable: true,
      value: function before() {
        var argArr = Array.prototype.slice.call(arguments),
          docFrag = document.createDocumentFragment();

        argArr.forEach(function (argItem) {
          var isNode = argItem instanceof Node;
          docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
        });

        this.parentNode.insertBefore(docFrag, this);
      }
    });
  });
})([Element.prototype, CharacterData.prototype, DocumentType.prototype]);

let insertNoSpamBotField = () => {
  let noSpamField = document.createElement('div')
  noSpamField.innerHTML = `
<input id="iAmNotASpamBotHere" type="checkbox" name="iAmNotASpamBotHere" value="${iAmNotASpamBotValue}">
                <label class="label" for="iAmNotASpamBotHere">
                    ${iAmNotARobotLabel}
                </label>
                
<input type="hidden" name="iAmNotASpamBot" value="">
<input id="iAmNotASpamBot" type="checkbox" name="iAmNotASpamBot" value="${iAmNotASpamBotValue}">
                <label class="label" for="iAmNotASpamBot">
                    ${iAmNotARobotLabel}
                </label>`

  document.getElementById('NewsletterSubscribeSubmit').before(noSpamField)
}

document.addEventListener("DOMContentLoaded", () => {
  console.log('haos')
  insertNoSpamBotField()
})
