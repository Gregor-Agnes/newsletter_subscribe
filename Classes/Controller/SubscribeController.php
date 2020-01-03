<?php

namespace Zwo3\Subscribe\Controller;

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

use ResourceBundle;
use Swift_Attachment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Zwo3\Subscribe\Domain\Model\Subscription;
use Zwo3\Subscribe\Domain\Repository\SubscriptionRepository;

/**
 * Class SubscribeController
 *
 * @package Zwo3\Subscribe\Controller
 */
class SubscribeController extends ActionController
{


    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;



    public function initializeAction()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subscriptionRepository = $this->objectManager->get(SubscriptionRepository::class);
    }

    public function showFormAction()
    {
        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'formToken' => $this->formToken
        ]);
    }

    /**
     * @param string $message
     */
    public function showUnsubscribeFormAction(?string $message = null)
    {
        DebuggerUtility::var_dump($this->subscriptionRepository->findAll());
        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'message' => $message
        ]);
    }

    /**
     * @param string $email
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * zur Verwendung: Fluid Link z.B. in E-Mail platzieren
     *      * <f:link.action action="createUnsubscribeMail" controller="Subscribe" pluginName="subscribe"  extensionName="subscribe" arguments="{email: 'ga@zwo3.de'}" pageUid="[uid of subscriptionPage]" >testen</f:link.action>
     * Wenn im Newsletter neben email-Adreesse auch token token aus tt_address zu Verfügung steht kann auch
     *    <f:link.action absolute="1" pageUid="{settings.subscribeFormPid}" action="unsubscribe" controller="Subscribe" pluginName="subscribe" extensionName="subscribe" arguments="{email: subscription.email, hash: '{v:format.hash(content: \'{adress.email}{adrdress.token}\', algorithm: \'sha256\')}'}">Abonnement kündigen</f:link.action>
     * direkt in der Mail verwendet werden. Dann reicht ein Klick im Newsletter zum kündigen
     * Oder Plugin unsubscribe auf beliebiger Seite platzieren undunsubscribeFormPid in settings setzen!
     */
    public function createUnsubscribeMailAction(?string $email = null)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($existing = $this->subscriptionRepository->findOneByEmail($email)) {
                // Abmelden Mail versenden
                /** @var Subscription $existing */
                $this->sendTemplateEmail(
                    [$existing->getEmail() => $existing->getName()],
                    [$this->settings['adminEmail'] => $this->settings['adminName']],
                    'Ihr Abonnement',
                    'Mail/CreateUnsubscribe',
                    [
                        'subscription' => $existing,
                        'hash' => hash('sha256', $existing->getEmail() . $existing->getToken())
                    ]
                );
                $this->subscriptionRepository->update($existing);
            }
        } else {
            $this->redirect('showUnsubscribeForm', null, null, ['message' => 'E-Mail-Adresse nicht valide']);
        }

        $message = 'Wir haben eine E-Mail zum Kündigen des Abonnements an die Adresse <strong>' . $email . '</strong> gesendet.';

        $this->view->assignMultiple(compact('message'));
    }

    /**
     * @param Subscription $subscription
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createConfirmationAction(Subscription $subscription)
    {
        if (!FormProtectionFactory::get('frontend')
            ->validateToken(
                (string) GeneralUtility::_POST('formToken'),
                'Subscribe', 'show', $this->configurationManager->getContentObject()->data['uid']
            ))
        {
            $this->redirect('showForm');
        }
// E-Mail-Adresse bereits registriert?
        if ($existing = $this->subscriptionRepository->findOneByEmail($subscription->getEmail())) {
            // Abmelden Mail versenden
            /** @var Subscription $existing */
            $this->sendTemplateEmail(
                [$existing->getEmail() => $existing->getName()],
                [$this->settings['adminEmail'] => $this->settings['adminName']],
                'Ihr Abonnement',
                'Mail/AlreadySubscribed',
                [
                    'subscription' => $existing,
                    'hash' => hash('sha256', $existing->getEmail() . $existing->getToken())
                ]
            );
            //$this->subscriptionRepository->update($existing);

            $this->view->assignMultiple(['subscription' => $existing]);
        } else {
            // nächstes Mal statt token einfach das crdate nehmen?!
            // das könnte dann mit https://fluidtypo3.org/viewhelpers/vhs/master/Format/HashViewHelper.html
            // auch in beliebigen andern Fluid-Templates funktionieren :)
            $token = bin2hex(random_bytes(32));
            $subscription->setToken($token);
            $subscription->setHidden(1);
            $subscription->setModuleSysDmailHtml(true);
            $subscription->setSubscriptionConfirmed(0);
            $subscription->setPid($this->subscriptionRepository->createQuery()
                ->getQuerySettings()
                ->getStoragePageIds()[0]);

            $this->sendTemplateEmail(
                [$subscription->getEmail() => $subscription->getName()],
                [$this->settings['adminEmail'] => $this->settings['adminName']],
                'Ihr Abonnement',
                'Mail/Confirmation',
                [
                    'subscription' => $subscription,
                    'hash' => hash('sha256', $subscription->getEmail() . $token)
                ]
            );
            $this->subscriptionRepository->add($subscription);

            $this->view->assignMultiple(['subscription' => $subscription]);
        }
    }

    /**
     * @param string $email
     * @param string $hash
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function unsubscribeAction(?string $email = null, ?string $hash = null)
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findOneByEmail($email);

        if ($subscription) {
            if (hash('sha256', $subscription->getEmail() . $subscription->getToken()) == $hash) {
                $this->subscriptionRepository->remove($subscription);

                $message = 'Ihr Abonnement wurde beendet, alle Ihre Daten wurden endgültig aus unserer Datenbank gelöscht.';
            } else {
                $message = 'Dieser Link ist nicht mehr gültig';
            }
        } else {
            $message = 'Wir konnten kein entsprechendes Abonnement finden.';
        }
        $this->view->assignMultiple(compact('message', 'subscription'));
    }

    /**
     * @param string $email
     * @param string $hash
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function doConfirmAction(?string $email = null, ?string $hash = null)
    {

        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findOneByEmail($email);

        if ($subscription) {
            if (hash('sha256', $subscription->getEmail() . $subscription->getToken()) == $hash) {
                $subscription->setHidden(0);
                $subscription->setSubscriptionConfirmed(1);

                $this->subscriptionRepository->update($subscription);

                $message = 'Sie haben Ihr Abonnement erfolgreich bestätigt und werden ab sofort den Blickpunkt Infodienst erhalten!';
            } else {
                $message = 'Dieser Link ist nicht mehr gültig';
            }
        } else {
            $message = 'Wir konnten kein entsprechende Abonnement finden. Haben Sie Ihr Abonnement vielleicht schon bestätigt?';
        }

        $this->view->assignMultiple(compact('message', 'subscription'));
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
        $emailView->setLayoutRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:subscribe/Resources/Private/Layouts'))
        );
        $emailView->setPartialRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:subscribe/Resources/Private/Partials'))
        );
        $emailView->setTemplateRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:subscribe/Resources/Private/Templates'))
        );
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
}