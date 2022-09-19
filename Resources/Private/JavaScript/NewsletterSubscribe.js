import '../Scss/Styles.scss'

const iAmNotASpamBotContainer = document.getElementById('iAmNotASpamBotContainer');
const iAmNotASpamBotValue = (iAmNotASpamBotContainer !== null && iAmNotASpamBotContainer.dataset.iamnotaspambotvalue) ? iAmNotASpamBotContainer.dataset.iamnotaspambotvalue : undefined;
const iAmNotARobotLabel = (iAmNotASpamBotContainer !== null && iAmNotASpamBotContainer.dataset.iamnotarobotlabel) ? iAmNotASpamBotContainer.dataset.iamnotarobotlabel : undefined;

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
