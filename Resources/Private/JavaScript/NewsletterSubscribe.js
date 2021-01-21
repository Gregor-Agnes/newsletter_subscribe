import '../Scss/Styles.scss'

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

