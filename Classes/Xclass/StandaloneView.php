<?php
namespace PatrickBroens\ContentRenderingCore\Xclass;

/**                                                                       *
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A standalone template view.
 * Should be used as view if you want to use Fluid without Extbase extensions
 *
 * @api
 */
class StandaloneView extends \TYPO3\CMS\Fluid\View\StandaloneView {

	/**
	 * Path(s) to the template root
	 *
	 * @var string[]
	 */
	protected $templateRootPaths = null;

	/**
	 * Set the root path(s) to the templates.
	 *
	 * @param string[] $templateRootPaths Root paths to the templates.
	 * @return void
	 * @api
	 */
	public function setTemplateRootPaths(array $templateRootPaths) {
		$this->templateRootPaths = $templateRootPaths;
	}

	/**
	 * Set template by name
	 * All set templateRootPaths are checked to find template by given name
	 *
	 * @param string $templateName Name of the template
	 * @throws InvalidTemplateResourceException
	 * @api
	 */
	public function setTemplate($templateName) {
		if ($this->templateRootPaths === null) {
			throw new InvalidTemplateResourceException('No template root path has been specified. Use setTemplateRootPaths().', 1430635895);
		}
		$format = $this->getRequest()->getFormat();
		$templatePathAndFilename = null;
		$possibleTemplatePaths = $this->buildListOfTemplateCandidates($templateName, $this->templateRootPaths, $format);
		foreach ($possibleTemplatePaths as $possibleTemplatePath) {
			if ($this->testFileExistence($possibleTemplatePath)) {
				$templatePathAndFilename = $possibleTemplatePath;
				break;
			}
		}
		if ($templatePathAndFilename !== null) {
			$this->setTemplatePathAndFilename($templatePathAndFilename);
		} else {
			throw new InvalidTemplateResourceException('Could not load template file. Tried following paths: "' . implode('", "', $possibleTemplatePaths) . '".', 1430635896);
		}
	}

	/**
	 * Builds a list of possible candidates for a given template name
	 *
	 * @param string $templateName Name of the template to search for
	 * @param array $paths Paths to search in
	 * @param string $format The file format to use. e.g 'html' or 'txt'
	 * @return array Array of paths to search for the template file
	 */
	protected function buildListOfTemplateCandidates($templateName, array $paths, $format) {
		$upperCasedTemplateName = $this->ucFileNameInPath($templateName);
		$possibleTemplatePaths = array();
		$paths = ArrayUtility::sortArrayWithIntegerKeys($paths);
		$paths = array_reverse($paths, true);
		foreach ($paths as $layoutRootPath) {
			$possibleTemplatePaths[] = $this->resolveFileNamePath($layoutRootPath . '/' . $upperCasedTemplateName . '.' . $format);
			$possibleTemplatePaths[] = $this->resolveFileNamePath($layoutRootPath . '/' . $upperCasedTemplateName);
			if ($upperCasedTemplateName !== $templateName) {
				$possibleTemplatePaths[] = $this->resolveFileNamePath($layoutRootPath . '/' . $templateName . '.' . $format);
				$possibleTemplatePaths[] = $this->resolveFileNamePath($layoutRootPath . '/' . $templateName);
			}
		}
		return $possibleTemplatePaths;
	}

	/**
	 * Wrapper method to make the static call to GeneralUtility mockable in tests
	 *
	 * @param string $pathAndFilename
	 * @return string absolute pathAndFilename
	 */
	protected function resolveFileNamePath($pathAndFilename) {
		return GeneralUtility::getFileAbsFileName(GeneralUtility::fixWindowsFilePath($pathAndFilename), false);
	}

	/*
	 * Ensures the given templatePath gets the file name in UpperCamelCase
	 *
	 * @param string $templatePath A file name or a relative path
	 * @return string
	 */
	protected function ucFileNameInPath($templatePath) {
		if (strpos($templatePath, '/') > 0) {
			$pathParts = explode('/', $templatePath);
			$index = count($pathParts) - 1;
			$pathParts[$index] = ucfirst($pathParts[$index]);

			$upperCasedTemplateName = implode('/', $pathParts);
		} else {
			$upperCasedTemplateName = ucfirst($templatePath);
		}
		return $upperCasedTemplateName;
	}
}