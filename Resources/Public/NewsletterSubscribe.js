"use strict";(self.webpackChunk_zwo3_newsletter_subscribe=self.webpackChunk_zwo3_newsletter_subscribe||[]).push([[219],{599:function(){const e=document.getElementById("iAmNotASpamBotContainer"),t=null!==e&&e.dataset.iamnotaspambotvalue?e.dataset.iamnotaspambotvalue:void 0,a=null!==e&&e.dataset.iamnotarobotlabel?e.dataset.iamnotarobotlabel:void 0,n=null!==e&&e.dataset.mandatoryfield?e.dataset.mandatoryfield:void 0;void 0!==t&&document.addEventListener("DOMContentLoaded",(()=>{(()=>{let e=document.createElement("label");if(e.classList.add("block"),e.innerHTML=`\n<input id="iAmNotASpamBotHere" class="hidden" type="checkbox" name="iAmNotASpamBotHere" value="${t}">\n<label class="label checkbox hidden" style="display: none;" for="iAmNotASpamBotHere">\n    ${a}\n</label>\n\n<input type="hidden" name="iAmNotASpamBot" value="">\n<label class="inline-flex items-center" for="iAmNotASpamBot">\n<input id="iAmNotASpamBot" type="checkbox" name="iAmNotASpamBot" value="${t}"  class="border-solid border-gray-600 form-checkbox">\n     <span class="ml-2">${a}</span>\n</label>`,document.getElementById("NewsletterSubscribeSubmit").closest("div").before(e),document.getElementById("NewsletterSubscribeSubmit").closest("form").classList.contains("spambotFailed")){let t=document.createElement("p");t.classList.add("-mt-4"),t.classList.add("text-sm"),t.innerHTML=n,e.after(t)}})()}))}},function(e){var t;t=599,e(e.s=t)}]);