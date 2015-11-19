<?php
namespace PatrickBroens\ContentRenderingCore\Xclass;

/**                                                                       *
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use PatrickBroens\ContentRenderingCore\Page\PageRepositoryGetPageOverlayHookInterface;

/**
 * Page functions, a lot of sql/pages-related functions
 *
 * Mainly used in the frontend but also in some cases in the backend. It's
 * important to set the right $where_hid_del in the object so that the
 * functions operate properly
 * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::fetch_the_id()
 */
class PageRepository extends \TYPO3\CMS\Frontend\Page\PageRepository
{

    /**
     * Returns an array with page-rows for pages with uid in $pageIds.
     *
     * This is used for menus. If there are mount points in overlay mode
     * the _MP_PARAM field is set to the correct MPvar.
     *
     * @param int[] $pageIds Array of page ids to fetch
     * @param string $fields List of fields to select. Default is "*" = all
     * @param string $sortField The field to sort by. Default is "sorting"
     * @param string $additionalWhereClause Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
     * @param bool $checkShortcuts Check if shortcuts exist, checks by default
     * @return array Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
     */
    public function getMenuForPages(array $pageIds, $fields = '*', $sortField = 'sorting', $additionalWhereClause = '', $checkShortcuts = true)
    {
        return $this->getSubpagesForPages($pageIds, $fields, $sortField, $additionalWhereClause, $checkShortcuts, false);
    }

    /**
     * Internal method used by getMenu() and getMenuForPages()
     * Returns an array with page rows for subpages with pid is in $pageIds or uid is in $pageIds, depending on $parentPages
     * This is used for menus. If there are mount points in overlay mode
     * the _MP_PARAM field is set to the corret MPvar.
     *
     * If the $pageIds being input does in itself require MPvars to define a correct
     * rootline these must be handled externally to this function.
     *
     * @param int[] $pageIds The page id (or array of page ids) for which to fetch subpages (PID)
     * @param string $fields List of fields to select. Default is "*" = all
     * @param string $sortField The field to sort by. Default is "sorting
     * @param string $additionalWhereClause Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
     * @param bool $checkShortcuts Check if shortcuts exist, checks by default
     * @param bool $parentPages Whether the uid list is meant as list of parent pages or the page itself TRUE means id list is checked agains pid field
     * @return array Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
     * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::getPageShortcut(), \TYPO3\CMS\Frontend\ContentObject\Menu\AbstractMenuContentObject::makeMenu()
     * @see \TYPO3\CMS\WizardCrpages\Controller\CreatePagesWizardModuleFunctionController, \TYPO3\CMS\WizardSortpages\View\SortPagesWizardModuleFunction
     */
    protected function getSubpagesForPages(array $pageIds, $fields = '*', $sortField = 'sorting', $additionalWhereClause = '', $checkShortcuts = true, $parentPages = true)
    {
        $pages = [];
        $relationField = $parentPages ? 'pid' : 'uid';
        $db = $this->getDatabaseConnection();

        $whereStatement = $relationField . ' IN ('
            . implode(',', $db->cleanIntArray($pageIds)) . ')'
            . $this->where_hid_del
            . $this->where_groupAccess
            . ' '
            . $additionalWhereClause;

        // Check the user group access for draft pages in preview
        if ($this->versioningWorkspaceId != 0) {
            $databaseResource = $db->exec_SELECTquery(
                'uid',
                'pages',
                $relationField . ' IN (' . implode(',', $db->cleanIntArray($pageIds)) . ')'
                . $this->where_hid_del . ' ' . $additionalWhereClause,
                '',
                $sortField
            );

            $draftUserGroupAccessWhereStatement = $this->getDraftUserGroupAccessWhereStatement(
                $databaseResource,
                $sortField,
                $additionalWhereClause
            );

            if ($draftUserGroupAccessWhereStatement !== false) {
                $whereStatement = $draftUserGroupAccessWhereStatement;
            }
        };

        $databaseResource = $db->exec_SELECTquery(
            $fields,
            'pages',
            $whereStatement,
            '',
            $sortField
        );

        while (($page = $db->sql_fetch_assoc($databaseResource))) {
            $originalUid = $page['uid'];

            // Versioning Preview Overlay
            $this->versionOL('pages', $page, true);

            // Add a mount point parameter if needed
            $page = $this->addMountPointParameterToPage((array)$page);

            // If shortcut, look up if the target exists and is currently visible
            if ($checkShortcuts) {
                $page = $this->checkValidShortcutOfPage((array)$page, $additionalWhereClause);
            }

            // If the page still is there, we add it to the output
            if (!empty($page)) {
                $pages[$originalUid] = $page;
            }
        }

        $db->sql_free_result($databaseResource);

        // Finally load language overlays
        return $this->getPagesOverlay($pages);
    }

    /**
     * Prevent pages being shown in menu's for preview which contain usergroup access rights in a draft workspace
     *
     * Returns an adapted "WHERE" statement if pages are in draft
     *
     * @param bool|\mysqli_result|object $databaseResource MySQLi result object / DBAL object
     * @param string $sortField The field to sort by
     * @param string $addWhere Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
     * @return bool|string FALSE if no records are available in draft, a WHERE statement with the uid's if available
     */
    protected function getDraftUserGroupAccessWhereStatement($databaseResource, $sortField, $addWhere)
    {
        $draftUserGroupAccessWhereStatement = false;
        $recordArray = [];

        while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($databaseResource)) {
            $workspaceRow = $this->getWorkspaceVersionOfRecord($this->versioningWorkspaceId, 'pages', $row['uid']);

            $realUid = is_array($workspaceRow) ? $workspaceRow['uid'] : $row['uid'];

            $result = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                'uid',
                'pages',
                'uid=' . intval($realUid)
                . $this->where_hid_del
                . $this->where_groupAccess
                . ' ' . $addWhere,
                '',
                $sortField
            );

            if (is_array($result)) {
                $recordArray[] = $row['uid'];
            }
        }

        if (!empty($recordArray)) {
            $draftUserGroupAccessWhereStatement = 'uid IN (' . implode(',', $recordArray) . ')';
        }

        return $draftUserGroupAccessWhereStatement;
    }

    /**
     * Add the mount point parameter to the page if needed
     *
     * @param array $page The page to check
     * @return array
     */
    protected function addMountPointParameterToPage(array $page)
    {
        if (empty($page)) {
            return [];
        }

        // $page MUST have "uid", "pid", "doktype", "mount_pid", "mount_pid_ol" fields in it
        $mountPointInfo = $this->getMountPointInfo($page['uid'], $page);

        // There is a valid mount point.
        if (is_array($mountPointInfo) && $mountPointInfo['overlay']) {

            // Using "getPage" is OK since we need the check for enableFields AND for type 2
            // of mount pids we DO require a doktype < 200!
            $mountPointPage = $this->getPage($mountPointInfo['mount_pid']);

            if (!empty($mountPointPage)) {
                $page = $mountPointPage;
                $page['_MP_PARAM'] = $mountPointInfo['MPvar'];
            } else {
                $page = [];
            }
        }
        return $page;
    }

    /**
     * If shortcut, look up if the target exists and is currently visible
     *
     * @param array $page The page to check
     * @param string $additionalWhereClause Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
     * @return array
     */
    protected function checkValidShortcutOfPage(array $page, $additionalWhereClause)
    {
        if (empty($page)) {
            return [];
        }

        $dokType = (int)$page['doktype'];
        $shortcutMode = (int)$page['shortcut_mode'];

        if ($dokType === self::DOKTYPE_SHORTCUT && ($page['shortcut'] || $shortcutMode)) {
            if ($shortcutMode === self::SHORTCUT_MODE_NONE) {
                // No shortcut_mode set, so target is directly set in $page['shortcut']
                $searchField = 'uid';
                $searchUid = (int)$page['shortcut'];
            } elseif ($shortcutMode === self::SHORTCUT_MODE_FIRST_SUBPAGE || $shortcutMode === self::SHORTCUT_MODE_RANDOM_SUBPAGE) {
                // Check subpages - first subpage or random subpage
                $searchField = 'pid';
                // If a shortcut mode is set and no valid page is given to select subpags
                // from use the actual page.
                $searchUid = (int)$page['shortcut'] ?: $page['uid'];
            } elseif ($shortcutMode === self::SHORTCUT_MODE_PARENT_PAGE) {
                // Shortcut to parent page
                $searchField = 'uid';
                $searchUid = $page['pid'];
            } else {
                $searchField = '';
                $searchUid = 0;
            }

            $whereStatement = $searchField . '=' . $searchUid
                . $this->where_hid_del
                . $this->where_groupAccess
                . ' ' . $additionalWhereClause;

            $count = $this->getDatabaseConnection()->exec_SELECTcountRows(
                'uid',
                'pages',
                $whereStatement
            );

            if (!$count) {
                $page = [];
            }
        } elseif ($dokType === self::DOKTYPE_SHORTCUT) {
            // Neither shortcut target nor mode is set. Remove the page from the menu.
            $page = [];
        }
        return $page;
    }

    /**
     * Returns the relevant page overlay record fields
     *
     * @param array $pagesInput Array of integers or array of arrays. If each value is an integer, it's the pids of the pageOverlay records and thus the page overlay records are returned. If each value is an array, it's page-records and based on this page records the language records are found and OVERLAYED before the page records are returned.
     * @param int $lUid Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
     * @throws \UnexpectedValueException
     * @return array Page rows which are overlayed with language_overlay record.
     *               If the input was an array of integers, missing records are not
     *               included. If the input were page rows, untranslated pages
     *               are returned.
     */
    public function getPagesOverlay(array $pagesInput, $lUid = -1)
    {
        if (empty($pagesInput)) {
            return array();
        }
        // Initialize:
        if ($lUid < 0) {
            $lUid = $this->sys_language_uid;
        }
        $row = null;
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'])) {
            foreach ($pagesInput as $origPage) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'] as $classRef) {
                    $hookObject = GeneralUtility::getUserObj($classRef);
                    if (!$hookObject instanceof PageRepositoryGetPageOverlayHookInterface) {
                        throw new \UnexpectedValueException('$hookObject must implement interface ' . PageRepositoryGetPageOverlayHookInterface::class, 1269878881);
                    }
                    $hookObject->getPageOverlay_preProcess($origPage, $lUid, $this);
                }
            }
        }
        // If language UID is different from zero, do overlay:
        if ($lUid) {
            $fieldArr = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'], true);
            $page_ids = array();

            $origPage = reset($pagesInput);
            if (is_array($origPage)) {
                // Make sure that only fields which exist in the first incoming record are overlaid!
                $fieldArr = array_intersect($fieldArr, array_keys($origPage));
            }
            foreach ($pagesInput as $origPage) {
                if (is_array($origPage)) {
                    // Was the whole record
                    $page_ids[] = $origPage['uid'];
                } else {
                    // Was the id
                    $page_ids[] = $origPage;
                }
            }
            if (!empty($fieldArr)) {
                if (!in_array('pid', $fieldArr, true)) {
                    $fieldArr[] = 'pid';
                }
                // NOTE to enabledFields('pages_language_overlay'):
                // Currently the showHiddenRecords of TSFE set will allow
                // pages_language_overlay records to be selected as they are
                // child-records of a page.
                // However you may argue that the showHiddenField flag should
                // determine this. But that's not how it's done right now.
                // Selecting overlay record:
                $db = $this->getDatabaseConnection();
                $res = $db->exec_SELECTquery(
                    implode(',', $fieldArr),
                    'pages_language_overlay',
                    'pid IN(' . implode(',', $db->cleanIntArray($page_ids)) . ')'
                    . ' AND sys_language_uid=' . (int)$lUid . $this->enableFields('pages_language_overlay')
                );
                $overlays = array();
                while ($row = $db->sql_fetch_assoc($res)) {
                    $this->versionOL('pages_language_overlay', $row);
                    if (is_array($row)) {
                        $row['_PAGES_OVERLAY'] = true;
                        $row['_PAGES_OVERLAY_UID'] = $row['uid'];
                        $row['_PAGES_OVERLAY_LANGUAGE'] = $lUid;
                        $origUid = $row['pid'];
                        // Unset vital fields that are NOT allowed to be overlaid:
                        unset($row['uid']);
                        unset($row['pid']);
                        $overlays[$origUid] = $row;
                    }
                }
                $db->sql_free_result($res);
            }
        }
        // Create output:
        $pagesOutput = array();
        foreach ($pagesInput as $key => $origPage) {
            if (is_array($origPage)) {
                $pagesOutput[$key] = $origPage;
                if (isset($overlays[$origPage['uid']])) {
                    // Overwrite the original field with the overlay
                    foreach ($overlays[$origPage['uid']] as $fieldName => $fieldValue) {
                        if ($fieldName !== 'uid' && $fieldName !== 'pid') {
                            if ($this->shouldFieldBeOverlaid('pages_language_overlay', $fieldName, $fieldValue)) {
                                $pagesOutput[$key][$fieldName] = $fieldValue;
                            }
                        }
                    }
                }
            } else {
                if (isset($overlays[$origPage])) {
                    $pagesOutput[$key] = $overlays[$origPage];
                }
            }
        }
        return $pagesOutput;
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
}