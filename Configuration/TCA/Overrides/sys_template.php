<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('content_rendering_core', 'Configuration/TypoScript/Static/', 'Content Elements');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('content_rendering_core', 'Configuration/TypoScript/Styling/', 'Content Elements CSS (optional)');
