import '../Css/Styles.css'

const iAmNotASpamBotContainer = document.getElementById('iAmNotASpamBotContainer');
const iAmNotASpamBotValue = (iAmNotASpamBotContainer !== null && iAmNotASpamBotContainer.dataset.iamnotaspambotvalue) ? iAmNotASpamBotContainer.dataset.iamnotaspambotvalue : undefined;
const iAmNotARobotLabel = (iAmNotASpamBotContainer !== null && iAmNotASpamBotContainer.dataset.iamnotarobotlabel) ? iAmNotASpamBotContainer.dataset.iamnotarobotlabel : undefined;
const mandatoryField = (iAmNotASpamBotContainer !== null && iAmNotASpamBotContainer.dataset.mandatoryfield) ? iAmNotASpamBotContainer.dataset.mandatoryfield : undefined;

let insertNoSpamBotField = () => {
  let noSpamField = document.createElement('label')
  noSpamField.classList.add('block')
  noSpamField.innerHTML = `
<input id="iAmNotASpamBotHere" class="hidden" type="checkbox" name="iAmNotASpamBotHere" value="${iAmNotASpamBotValue}">
<label class="label checkbox hidden" for="iAmNotASpamBotHere">
    ${iAmNotARobotLabel}
</label>

<input type="hidden" name="iAmNotASpamBot" value="">
<label class="inline-flex items-center" for="iAmNotASpamBot">
<input id="iAmNotASpamBot" type="checkbox" name="iAmNotASpamBot" value="${iAmNotASpamBotValue}"  class="border-solid border-gray-600 form-checkbox">
     <span class="ml-2">${iAmNotARobotLabel}</span>
</label>`
  document.getElementById('NewsletterSubscribeSubmit').closest('div').before(noSpamField)

	if (document.getElementById('NewsletterSubscribeSubmit').closest('form').classList.contains('spambotFailed')) {
		let hint = document.createElement('p')
		hint.classList.add('-mt-4')
		hint.classList.add('text-sm')
		hint.innerHTML = mandatoryField
		noSpamField.after(hint)
	}
}

if (typeof (iAmNotASpamBotValue) !== "undefined") {
  document.addEventListener("DOMContentLoaded", () => {
    insertNoSpamBotField()
  })
}
