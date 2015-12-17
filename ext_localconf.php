<?php
defined('TYPO3_MODE') or die();

// Get the extension configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// Define TypoScript as content rendering template
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'contentrenderingcore/Configuration/TypoScript/Static/';

// Register for hook to show preview of tt_content element of CType="textmedia" in page module
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['textmedia'] = \PatrickBroens\ContentRenderingCore\Hooks\TextmediaPreviewRenderer::class;

// Overload (XCLASS) the FLUIDTEMPLATE content object for use of Data Processors
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\FluidTemplateContentObject'] = array(
    'className' => 'PatrickBroens\\ContentRenderingCore\\Xclass\\FluidTemplateContentObject'
);

// Overload (XCLASS) the FLUID StandaloneView to make use of templateRootPaths
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\View\\StandaloneView'] = array(
    'className' => 'PatrickBroens\\ContentRenderingCore\\Xclass\\StandaloneView'
);

// Overload (XCLASS) the PageRepository to use method getMenuForPages
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Page\\PageRepository'] = array(
    'className' => 'PatrickBroens\\ContentRenderingCore\\Xclass\\PageRepository'
);

// Overload (XCLASS) the PageLayoutView to use method getThumbCodeUnlinked
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\Backend\\View\\PageLayoutView'] = array(
    'className' => 'PatrickBroens\\ContentRenderingCore\\Xclass\\PageLayoutView'
);

// Commalist of file extensions perceived as media files by TYPO3. Lowercase and no spaces between!
$GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] = 'gif,jpg,jpeg,bmp,png,pdf,svg,ai,mp3,wav,mp4,webm,youtube,vimeo';

// Include new content elements to mod.wizards
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:content_rendering_core/Configuration/PageTSconfig/NewContentElementWizard.ts">'
);

// Exclude non used content elements from core
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:content_rendering_core/Configuration/PageTSconfig/TCEFORM.ts">'
);

// Register upgrade wizard to migrate FlexForm data for CE "table" to regular database fields
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['tableCType'] = \PatrickBroens\ContentRenderingCore\Updates\TableFlexFormToTtContentFieldsUpdate::class;
// Register upgrade wizard to migrate the old Ctypes "text", "image" and "textpic" to "textmedia"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['textmediaCType'] = \PatrickBroens\ContentRenderingCore\Updates\ContentTypesToTextMediaUpdate::class;
// Register upgrade wizard to migrate the field "media" to "assets" for the CE "textmedia"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['textmediaAssets'] = \PatrickBroens\ContentRenderingCore\Updates\MigrateMediaToAssetsForTextMediaCe::class;
