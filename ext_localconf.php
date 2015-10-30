<?php
defined('TYPO3_MODE') or die();

// Get the extension configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// Define TypoScript as content rendering template
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'content_rendering_core/Configuration/TypoScript/Static/';

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

if (!isset($extConf['loadContentElementWizardTsConfig']) || (int)$extConf['loadContentElementWizardTsConfig'] === 1) {
	// Include new content elements to modWizards
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:content_rendering_core/Configuration/PageTSconfig/NewContentElementWizard.ts">');
}
