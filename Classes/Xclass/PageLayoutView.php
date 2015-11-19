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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Child class for the Web > Page module
 */
class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView
{

    /**
     * Create thumbnail code for record/field but not linked
     *
     * @param mixed[] $row Record array
     * @param string $table Table (record is from)
     * @param string $field Field name for which thumbnail are to be rendered.
     * @return string HTML for thumbnails, if any.
     */
    public function getThumbCodeUnlinked($row, $table, $field)
    {
        return BackendUtility::thumbCode($row, $table, $field, '', '', null, 0, '', '', false);
    }
}