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
 * A view helper which returns content elements with 'Show in Section Menus' enabled
 *
 * By default only content in colPos=0 will be found. This can be overruled by using "column"
 *
 * If you set property "type" to 'all', then the 'Show in Section Menus' checkbox is not considered
 * and all content elements are selected.
 *
 * If the property "type" is 'header' then only content elements with a visible header layout
 * (and a non-empty 'header' field!) are selected.
 * In other words, if the header layout of an element is set to 'Hidden' then the element will not be in the results.
 *
 * = Example =
 *
 * <code title="Content elements in page with uid = 1 and 'Show in Section Menu's' enabled">
 * <ce:menu.section pageUid="1" as="contentElements">
 *   <f:for each="{contentElements}" as="contentElement">
 *     {contentElement.header}
 *   </f:for>
 * </ce:menu.section>
 * </code>
 *
 * <output>
 * Content element 1 in page with uid = 1 and "Show in section menu's" enabled
 * Content element 2 in page with uid = 1 and "Show in section menu's" enabled
 * Content element 3 in page with uid = 1 and "Show in section menu's" enabled
 * </output>
 */
class SectionViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'Name of the template variable that will contain selected pages', true);
        $this->registerArgument('column', 'integer', 'Column number (colPos) from which to select content', false, 0);
        $this->registerArgument('pageUid', 'integer', 'UID of page containing section-objects; defaults to current page', false, null);
        $this->registerArgument('type', 'string', 'Search method when selecting indices from page', false, '');
    }

    /**
     * Render the view helper
     *
     * @return string
     */
    public function render()
    {
        $as = (string)$this->arguments['as'];
        $pageUid = (int)$this->arguments['pageUid'];
        $type = (string)$this->arguments['type'];

        if (empty($pageUid)) {
            $pageUid = $this->getTypoScriptFrontendController()->id;
        }

        if (!empty($type) && !in_array($type, array('all', 'header'), true)) {
            return '';
        }

        return $this->renderChildrenWithVariables(array(
            $as => $this->findBySection($pageUid, $type, (int)$this->arguments['column'])
        ));
    }

    /**
     * Find content with 'Show in Section Menus' enabled in a page
     *
     * By default only content in colPos=0 will be found. This can be overruled by using $column
     *
     * If you set property type to "all", then the 'Show in Section Menus' checkbox is not considered
     * and all content elements are selected.
     *
     * If the property $type is 'header' then only content elements with a visible header layout
     * (and a non-empty 'header' field!) is selected.
     * In other words, if the header layout of an element is set to 'Hidden' then the page will not appear in the menu.
     *
     * @param int $pageUid The page uid
     * @param string $type Search method
     * @param int $column Restrict content by the column number
     * @return array
     */
    protected function findBySection($pageUid, $type = '', $column = 0)
    {
        $constraints = array(
            'colPos = ' . (int)$column
        );

        switch ($type) {
            case 'all':
                break;
            case 'header':
                $constraints[] = 'sectionIndex = 1';
                $constraints[] = 'header <> \'\'';
                $constraints[] = 'header_layout <> 100';
                break;
            default:
                $constraints[] = 'sectionIndex = 1';
        }

        $whereStatement = implode(' AND ', $constraints);

        $contentElements = $this->getTypoScriptFrontendController()->cObj->getRecords('tt_content', array(
            'where' => $whereStatement,
            'orderBy' => 'sorting',
            'languageField = sys_language_uid',
            'pidInList' => (int)$pageUid
        ));

        return $contentElements;
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