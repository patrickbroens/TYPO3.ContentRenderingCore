<?php
namespace PatrickBroens\ContentRenderingCore\Xclass;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Child class for the command utility
 */
class CommandUtility extends \TYPO3\CMS\Core\Utility\CommandUtility
{
    /**
     * Escape shell arguments (for example filenames) to be used on the local system.
     *
     * The setting UTF8filesystem will be taken into account.
     *
     * @param string[] $input Input arguments to be escaped
     * @return string[] Escaped shell arguments
     */
    public static function escapeShellArguments(array $input)
    {
        $isUTF8Filesystem = !empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem']);
        if ($isUTF8Filesystem) {
            $currentLocale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']);
        }

        $output = array_map('escapeshellarg', $input);

        if ($isUTF8Filesystem) {
            setlocale(LC_CTYPE, $currentLocale);
        }

        return $output;
    }
}