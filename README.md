TYPO3.ContentRenderingCore
==========================

This TYPO3 extension is a backport from TYPO3 version 7LTS Fluid Styled Content to TYPO3 version 6.2LTS

Installation
------------

* In the "Extension Manager" deinstall the extension "CSS Styled Content"
* Install the extension "Content Rendering Core"
* Go to Install tool and run the "Database Analyzer" under "Important actions"
* In the Install tool run the "Upgrade wizards" available
* In your Root TypoScript template, go to the tab "Includes"
* Add "Content Elements (content_rendering_core)" below "Include static (from extensions)"
* Preferably add "Content Elements CSS (content_rendering_core) as well to get the predefined CSS

To do
-----

* Support media url's like YouTube and Vimeo as implemented in 7LTS
* Add the media renderers provided by TYPO3 7LTS
