<?php
declare(strict_types=1);

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

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Psr\Http\Message\ResponseInterface;
use Random\RandomException;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Event\Mvc\BeforeActionCallEvent;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use Zwo3\NewsletterSubscribe\Domain\Model\Subscription;
use Zwo3\NewsletterSubscribe\Domain\Repository\SubscriptionRepository;
use Zwo3\NewsletterSubscribe\Event\SubscriptionCancelledEvent;
use Zwo3\NewsletterSubscribe\Event\SubscriptionChangedEvent;
use Zwo3\NewsletterSubscribe\Event\SubscriptionConfirmedEvent;
use Zwo3\NewsletterSubscribe\Utility\TypoScript;

/**
 * Class SubscribeController
 *
 * @package Zwo3\NewsletterSubscribe\Controller
 */
class SubscribeController extends ActionController
{
    
    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;
    
    /*
     * @var SubscriptionRepository
     */
    private ?SubscriptionRepository $subscriptionRepository = null;
    
    /*
     * @var FormProtectionFactory
     */
    private ?FormProtectionFactory $formProtectionFactory = null;
    
    /** @var FrontendUserAuthentication */
    protected $frontendUser;
    
    public function injectSubscriptionRepository(SubscriptionRepository $subscriptionRepository): void
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }
    
    public function injectFormProtectionFactory(FormProtectionFactory $formProtectionFactory): void
    {
        $this->formProtectionFactory = $formProtectionFactory;
    }
    
    public function initializeAction(): void
    {
        $this->buildSettings();
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $this->frontendUser = $this->request->getAttribute('frontend.user');
    }
    
    /**
     * from "news" Extension
     */
    public function buildSettings(): void
    {
        $tsSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'NewsletterSubscribe',
            'newslettersubscribe_subscribe'
        );
        
        $originalSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
        
        // Use stdWrap for given defined settings
        if (isset($originalSettings['useStdWrap']) && !empty($originalSettings['useStdWrap'])) {
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $typoScriptArray = $typoScriptService->convertPlainArrayToTypoScriptArray($originalSettings);
            $stdWrapProperties = GeneralUtility::trimExplode(',', $originalSettings['useStdWrap'], true);
            foreach ($stdWrapProperties as $key) {
                if (is_array($typoScriptArray[$key . '.'])) {
                    $originalSettings[$key] = $this->configurationManager->getContentObject()
                        ->stdWrap(
                            $typoScriptArray[$key],
                            $typoScriptArray[$key . '.']
                        );
                }
            }
        }
        
        // start override
        if (isset($tsSettings['settings']['overrideFlexformSettingsIfEmpty'])) {
            $typoScriptUtility = GeneralUtility::makeInstance(TypoScript::class);
            $originalSettings = $typoScriptUtility->override($originalSettings, $tsSettings);
        }
        
        $this->settings = $originalSettings;
    }
    
    protected function getExtensionConfiguration(): array
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('newsletter_subscribe');
    }
    
    protected function useSimpleSpamPrevention(): bool
    {
        $backendConfiguration = $this->getExtensionConfiguration();
        
        return (bool)($this->settings['useSimpleSpamPrevention'] ?? false);
    }
    
    /**
     * @param null|Subscription $subscription
     * @param bool $spambotFailed
     * @throws \Exception
     * @IgnoreValidation("subscription"))
     */
    public function showFormAction(Subscription $subscription = null, bool $spambotFailed = null): ResponseInterface
    {
        $formProtection = $this->formProtectionFactory->createFromRequest($this->request);
        $formToken = $formProtection->generateToken(
            'Subscribe',
            'showForm',
            $this->request->getAttribute('currentContentObject')->data['uid']
        );
        $fields = GeneralUtility::trimExplode(',', (string)$this->settings['showFields'], true);
        
        if ($this->useSimpleSpamPrevention()) {
            $iAmNotASpamBotValue = bin2hex(random_bytes(16));
            $this->frontendUser->setAndSaveSessionData('i_am_not_a_robot', $iAmNotASpamBotValue);
            $this->view->assign('iAmNotASpamBotValue', $iAmNotASpamBotValue);
            if ($spambotFailed) {
                $this->view->assign('spambotFailed', 1);
            }
        }
        
        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'formToken' => $formToken,
            'fields' => $fields,
            'subscription' => $subscription,
        ]);
        
        return $this->htmlResponse();
    }
    
    /**
     * @param string $message
     */
    public function showUnsubscribeFormAction(?string $message = null): ResponseInterface
    {
        $formProtection = $this->formProtectionFactory->createFromRequest($this->request);
        $formToken = $formProtection->generateToken(
            'Subscribe',
            'showUnsubscribeForm',
            $this->request->getAttribute('currentContentObject')->data['uid']
        );
        
        $this->view->assignMultiple([
            'dataProtectionPage' => $this->settings['dataProtectionPage'],
            'message' => $message,
            'formToken' => $formToken
        ]);
        
        return $this->htmlResponse();
    }
    
    /**
     * @param string $email
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function createUnsubscribeMailAction(?string $email = null): ?ResponseInterface
    {
        $formProtection = $this->formProtectionFactory->createFromRequest($this->request);
        if (!$formProtection->validateToken(
            (string)($this->request->getParsedBody()['formToken'] ?? ''),
            'Subscribe',
            'showUnsubscribeForm',
            $this->request->getAttribute('currentContentObject')->data['uid']
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
                $name = $existing->getName() ?: LocalizationUtility::translate('nameEmpty', 'NewsletterSubscribe');
                $subject = $this->settings['newsletterName'];
                $subject .= $subject ? ' - ' : '';
                $subject .= LocalizationUtility::translate('subjectUnsubscribe', 'NewsletterSubscribe');
                try {
                    $this->sendTemplateEmail(
                        [$existing->getEmail(), $name],
                        [$this->settings['senderEmail'], $this->settings['senderName']],
                        $subject,
                        'CreateUnsubscribe',
                        [
                            'subscription' => $existing,
                            'settings' => $this->settings,
                        ]
                    );
                    $this->subscriptionRepository->update($existing);
                } catch (InvalidTemplateResourceException $exception) {
                    $this->addFlashMessage(
                        'Create a template in the Mail Folder for the current language (e.g. de, fr, dk).',
                        'No E-Mail-Template found',
                        ContextualFeedbackSeverity::ERROR
                    );
                }
            }
        } else {
            $this->redirect('showUnsubscribeForm', null, null, ['message' => 'E-Mail-Adresse nicht valide']);
        }
        
        $this->view->assignMultiple(compact('email'));
        
        return $this->htmlResponse();
    }
    
    public function initializeCreateConfirmationAction(): ?ResponseInterface
    {
        if (!$this->request->hasArgument('subscription')) {
            return (new ForwardResponse('showForm'))
                ->withControllerName('Subscribe')
                ->withExtensionName('newsletter_subscribe');
        }
        
        return null;
    }
    
    /**
     * @param Subscription|null $subscription
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws RandomException
     */
    public function createConfirmationAction(Subscription $subscription = null): ResponseInterface
    {
        if (!$subscription) {
            return $this->redirect('showForm');
        }
        if ($this->useSimpleSpamPrevention()) {
            if (
                !empty($this->request->getParsedBody()['iAmNotASpamBotHere'] ?? '') ||
                ($this->request->getParsedBody()['iAmNotASpamBot'] ?? '') !== $this->frontendUser->getSessionData('i_am_not_a_robot')
            ) {
                sleep((int)$this->settings['spamTimeout']);
                
                return (new ForwardResponse('showForm'))->withArguments(['subscription' => $subscription, 'spambotFailed' => true]);
            }
        }
        
        if ($this->settings['useHCaptcha'] ?? false) {
            if (($this->request->getParsedBody()['h-captcha-response'] ?? false)) {
                $data = [
                    'secret' => $this->settings['hCaptchaSecretKey'],
                    'response' => $this->request->getParsedBody()['h-captcha-response'] ?? $this->request->getQueryParams()['h-captcha-response'] ?? null
                ];
                $verify = curl_init();
                curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
                curl_setopt($verify, CURLOPT_POST, true);
                curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($verify);
                // var_dump($response);
                $responseData = json_decode($response);
                if ($responseData->success) {
                    // your success code goes here
                    /*
                    $this->addFlashMessage(
                        'Super, geschafft!',
                        '',
                        ContextualFeedbackSeverity::ERROR
                    );
                    */
                } else {
                    $this->addFlashMessage(
                        LocalizationUtility::translate('captchaWrong', 'NewsletterSubscribe'),
                        '',
                        ContextualFeedbackSeverity::ERROR
                    );
                    
                    return (new ForwardResponse('showForm'))->withArguments(['subscription' => $subscription]);
                }
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate('captchaWrong', 'NewsletterSubscribe'),
                    '',
                    ContextualFeedbackSeverity::ERROR
                );
                
                return (new ForwardResponse('showForm'))->withArguments(['subscription' => $subscription]);
            }
        }
        
        $formProtection = $this->formProtectionFactory->createFromRequest($this->request);
        if (!$formProtection->validateToken(
            (string)($this->request->getParsedBody()['formToken'] ?? ''),
            'Subscribe',
            'showForm',
            $this->request->getAttribute('currentContentObject')->data['uid']
        )) {
            return (new ForwardResponse('showForm'))->withArguments(['subscription' => $subscription]);
        }
        // already subscribed
        if ($existing = $this->subscriptionRepository->findOneByEmail($subscription->getEmail(), false)) {
            // Abmelden Mail versenden
            /** @var Subscription $existing */
            $subject = $this->settings['newsletterName'];
            $subject .= $subject ? ' - ' : '';
            $subject .= LocalizationUtility::translate('yourSubscription', 'NewsletterSubscribe');
            try {
                $this->sendTemplateEmail(
                    [$existing->getEmail(), ($existing->getName() ?: '')],
                    [$this->settings['senderEmail'], $this->settings['senderName']],
                    $subject,
                    'AlreadySubscribed',
                    [
                        'subscription' => $existing,
                    ]
                );
            } catch (InvalidTemplateResourceException $exception) {
                $this->addFlashMessage(
                    'Create a template in the Mail Folder for the current language (e.g. de, fr, dk).',
                    'No E-Mail-Template found',
                    ContextualFeedbackSeverity::ERROR
                );
            }
            
            //$this->subscriptionRepository->update($existing);
            
            $this->view->assignMultiple(['subscription' => $existing]);
        } else {
            $subscription->setHidden(1);
            if (ExtensionManagementUtility::isLoaded('direct_mail')) {
                $subscription->setModuleSysDmailNewsletter(true);
                $subscription->setModuleSysDmailHtml(true);
            }
            $subscription->setCrdate(time());
            $subscription->setSubscriptionHash(hash('sha256', $subscription->getEmail() . $subscription->getCrdate() . random_bytes(32)));
            $subscription->setPid($this->subscriptionRepository->createQuery()
                ->getQuerySettings()
                ->getStoragePageIds()[0]);
            $subscription->setName($subscription->getFirstName() . " " . $subscription->getLastName());
            
            $this->addSalutation($subscription);
            
            $this->subscriptionRepository->add($subscription);
            $this->persistenceManager->persistAll();
            $subject = $this->settings['newsletterName'];
            $subject .= $subject ? ' - ' : '';
            $subject .= LocalizationUtility::translate('yourSubscription', 'NewsletterSubscribe');
            try {
                $this->sendTemplateEmail(
                    [$subscription->getEmail(), ($subscription->getName() ?: '')],
                    [$this->settings['senderEmail'], $this->settings['senderName']],
                    $subject,
                    'Confirmation',
                    [
                        'subscription' => $subscription,
                        'settings' => $this->settings,
                    ]
                );
            } catch (InvalidTemplateResourceException $exception) {
                $this->addFlashMessage(
                    'Create a template in the Mail Folder for the current language (e.g. de, fr, dk).',
                    'No E-Mail-Template found',
                    ContextualFeedbackSeverity::ERROR
                );
            }
            
            $this->view->assignMultiple(['subscription' => $subscription]);
        }
        
        return $this->htmlResponse();
    }
    
    /**
     * @param int $uid
     * @param string $subscriptionHash
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function unsubscribeAction(?int $uid = null, ?string $subscriptionHash = null): ResponseInterface
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findSubscriptionByUid($uid, false);
        $success = false;
        $alreadySubscribed = false;
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
            /** @var ErrorController $errorController */
            $errorController = GeneralUtility::makeInstance(ErrorController::class);
            $response = $errorController->pageNotFoundAction(
                \TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals(),
                'Page not found',
                ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
            );
            throw new ImmediateResponseException($response);
        }
        
        if ($success && $this->settings['sendAdminInfo']) {
            $this->sendAdminInfo($subscription, 0);
        }
        
        if ($success) {
            $this->eventDispatcher->dispatch(new SubscriptionChangedEvent(static::class, $this->actionMethodName, $subscription, 'unsubscribe'));
        }
        
        $this->view->assignMultiple(compact('subscription', 'success'));
        return $this->htmlResponse();
    }
    
    /**
     * @param int $uid
     * @param string $subscriptionHash
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function doConfirmAction(?int $uid = null, ?string $subscriptionHash = null): ResponseInterface
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findSubscriptionByUid($uid, false);
        $alreadySubscribed = false;
        
        $success = false;
        if ($subscription) {
            if ($subscriptionHash == $subscription->getSubscriptionHash() && $subscription->isHidden()) {
                $subscription->setHidden(0);
                $this->subscriptionRepository->update($subscription);
                $success = true;
            } else {
                if ($this->settings['sendPageNotFoundOnInvalidConfirmation']) {
// increasing sleeptimer
                    $subscription = $this->setSleep($subscription, 300, 2);
                    $this->subscriptionRepository->update($subscription);
                    /** @var ErrorController $errorController */
                    $errorController = GeneralUtility::makeInstance(ErrorController::class);
                    $response = $errorController
                        ->pageNotFoundAction(
                            \TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals(),
                            'Page not found',
                            ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
                        );
                    throw new ImmediateResponseException($response);
                } else {
                    $alreadySubscribed = true;
                }
            }
        } else {
            /** @var ErrorController $errorController */
            $errorController = GeneralUtility::makeInstance(ErrorController::class);
            $response = $errorController->pageNotFoundAction(
                    \TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals(),
                    'Page not found',
                    ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
                );
            throw new ImmediateResponseException($response);
        }
        
        if ($success && $this->settings['sendAdminInfo']) {
            $this->sendAdminInfo($subscription, 1);
        }
        
        if ($success) {
            $this->eventDispatcher->dispatch(new SubscriptionChangedEvent(static::class, $this->actionMethodName, $subscription, 'subscribe'));
        }
        $this->view->assignMultiple(compact('subscription', 'success', 'alreadySubscribed'));
        
        return $this->htmlResponse();
    }
    
    public function sendAdminInfo(Subscription $subscription, int $unsubscribeaction = 0): void
    {
        $subject = $unsubscribeaction == 0 ? LocalizationUtility::translate('unsubscription', 'newsletterSubscribe') : LocalizationUtility::translate('newSubscription', 'newsletterSubscribe');
        
        try {
            $this->sendTemplateEmail(
                [$this->settings['adminEmail'], $this->settings['adminName']],
                [$this->settings['adminEmail'], $this->settings['adminName']],
                $subject,
                'AdminInfo',
                [
                    'subscription' => $subscription,
                ],
                [$subscription->getEmail(), ($subscription->getName() ?: '')]
            );
        } catch (InvalidTemplateResourceException $exception) {
            $this->addFlashMessage(
                'Template for AdminInfo Missing',
                'No E-Mail-Template found',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }
    
    protected function addSalutation(&$subscription): void
    {
        if (isset($this->settings['addsalutation']) && $this->settings['addsalutation']) {
            $twoLetterIsoCode = $this->prepareTwoLetterIsoCode();
            if (isset($this->settings['salutation'][$twoLetterIsoCode])) {
                if (isset($this->settings['salutation'][$twoLetterIsoCode][$subscription->getGender()]) && $subscription->getLastName()) {
                    $salutation = $this->settings['salutation'][$twoLetterIsoCode][$subscription->getGender()];
                    $salutation .= $subscription->getTitle() ? ' ' . $subscription->getTitle() : '';
                    $salutation .= ' ' . $subscription->getLastName();
                } else {
                    $salutation = $this->settings['salutation'][$twoLetterIsoCode]['default'];
                }
                
                $subscription->setSalutation($salutation);
            }
        }
    }
    
    /**
     * @return string
     */
    protected function getTwoLetterIsoCodeFromSiteConfig(): string
    {
        $site = $this->request->getAttribute('site');
        $languageAspect = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)
            ->getAspect('language');
        $language = $site->getLanguageById($languageAspect->getId());
        
        return $language->getLocale()
            ->getLanguageCode();
    }
    
    /**
     * @return string
     */
    protected function prepareTwoLetterIsoCode(): string
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $versionString = $typo3Version->getVersion();
        $version = explode('.', $versionString);
        
        if ($version[0] < 9) {
            $twoLetterIsoCode = $GLOBALS['TSFE']->config['config']['language'];
        } else {
            $twoLetterIsoCode = $this->getTwoLetterIsoCodeFromSiteConfig();
        }
        return $twoLetterIsoCode;
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
    protected function sendTemplateEmail(
        array $recipient,
        array $sender,
        string $subject,
        string $templateName = 'Mail/Default',
        array $variables = [],
        array $replyTo = null,
        array $attachments = []
    ): bool {
        $templatePaths = new TemplatePaths();
        
        if (mb_stripos($templateName, 'admin') !== false) {
            // Admin Mail, no translation possible and necessary
            $templatePaths->setTemplateRootPaths(
                [GeneralUtility::getFileAbsFileName($this->settings['mailTemplateRootPath'])]
            );
        } else {
            // User Mails
            $twoLetterIsoCode = $this->prepareTwoLetterIsoCode();
            $templatePaths->setTemplateRootPaths(
                [GeneralUtility::getFileAbsFileName($this->settings['mailTemplateRootPath'] . $twoLetterIsoCode . '/')]
            );
        }
        $templatePaths->setLayoutRootPaths([$this->settings['mailLayoutRootPath'] . '/']);
        
        /** @var FluidEmail $email */
        $email = GeneralUtility::makeInstance(FluidEmail::class, $templatePaths);
        $email->format(FluidEmail::FORMAT_HTML);
        $email
            ->to(new Address(...$recipient))
            ->from(new Address(...$sender))
            ->subject($subject)
            ->setTemplate($templateName)
            ->assignMultiple($variables);
        
        if ($replyTo) {
            $email->replyTo(new Address(...$replyTo));
        }
        $email->setRequest($this->request);
        GeneralUtility::makeInstance(MailerInterface::class)
            ->send($email);
        
        return true;
    }
    
    /**
     * @param Subscription $subscription
     * @param int $maxSleeptime max time to wait after last hit, if reached, sleep is resetted
     * @param int $multiplier multipliere * hitnumber = seconds to wait,
     * @return Subscription
     */
    protected function setSleep(
        Subscription $subscription,
        int $maxSleeptime = 300,
        int $multiplier = 2
    ): Subscription {
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
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }
    
}
