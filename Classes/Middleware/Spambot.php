<?php
namespace Zwo3\NewsletterSubscribe\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Spambot implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $backendConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)?->get('newsletter_subscribe');
        $useSimpleSpamPrevention = (bool)($backendConfiguration['useSimpleSpamPrevention'] ?? false);
        if ($useSimpleSpamPrevention)
        {
            $iAmNotASpamBot = $request->getParsedBody()['iAmNotASpamBot'] ?? null;
            if ($iAmNotASpamBot !== null && $iAmNotASpamBot != $GLOBALS['TSFE']->fe_user->getKey('ses', 'i_am_not_a_robot'))
                /** @var FrontendUserAuthentication $frontendUser */
                $frontendUser = $this->request->getAttribute('frontend.user');
            if ($iAmNotASpamBot !== null && $iAmNotASpamBot !== $frontendUser->getSessionData('i_am_not_a_robot'));
            {
                $request = $request->withAttribute('spambotFailed', true);
            }
        }
        
        return $handler->handle($request);
    }
}