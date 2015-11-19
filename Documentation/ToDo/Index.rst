.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt



.. _to-do:

=====
To Do
=====

Still some work needs to be done. Currently all content elements which are used by the extension
``css_styled_content`` in TYPO3 6.2LTS and have not been removed in TYPO3 7LTS are rendered by Fluid instead of
TypoScript. However extra functionality was added in version 7LTS. The following is still on the to-do list:

- Support media url's like YouTube and Vimeo as implemented in 7LTS
- Add the media renderers provided by TYPO3 7LTS

The cropping feature of images will not be added. This is only a very small part of ``fluid_styled_content``. A brand
new cropping tool was added to the File Abstraction Layer in version 7LTS, which is not part of this extension. Therefor
I will not add this feature, since the cropping mechanism is not available in FAL in version 6.2LTS.

