<?php
/*
 * Register necessary class names with autoloader
 */
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('content_rendering_core');

$extensionNamespaces = array(
    'core' => 'TYPO3\CMS\Core',
    'fluid' => 'TYPO3\CMS\Fluid',
    'fluid_styled_content' => 'TYPO3\CMS\FluidStyledContent',
    'frontend' => 'TYPO3\CMS\Frontend'
);

$classes = array(
    'core' => array(
        'Resource\OnlineMedia\Helpers\AbstractOEmbedHelper',
        'Resource\OnlineMedia\Helpers\AbstractOnlineMediaHelper',
        'Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface',
        'Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry',
        'Resource\OnlineMedia\Helpers\VimeoHelper',
        'Resource\OnlineMedia\Helpers\YouTubeHelper',
        'Resource\OnlineMedia\Metadata\Extractor',
        'Resource\OnlineMedia\Processing\PreviewProcessing',
        'Resource\Rendering\AudioTagRenderer',
        'Resource\Rendering\FileRendererInterface',
        'Resource\Rendering\RendererRegistry',
        'Resource\Rendering\VideoTagRenderer',
        'Resource\Rendering\VimeoRenderer',
        'Resource\Rendering\YouTubeRenderer',
        'Utility\CsvUtility'
    ),
    'fluid' => array(
        'ViewHelpers\Link\TypolinkViewHelper',
        'ViewHelpers\MediaViewHelper'
    ),
    'fluid_styled_content' => array(
        'Hooks\TextmediaPreviewRenderer',
        'ViewHelpers\Link\ClickEnlargeViewHelper',
        'ViewHelpers\Menu\CategoriesViewHelper',
        'ViewHelpers\Menu\DirectoryViewHelper',
        'ViewHelpers\Menu\KeywordsViewHelper',
        'ViewHelpers\Menu\ListViewHelper',
        'ViewHelpers\Menu\MenuViewHelperTrait',
        'ViewHelpers\Menu\SectionViewHelper',
        'ViewHelpers\Menu\UpdatedViewHelper'
    ),
    'frontend' => array(
        'ContentObject\Exception\ContentRenderingException',
        'ContentObject\ContentDataProcessor',
        'ContentObject\DataProcessorInterface',
        'DataProcessing\CommaSeparatedValueProcessor',
        'DataProcessing\DatabaseQueryProcessor',
        'DataProcessing\FilesProcessor',
        'DataProcessing\GalleryProcessor',
        'DataProcessing\SplitProcessor',
        'Resource\FileCollector',
        'Service\TypoLinkCodecService'
    )
);

$autoloadArray = array();

foreach ($classes as $extensionName => $extensionClasses) {
    foreach ($extensionClasses as $extensionClass) {
        $autoloadArray[$extensionNamespaces[$extensionName] . '\\' . $extensionClass] = $extensionPath . 'Classes/' . $extensionName . '/' . str_replace('\\', '/', $extensionClass) . '.php';
    }
}

return $autoloadArray;