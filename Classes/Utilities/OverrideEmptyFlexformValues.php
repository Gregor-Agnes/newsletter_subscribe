<?php

namespace Zwo3\NewsletterSubscribe\Utilities;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class OverrideEmptyFlexformValues
{
    public function overrideSettings($extensionName, $pluginName) {

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);

        $tsSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $extensionName,
            $pluginName
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
                    $originalSettings[$key] = $this->configurationManager->getContentObject()->stdWrap(
                        $typoScriptArray[$key],
                        $typoScriptArray[$key . '.']
                    );
                }
            }
        }

        // start override
        if (isset($tsSettings['settings']['overrideFlexformSettingsIfEmpty'])) {
            $originalSettings = $this->override($originalSettings, $tsSettings);
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Controller/NewsController.php']['overrideSettings'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Controller/NewsController.php']['overrideSettings'] as $_funcRef) {
                $_params = [
                    'originalSettings' => $originalSettings,
                    'tsSettings' => $tsSettings,
                ];
                $originalSettings = GeneralUtility::callUserFunction($_funcRef, $_params, $this);
            }
        }

        return $originalSettings;
    }

    /**
     * @param array $base
     * @param array $overload
     * @return array
     */
    protected function override(array $base, array $overload)
    {
        $validFields = GeneralUtility::trimExplode(',', $overload['settings']['overrideFlexformSettingsIfEmpty'], true);
        foreach ($validFields as $fieldName) {

            // Multilevel field
            if (strpos($fieldName, '.') !== false) {
                $keyAsArray = explode('.', $fieldName);

                $foundInCurrentTs = $this->getValue($base, $keyAsArray);

                if (is_string($foundInCurrentTs) && strlen($foundInCurrentTs) === 0) {
                    $foundInOriginal = $this->getValue($overload['settings'], $keyAsArray);
                    if ($foundInOriginal) {
                        $base = $this->setValue($base, $keyAsArray, $foundInOriginal);
                    }
                }
            } else {
                // if flexform setting is empty and value is available in TS
                if (((!isset($base[$fieldName]) || $base[$fieldName] === '0') || (strlen($base[$fieldName]) === 0))
                    && isset($overload['settings'][$fieldName])
                ) {
                    $base[$fieldName] = $overload['settings'][$fieldName];
                }
            }
        }
        return $base;
    }

    /**
     * Get value from array by path
     *
     * @param array $data
     * @param array $path
     * @return array|null
     */
    protected function getValue(array $data, array $path)
    {
        $found = true;

        for ($x = 0; ($x < count($path) && $found); $x++) {
            $key = $path[$x];

            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $found = false;
            }
        }

        if ($found) {
            return $data;
        }
        return null;
    }

    /**
     * Set value in array by path
     *
     * @param array $array
     * @param $path
     * @param $value
     * @return array
     */
    protected function setValue(array $array, $path, $value)
    {
        $this->setValueByReference($array, $path, $value);

        $final = array_merge_recursive([], $array);
        return $final;
    }

    /**
     * Set value by reference
     *
     * @param array $array
     * @param array $path
     * @param $value
     */
    private function setValueByReference(array &$array, array $path, $value)
    {
        while (count($path) > 1) {
            $key = array_shift($path);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }

        $key = reset($path);
        $array[$key] = $value;
    }
}