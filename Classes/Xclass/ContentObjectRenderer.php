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

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Child class the content object renderer
 */
class ContentObjectRenderer extends \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * Executes a SELECT query for records from $table and with conditions based on the configuration in the $conf array
     * and overlays with translation and version if available
     *
     * @param string $tableName the name of the TCA database table
     * @param array $queryConfiguration The TypoScript configuration properties, see .select in TypoScript reference
     * @return array The records
     */
    public function getRecords($tableName, array $queryConfiguration)
    {
        $records = [];

        $res = $this->exec_getQuery($tableName, $queryConfiguration);

        $db = $this->getDatabaseConnection();
        if ($error = $db->sql_error()) {
            $this->getTimeTracker()->setTSlogMessage($error, 3);
        } else {
            $tsfe = $this->getTypoScriptFrontendController();
            while (($row = $db->sql_fetch_assoc($res)) !== false) {

                // Versioning preview:
                $tsfe->sys_page->versionOL($tableName, $row, true);

                // Language overlay:
                if (is_array($row) && $tsfe->sys_language_contentOL) {
                    if ($tableName === 'pages') {
                        $row = $tsfe->sys_page->getPageOverlay($row);
                    } else {
                        $row = $tsfe->sys_page->getRecordOverlay(
                            $tableName,
                            $row,
                            $tsfe->sys_language_content,
                            $tsfe->sys_language_contentOL
                        );
                    }
                }

                // Might be unset in the sys_language_contentOL
                if (is_array($row)) {
                    $records[] = $row;
                }
            }
            $db->sql_free_result($res);
        }

        return $records;
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \TYPO3\CMS\Core\TimeTracker\TimeTracker
     */
    protected function getTimeTracker()
    {
        return $GLOBALS['TT'];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $this->typoScriptFrontendController ?: $GLOBALS['TSFE'];
    }
}