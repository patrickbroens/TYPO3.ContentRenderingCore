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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * A view helper which returns a list of pages
 *
 * = Example =
 *
 * <code title="List of pages with uid = 1 and uid = 2">
 * <ce:menu.list pageUids="{0: 1, 1: 2}" as="pages">
 *   <f:for each="{pages}" as="page">
 *     {page.title}
 *   </f:for>
 * </ce:menu.list>
 * </code>
 *
 * <output>
 * Page with uid = 1
 * Page with uid = 2
 * </output>
 */
class ListViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'Name of template variable which will contain selected pages', true);
        $this->registerArgument('levelAs', 'string', 'Name of template variable which will contain current level', false, null);
        $this->registerArgument('pageUids', 'array', 'Page UIDs of parent pages', false, array());
        $this->registerArgument('entryLevel', 'integer', 'The entry level', false, null);
        $this->registerArgument('maximumLevel', 'integer', 'Maximum level for rendering of nested menus', false, 10);
        $this->registerArgument('includeNotInMenu', 'boolean', 'Include pages that are marked "hide in menu"?', false, false);
        $this->registerArgument('includeMenuSeparator', 'boolean', 'Include pages of the type "Menu separator"?', false, false);
    }

    /**
     * Render the view helper
     *
     * @return string
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function render()
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        $as = $this->arguments['as'];
        $pageUids = (array)$this->arguments['pageUids'];
        $entryLevel = $this->arguments['entryLevel'];
        $levelAs = $this->arguments['levelAs'];
        $maximumLevel = $this->arguments['maximumLevel'];
        $includeNotInMenu = (bool)$this->arguments['includeNotInMenu'];
        $includeMenuSeparator = (bool)$this->arguments['includeMenuSeparator'];

        $pageUids = $this->getPageUids($pageUids, $entryLevel);
        $pages = $typoScriptFrontendController->sys_page->getMenuForPages(
            $pageUids,
            '*',
            '',
            $this->getPageConstraints($includeNotInMenu, $includeMenuSeparator)
        );

        $output = '';

        if (!empty($pages)) {
            if (!$typoScriptFrontendController->register['ceMenuLevel_list']) {
                $typoScriptFrontendController->register['ceMenuLevel_list'] = 1;
                $typoScriptFrontendController->register['ceMenuMaximumLevel_list'] = $maximumLevel;
            } else {
                $typoScriptFrontendController->register['ceMenuLevel_list']++;
            }

            if ($typoScriptFrontendController->register['ceMenuLevel_list'] > $typoScriptFrontendController->register['ceMenuMaximumLevel_list']) {
                return '';
            }

            $variables = array(
                $as => $pages
            );
            if (!empty($levelAs)) {
                $variables[$levelAs] = $typoScriptFrontendController->register['ceMenuLevel_list'];
            }
            $output = $this->renderChildrenWithVariables($variables);

            $typoScriptFrontendController->register['ceMenuLevel_list']--;

            if ($typoScriptFrontendController->register['ceMenuLevel_list'] === 0) {
                unset($typoScriptFrontendController->register['ceMenuLevel_list']);
                unset($typoScriptFrontendController->register['ceMenuMaximumLevel_list']);
            }
        }

        return $output;
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