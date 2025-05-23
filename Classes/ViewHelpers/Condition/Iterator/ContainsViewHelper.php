<?php
declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\ViewHelpers\Condition\Iterator;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface as RenderingContextInterfaceAlias;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Condition ViewHelper. Renders the then-child if Iterator/array
 * haystack contains needle value.
 *
 * ### Example:
 *
 *     {ns:condition.iterator.contains(needle: 'foo', haystack: {0: 'foo'}, then: 'yes', else: 'no')}
 */
class ContainsViewHelper extends AbstractConditionViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('needle', 'mixed', 'Needle to search for in haystack', true);
        $this->registerArgument('haystack', 'mixed', 'Haystack in which to look for needle', true);
        $this->registerArgument(
            'considerKeys',
            'boolean',
            'Tell whether to consider keys in the search assuming haystack is an array.',
            false,
            false
        );
    }
    
    /**
     * @param mixed[] $arguments
     * @param RenderingContextInterfaceAlias $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterfaceAlias $renderingContext): bool
    {
        return false !== static::assertHaystackHasNeedle($arguments['haystack'], $arguments['needle'], $arguments);
    }
    
    /**
     * @param integer $index
     * @param array $arguments
     * @return mixed
     */
    protected static function getNeedleAtIndex(int $index, array $arguments)
    {
        if (0 > $index) {
            return null;
        }
        $haystack = $arguments['haystack'];
        $asArray = [];
        if (true === is_array($haystack)) {
            $asArray = $haystack;
        } elseif (true === $haystack instanceof LazyObjectStorage) {
            /** @var $haystack LazyObjectStorage */
            $asArray = $haystack->toArray();
        } elseif (true === $haystack instanceof ObjectStorage) {
            /** @var $haystack ObjectStorage */
            $asArray = $haystack->toArray();
        } elseif (true === $haystack instanceof QueryResult) {
            /** @var $haystack QueryResult */
            $asArray = $haystack->toArray();
        } elseif (true === is_string($haystack)) {
            $asArray = str_split($haystack);
        }
        return (true === isset($asArray[$index]) ? $asArray[$index] : false);
    }
    
    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @param array $arguments
     * @return boolean|integer
     */
    protected static function assertHaystackHasNeedle($haystack, $needle, array $arguments): bool|int
    {
        if (true === is_array($haystack)) {
            return static::assertHaystackIsArrayAndHasNeedle($haystack, $needle, $arguments);
        } elseif ($haystack instanceof LazyObjectStorage) {
            return static::assertHaystackIsObjectStorageAndHasNeedle($haystack, $needle);
        } elseif ($haystack instanceof ObjectStorage) {
            return static::assertHaystackIsObjectStorageAndHasNeedle($haystack, $needle);
        } elseif ($haystack instanceof QueryResult) {
            return static::assertHaystackIsQueryResultAndHasNeedle($haystack, $needle);
        } elseif (true === is_string($haystack)) {
            return strpos($haystack, $needle);
        }
        return false;
    }
    
    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @return boolean|integer
     */
    protected static function assertHaystackIsQueryResultAndHasNeedle($haystack, $needle): bool|int
    {
        if (true === $needle instanceof DomainObjectInterface) {
            /** @var $needle DomainObjectInterface */
            $needle = $needle->getUid();
        }
        foreach ($haystack as $index => $candidate) {
            /** @var $candidate DomainObjectInterface */
            if ((integer) $candidate->getUid() === (integer) $needle) {
                return $index;
            }
        }
        return false;
    }
    
    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @return boolean|integer
     */
    protected static function assertHaystackIsObjectStorageAndHasNeedle($haystack, $needle): bool|int
    {
        $index = 0;
        /** @var $candidate DomainObjectInterface */
        if (true === $needle instanceof AbstractDomainObject) {
            $needle = $needle->getUid();
        }
        foreach ($haystack as $candidate) {
            if ((integer) $candidate->getUid() === (integer) $needle) {
                return $index;
            }
            $index++;
        }
        return false;
    }
    
    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @param array $arguments
     * @return boolean|integer
     */
    protected static function assertHaystackIsArrayAndHasNeedle($haystack, $needle, array $arguments): bool|int
    {
        if (false === $needle instanceof DomainObjectInterface) {
            if (true === (boolean) $arguments['considerKeys']) {
                $result = (boolean) (false !== array_search($needle, $haystack) || true === isset($haystack[$needle]));
            } else {
                $result = array_search($needle, $haystack);
            }
            return $result;
        } else {
            /** @var $needle DomainObjectInterface */
            foreach ($haystack as $index => $straw) {
                /** @var $straw DomainObjectInterface */
                if ((integer) $straw->getUid() === (integer) $needle->getUid()) {
                    return $index;
                }
            }
        }
        return false;
    }
    
    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @return boolean|integer
     */
    protected static function assertHaystackIsStringAndHasNeedle($haystack, $needle): bool|int
    {
        return strpos($haystack, $needle);
    }
}