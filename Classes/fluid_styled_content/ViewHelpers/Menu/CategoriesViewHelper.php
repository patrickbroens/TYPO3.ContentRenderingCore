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
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * A view helper which returns records with assigned categories
 *
 * = Example =
 *
 * <code title="Pages with categories 1 and 2 assigned">
 * <ce:menu.categories categoryUids="{0: 1, 1: 2}" as="pages" relationField="categories" table="pages">
 *   <f:for each="{pages}" as="page">
 *     {page.title}
 *   </f:for>
 * </ce:menu.categories>
 * </code>
 *
 * <output>
 * Page with category 1 assigned
 * Page with category 1 and 2 assigned
 * </output>
 */
class CategoriesViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('categoryUids', 'array', 'The categories assigned', true);
        $this->registerArgument('as', 'string', 'Name of the template variable that will contain resolved pages', true);
        $this->registerArgument('relationField', 'string', 'The category field for MM relation table', true);
        $this->registerArgument('table', 'string', 'The table to which categories are assigned (source table)', true);
    }

    /**
     * Render the view helper
     *
     * @return string
     */
    public function render()
    {
        $categoryUids = (array)$this->arguments['categoryUids'];
        $as = (string)$this->arguments['as'];
        if (empty($categoryUids)) {
            return '';
        }

        return $this->renderChildrenWithVariables(array(
            $as => $this->findByCategories($categoryUids, $this->arguments['relationField'], $this->arguments['table'])
        ));
    }

    /**
     * Find records from a certain table which have categories assigned
     *
     * @param array $categoryUids The uids of the categories
     * @param string $relationField Field relation in MM table
     * @param string $tableName Name of the table to search in
     * @return array
     * @throws Exception
     */
    protected function findByCategories($categoryUids, $relationField, $tableName = 'pages')
    {
        $result = array();

        foreach ($categoryUids as $categoryUid) {
            try {
                $collection = CategoryCollection::load(
                    $categoryUid,
                    true,
                    $tableName,
                    $relationField
                );
                if ($collection->count() > 0) {
                    foreach ($collection as $record) {
                        $result[$record['uid']] = $record;
                    }
                }
            } catch (\RuntimeException $e) {
                throw new Exception($e->getMessage());
            }
        }
        return $result;
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