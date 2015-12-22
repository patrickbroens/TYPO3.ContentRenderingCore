<?php
namespace TYPO3\CMS\FluidStyledContent\ViewHelpers\Menu;

/*
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
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * A view helper which returns pages with one of the same keywords as the given pages
 *
 * Search default starts at the root of the current page tree.
 * With entryLevel this can be adjusted.
 *
 * = Example =
 *
 * <code title="Pages with the similar keyword(s) of page uid = 1 and uid = 2">
 * <ce:menu.keywords pageUids="{0: 1, 1: 2}" as="pages">
 *   <f:for each="{pages}" as="page">
 *     {page.title}
 *   </f:for>
 * </ce:menu.keywords>
 * </code>
 *
 * <output>
 * Page with the keywords "typo3" and "fluid"
 * Page with the keyword "fluid"
 * Page with the keyword "typo3"
 * </output>
 */
class KeywordsViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'Name of template variable which will contain selected pages', true);
        $this->registerArgument('entryLevel', 'integer', 'The entry level', false, 0);
        $this->registerArgument('pageUids', 'array', 'Page UIDs of pages to fetch the keywords from', false, array());
        $this->registerArgument('keywords', 'array', 'Keywords for which to search', false, array());
        $this->registerArgument('includeNotInMenu', 'boolean', 'Include pages that are marked "hide in menu"?', false, false);
        $this->registerArgument('includeMenuSeparator', 'boolean', 'Include pages of the type "Menu separator"?', false, false);
        $this->registerArgument('excludeNoSearchPages', 'boolean', 'Exclude pages that are NOT marked "include in search"?', false, true);
    }

    /**
     * Render the view helper
     *
     * @return string
     */
    public function render()
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        $as = (string)$this->arguments['as'];
        $entryLevel = (int)$this->arguments['entryLevel'];
        $pageUids = (array)$this->arguments['pageUids'];
        $keywords = (array)$this->arguments['keywords'];
        $includeNotInMenu = (bool)$this->arguments['includeNotInMenu'];
        $includeMenuSeparator = (bool)$this->arguments['includeMenuSeparator'];
        $excludeNoSearchPages = (bool)$this->arguments['excludeNoSearchPages'];

        // If no pages have been defined, use the current page
        if (empty($pageUids)) {
            $pageUids = array($typoScriptFrontendController->page['uid']);
        }

        // Transform the keywords list into an array
        if (!is_array($keywords)) {
            $unfilteredKeywords = $this->keywordsToArray($keywords);
        } else {
            $unfilteredKeywords = $keywords;
        }

        // Use the keywords of the page when none has been given
        if (empty($keywords)) {
            foreach ($pageUids as $pageUid) {
                $page = $typoScriptFrontendController->sys_page->getPage($pageUid);
                $unfilteredKeywords = array_merge(
                    $unfilteredKeywords,
                    $this->keywordsToArray($page['keywords'])
                );
            }
        }
        $filteredKeywords = array_unique($unfilteredKeywords);

        $constraints = $this->getPageConstraints($includeNotInMenu, $includeMenuSeparator);
        if ($excludeNoSearchPages) {
            $constraints .= ' AND no_search = 0';
        }

        $keywordConstraints = array();
        if ($filteredKeywords) {
            $db = $this->getDatabaseConnection();
            foreach ($filteredKeywords as $keyword) {
                $keyword = $db->fullQuoteStr('%' . $db->escapeStrForLike($keyword, 'pages') . '%', 'pages');
                $keywordConstraints[] = 'keywords LIKE ' . $keyword;
            }
            $constraints .= ' AND (' . implode(' OR ', $keywordConstraints) . ')';
        }

        // Start point
        if ($entryLevel < 0) {
            $entryLevel = count($typoScriptFrontendController->tmpl->rootLine) - 1 + $entryLevel;
        }
        $startUid = $typoScriptFrontendController->tmpl->rootLine[$entryLevel]['uid'];
        $treePageUids = explode(
            ',',
            $typoScriptFrontendController->cObj->getTreeList($startUid, 20)
        );

        $pages = $typoScriptFrontendController->sys_page->getMenuForPages(
            array_merge(array($startUid), $treePageUids),
            '*',
            '',
            $constraints
        );
        return $this->renderChildrenWithVariables(array(
            $as => $pages
        ));
    }

    /**
     * Get a clean array of keywords
     *
     * The list of keywords can have a separator like comma, semicolon or line feed
     *
     * @param string $keywords The list of keywords
     * @return array Cleaned up list
     */
    protected function keywordsToArray($keywords)
    {
        $keywordList = preg_split('/[,;' . LF . ']/', $keywords);

        return array_filter(array_map('trim', $keywordList));
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Get the constraints for the page based on doktype and field "nav_hide"
     *
     * By default the following doktypes are always ignored:
     * - 6: Backend User Section
     * - > 200: Folder (254)
     *          Recycler (255)
     *
     * Optional are:
     * - 199: Menu separator
     * - nav_hide: Not in menu
     *
     * @param bool $includeNotInMenu Should pages which are hidden for menu's be included
     * @param bool $includeMenuSeparator Should pages of type "Menu separator" be included
     * @return string
     */
    protected function getPageConstraints($includeNotInMenu = false, $includeMenuSeparator = false)
    {
        $constraints = array();
        $constraints[] = 'doktype NOT IN (' . PageRepository::DOKTYPE_BE_USER_SECTION . ',' . PageRepository::DOKTYPE_RECYCLER . ',' . PageRepository::DOKTYPE_SYSFOLDER . ')';
        if (!$includeNotInMenu) {
            $constraints[] = 'nav_hide = 0';
        }
        if (!$includeMenuSeparator) {
            $constraints[] = 'doktype != ' . PageRepository::DOKTYPE_SPACER;
        }
        return 'AND ' . implode(' AND ', $constraints);
    }

    /**
     * Get a filtered list of page UIDs according to initial list
     * of UIDs and entryLevel parameter.
     *
     * @param array $pageUids
     * @param int|NULL $entryLevel
     * @return array
     */
    protected function getPageUids(array $pageUids, $entryLevel = 0)
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        // Remove empty entries from array
        $pageUids = array_filter($pageUids);
        // If no pages have been defined, use the current page
        if (empty($pageUids)) {
            if ($entryLevel !== null) {
                if ($entryLevel < 0) {
                    $entryLevel = count($typoScriptFrontendController->tmpl->rootLine) - 1 + $entryLevel;
                }
                $pageUids = array($typoScriptFrontendController->tmpl->rootLine[$entryLevel]['uid']);
            } else {
                $pageUids = array($typoScriptFrontendController->id);
            }
        }
        return $pageUids;
    }

    /**
     * @param array $variables
     * @return mixed
     */
    protected function renderChildrenWithVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->templateVariableContainer->add($name, $value);
        }
        $output = $this->renderChildren();
        foreach ($variables as $name => $_) {
            $this->templateVariableContainer->remove($name);
        }
        return $output;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}