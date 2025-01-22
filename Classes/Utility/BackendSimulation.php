<?php

declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\Utility;

use TYPO3\CMS\Core\Http\ServerRequest;

class BackendSimulation
{
    /**
     * @template T
     * @param callable(): mixed $closure
     * @phpstan-param callable(): T $closure
     */
    public static function runWithSimulateBackend(callable $closure): mixed
    {
        $simulateBackend = false;
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            $simulateBackend = true;

            /* @see \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE */
            $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())->withAttribute('applicationType', 2);
        }

        $result = $closure();

        if ($simulateBackend) {
            unset($GLOBALS['TYPO3_REQUEST']);
        }

        return $result;
    }
}
