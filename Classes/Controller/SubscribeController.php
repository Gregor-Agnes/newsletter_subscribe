<?php

namespace Zwo3\NewsletterSubscribe\Controller;

/*
 * Subscription für tt_address
 *
 * erstmaliges Abschicken erzeugt Confirmation-Mail und deaktivierten Account
 * Click in Confirmation-Mail erzeugt aktivieren Account
 * nochmalige Anmeldung mit gleicher Adresse erzeugt Mail mit Info und Kündigungslink
 * Click auf Kündigungslink löscht Datensatz komplett
 *
 * Separate Funktion zur Kündigung  ohne Token, nur mit Action createUnsubscribeMail und E-Mail-Adresse erzeugt Mail mit Kündigungslink
 */

use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use Zwo3\NewsletterSubscribe\Domain\Model\Subscription;
use Zwo3\NewsletterSubscribe\Domain\Repository\SubscriptionRepository;
use Zwo3\NewsletterSubscribe\Utilities\OverrideEmptyFlexformValues;

/**
 * Class SubscribeController
 *
 * @package Zwo3\NewsletterSubscribe\Controller
 */
class SubscribeController extends ActionController
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var OverrideEmptyFlexformValues
     */
    protected $overrideFlexFormValues;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function initializeAction()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $this->subscriptionRepository = $this->objectManager->get(SubscriptionRepository::class);
        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $this->overrideFlexFormValues = $this->objectManager->get(OverrideEmptyFlexformValues::class);

        $this->settings = $this->overrideFlexFormValues->overrideSettings('newsletter_subscribe', 'Subscribe');

    }

    public function showFormAction()
    {

        $formToken = FormProtectionFactory::get('frontend')
            ->generateToken('Subscribe', 'showForm', $this->configurationManager->getContentObject()->data['uid']);

        $fields = explode(',', $this->settings['showFields']);

        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'formToken' => $formToken,
            'fields' => $fields
        ]);
    }

    /**
     * @param string $message
     */
    public function showUnsubscribeFormAction(?string $message = null)
    {
        $formToken = FormProtectionFactory::get('frontend')
            ->generateToken('Subscribe', 'showUnsubscribeForm', $this->configurationManager->getContentObject()->data['uid']);

        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'message' => $message,
            'formToken' => $formToken

        ]);
    }

    /**
     * @param string $email
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function createUnsubscribeMailAction(?string $email = null)
    {
        if (!FormProtectionFactory::get('frontend')
            ->validateToken(
                (string)GeneralUtility::_POST('formToken'),
                'Subscribe', 'showUnsubscribeForm', $this->configurationManager->getContentObject()->data['uid']
            )) {
            $this->redirect('showUnsubscribeForm');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($existing = $this->subscriptionRepository->findOneByEmail($email, false)) {
                /** @var Subscription $existing */
                // if no hash (e.g. manually added address), create one!
                if (!$existing->getSubscriptionHash()) {
                    $existing->setSubscriptionHash(hash('sha256', $existing->getEmail() . $existing->getCrdate() . random_bytes(32)));
                }
                // Abmelden Mail versenden

                try {
                    $this->sendTemplateEmail(
                        [$existing->getEmail() => $existing->getName()],
                        [$this->settings['adminEmail'] => $this->settings['adminName']],
                        LocalizationUtility::translate('subjectUnsubscribe', 'newsletter_subscribe') . $this->settings['newsletterName'],
                        'Mail/' . $GLOBALS['TSFE']->sys_language_isocode . '/CreateUnsubscribe',
                        [
                            'subscription' => $existing
                        ]
                    );
                    $this->subscriptionRepository->update($existing);
                } catch (InvalidTemplateResourceException $exception) {
                    $this->addFlashMessage('Create a template in the Mail Folder for the current language (e.g. de, fr, dk).', 'No E-Mail-Template found', AbstractMessage::ERROR);
                }
            }
        } else {
            $this->redirect('showUnsubscribeForm', null, null, ['message' => 'E-Mail-Adresse nicht valide']);
        }

        $this->view->assignMultiple(compact('message', 'email'));
    }

    /**
     * @param Subscription $subscription
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    public function createConfirmationAction(Subscription $subscription)
    {
        if (!FormProtectionFactory::get('frontend')
            ->validateToken(
                (string)GeneralUtility::_POST('formToken'),
                'Subscribe', 'showForm', $this->configurationManager->getContentObject()->data['uid']
            )) {
            $this->redirect('showForm');
        }
        // already subscribed
        if ($existing = $this->subscriptionRepository->findOneByEmail($subscription->getEmail(), false)) {
            // Abmelden Mail versenden
            /** @var Subscription $existing */

            try {
                $this->sendTemplateEmail(
                    [$existing->getEmail() => $existing->getName()],
                    [$this->settings['adminEmail'] => $this->settings['adminName']],
                    'Ihr Abonnement',
                    'Mail/' . $GLOBALS['TSFE']->sys_language_isocode . '/AlreadySubscribed',
                    [
                        'subscription' => $existing,
                    ]
                );
            } catch (InvalidTemplateResourceException $exception) {
                $this->addFlashMessage('Create a template in the Mail Folder for the current language (e.g. de, fr, dk).', 'No E-Mail-Template found', AbstractMessage::ERROR);
            }

            //$this->subscriptionRepository->update($existing);

            $this->view->assignMultiple(['subscription' => $existing]);
        } else {
            $subscription->setHidden(1);
            $subscription->setModuleSysDmailNewsletter(true);
            $subscription->setModuleSysDmailHtml(true);
            $subscription->setCrdate(time());
            $subscription->setSubscriptionHash(hash('sha256', $subscription->getEmail() . $subscription->getCrdate() . random_bytes(32)));
            $subscription->setPid($this->subscriptionRepository->createQuery()
                ->getQuerySettings()
                ->getStoragePageIds()[0]);

            $this->subscriptionRepository->add($subscription);
            $this->persistenceManager->persistAll();

            try {
                $this->sendTemplateEmail(
                    [$subscription->getEmail() => $subscription->getName()],
                    [$this->settings['adminEmail'] => $this->settings['adminName']],
                    LocalizationUtility::translate('yourSubscription', 'newsletterSubscribe'),
                    'Mail/' . $GLOBALS['TSFE']->sys_language_isocode . '/Confirmation',
                    [
                        'subscription' => $subscription,
                    ]
                );
            } catch (InvalidTemplateResourceException $exception) {
                $this->addFlashMessage('Create a template in the Mail Folder for the current language (e.g. de, fr, dk).', 'No E-Mail-Template found', AbstractMessage::ERROR);
            }

            $this->view->assignMultiple(['subscription' => $subscription]);
        }
    }

    /**
     * @param int $uid
     * @param string $subscriptionHash
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function unsubscribeAction(?int $uid = null, ?string $subscriptionHash = null)
    {

        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findByUid($uid, false);

        $success = false;
        if ($subscription) {
            if ($subscriptionHash == $subscription->getSubscriptionHash()) {
                $this->subscriptionRepository->remove($subscription);
                $success = true;
            } else {
                // increasing sleeptimer
                $subscription = $this->setSleep($subscription, 300, 2);
                $this->subscriptionRepository->update($subscription);
                //TODO redirect with 404
            }
        } else {
            //TODO redirect with 404
        }
        $this->view->assignMultiple(compact('message', 'subscription', 'success'));
    }

    public function undosubscribeAction(?int $uid = null, ?string $subscriptionHash = null)
    {
        $this->redirect('unsubscribe', null, null, compact('uid', 'subscriptionHash'));
    }

    /**
     * @param int $uid
     * @param string $subscriptionHash
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function doConfirmAction(?int $uid = null, ?string $subscriptionHash = null)
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findByUid($uid, false);

        $success = false;
        if ($subscription) {
            if ($subscriptionHash == $subscription->getSubscriptionHash() && $subscription->isHidden()) {
                $subscription->setHidden(0);
                $this->subscriptionRepository->update($subscription);
                $message = 'Sie haben Ihr Abonnement erfolgreich bestätigt und werden ab sofort den Blickpunkt Infodienst erhalten!';
                $success = true;
            } else {
                $message = 'Dieser Link ist nicht gültig';
                // increasing sleeptimer
                $subscription = $this->setSleep($subscription, 300, 2);
                $this->subscriptionRepository->update($subscription);
                //TODO redirect with 404

            }
        } else {
            $message = 'Wir konnten kein entsprechende Abonnement finden. Haben Sie Ihr Abonnement vielleicht schon bestätigt?';
            //TODO redirect with 404
        }

        $this->view->assignMultiple(compact('message', 'subscription', 'success'));
    }

    /**
     * @param array $recipient recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
     * @param array $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
     * @param string $subject subject of the email
     * @param string $templateName template name (UpperCamelCase)
     * @param array $variables variables to be passed to the Fluid view
     * @param array $replyTo replyTo Address
     * @return boolean TRUE on success, otherwise false
     */
    protected function sendTemplateEmail(array $recipient, array $sender, $subject, $templateName = 'Mail/Default', array $variables = array(), array $replyTo = null, array $attachments = [])
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
        $emailView = GeneralUtility::makeInstance(StandaloneView::class);
        $emailView->setControllerContext($this->controllerContext);
        $emailView->setTemplate($templateName);


        $emailView->assignMultiple($variables);
        $emailBody = $emailView->render();

        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = new MailMessage();
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject) // ->setBcc(array('form@zwo3.de' => 'zwo3.de'))
        ;

        if ($replyTo) {
            $message->setReplyTo($replyTo);
        }

        // Possible attachments here
        foreach ($attachments as $attachment) {
            $message->attach($attachment);
        }

        // HTML Email
        $message->setBody($emailBody, 'text/html');

        // Add TXT Part
        #$message->addPart($emailBodyTxt, 'text/plain');

        $message->send();

        return $message->isSent();
    }

    /**
     * @param Subscription $subscription
     * @param int $maxSleeptime max time to wait after last hit, if reached, sleep is resetted
     * @param int $multiplier multipliere * hitnumber = seconds to wait,
     * @return Subscription
     */
    protected function setSleep(Subscription $subscription, $maxSleeptime = 300, $multiplier = 2): Subscription
    {
        $sleepTime = $subscription->getHitNumber() * $multiplier;
        if (time() > $subscription->getLastHit() + $maxSleeptime) {
            // reset sleep after 5 minutes
            $sleepTime = 0;
            $subscription->setHitNumber(0);
        } else {
            $subscription->setHitNumber($subscription->getHitNumber() + 1);
        }
        sleep($sleepTime);

        $subscription->setLastHit(time());

        return $subscription;
    }

    /**
     * @return bool The flash message or FALSE if no flash message should be set
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

}