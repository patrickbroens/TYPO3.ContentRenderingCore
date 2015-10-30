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

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use PatrickBroens\ContentRenderingCore\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains FLUIDTEMPLATE class object
 */
class FluidTemplateContentObject extends \TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject {

	/**
	 * @var ContentDataProcessor
	 */
	protected $contentDataProcessor;

	/**
	 * @param ContentObjectRenderer $cObj
	 */
	public function __construct(ContentObjectRenderer $cObj) {
		parent::__construct($cObj);
		$this->contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
	}

	/**
	 * Rendering the cObject, FLUIDTEMPLATE
	 *
	 * Configuration properties:
	 * - file string+stdWrap The FLUID template file
	 * - layoutRootPaths array of filepath+stdWrap Root paths to layouts (fallback)
	 * - partialRootPaths array of filepath+stdWrap Root paths to partials (fallback)
	 * - variable array of cObjects, the keys are the variable names in fluid
	 * - dataProcessing array of data processors which are classes to manipulate $data
	 * - extbase.pluginName
	 * - extbase.controllerExtensionName
	 * - extbase.controllerName
	 * - extbase.controllerActionName
	 *
	 * Example:
	 * 10 = FLUIDTEMPLATE
	 * 10.templateName = MyTemplate
	 * 10.templateRootPaths.10 = EXT:site_configuration/Resources/Private/Templates/
	 * 10.partialRootPaths.10 = EXT:site_configuration/Resources/Private/Patials/
	 * 10.layoutRootPaths.10 = EXT:site_configuration/Resources/Private/Layouts/
	 * 10.variables {
	 *   mylabel = TEXT
	 *   mylabel.value = Label from TypoScript coming
	 * }
	 *
	 * @param array $conf Array of TypoScript properties
	 * @return string The HTML output
	 */
	public function render($conf = array()) {
		$parentView = $this->view;
		$this->initializeStandaloneViewInstance();

		if (!is_array($conf)) {
			$conf = array();
		}

		$this->setFormat($conf);
		$this->setTemplate($conf);
		$this->setLayoutRootPath($conf);
		$this->setPartialRootPath($conf);
		$this->setExtbaseVariables($conf);
		$this->assignSettings($conf);
		$variables = $this->getContentObjectVariables($conf);
		$variables = $this->contentDataProcessor->process($this->cObj, $conf, $variables);

		$this->view->assignMultiple($variables);

		$content = $this->renderFluidView();
		$content = $this->applyStandardWrapToRenderedContent($content, $conf);

		$this->view = $parentView;
		return $content;
	}

	/**
	 * Set template
	 *
	 * @param array $conf With possibly set file resource
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	protected function setTemplate(array $conf) {
		// Fetch the Fluid template by templateName
		if (!empty($conf['templateName']) && !empty($conf['templateRootPaths.']) && is_array($conf['templateRootPaths.'])) {
			$templateRootPaths = $this->applyStandardWrapToFluidPaths($conf['templateRootPaths.']);
			$this->view->setTemplateRootPaths($templateRootPaths);
			$templateName = isset($conf['templateName.']) ? $this->cObj->stdWrap($conf['templateName'], $conf['templateName.']) : $conf['templateName'];
			$this->view->setTemplate($templateName);
		// Fetch the Fluid template by template cObject or file stdWrap
		} else {
			parent::setTemplate($conf);
		}
	}

	/**
	 * Compile rendered content objects in variables array ready to assign to the view
	 *
	 * @param array $conf Configuration array
	 * @return array the variables to be assigned
	 * @throws \InvalidArgumentException
	 */
	protected function getContentObjectVariables(array $conf) {
		$variables = array();
		$reservedVariables = array('data', 'current');
		// Accumulate the variables to be process and loop them through cObjGetSingle
		$variablesToProcess = (array)$conf['variables.'];
		foreach ($variablesToProcess as $variableName => $cObjType) {
			if (is_array($cObjType)) {
				continue;
			}
			if (!in_array($variableName, $reservedVariables)) {
				$variables[$variableName] = $this->cObj->cObjGetSingle($cObjType, $variablesToProcess[$variableName . '.']);
			} else {
				throw new \InvalidArgumentException(
					'Cannot use reserved name "' . $variableName . '" as variable name in FLUIDTEMPLATE.',
					1288095720
				);
			}
		}
		$variables['data'] = $this->cObj->data;
		$variables['current'] = $this->cObj->data[$this->cObj->currentValKey];
		return $variables;
	}
}