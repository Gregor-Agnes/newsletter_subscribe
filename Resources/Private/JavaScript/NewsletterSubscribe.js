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

const iAmNotASpamBotContainer = document.getElementById('iAmNotASpamBotContainer');
const iAmNotASpamBotValue = iAmNotASpamBotContainer.dataset.iamnotaspambotvalue;
const iAmNotARobotLabel = iAmNotASpamBotContainer.dataset.iamnotarobotlabel;
let insertNoSpamBotField = () => {
  let noSpamField = document.createElement('div')
  noSpamField.classList.add('field')
  noSpamField.innerHTML = `
<input id="iAmNotASpamBotHere" type="checkbox" name="iAmNotASpamBotHere" value="${iAmNotASpamBotValue}">
<label class="label checkbox" for="iAmNotASpamBotHere">
    ${iAmNotARobotLabel}
</label>
                
<input type="hidden" name="iAmNotASpamBot" value="">
<label class="label" for="iAmNotASpamBot">
<input id="iAmNotASpamBot" type="checkbox" name="iAmNotASpamBot" value="${iAmNotASpamBotValue}">
    ${iAmNotARobotLabel}
</label>`
  document.getElementById('NewsletterSubscribeSubmit').before(noSpamField)
}

if (typeof (iAmNotASpamBotValue) !== "undefined") {
  document.addEventListener("DOMContentLoaded", () => {
    insertNoSpamBotField()
  })
}
