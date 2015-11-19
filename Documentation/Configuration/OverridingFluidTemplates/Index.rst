.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt



.. _overriding-fluid-templates:

==============================
Overriding the FLUID templates
==============================

At :ref:`typoscript` we have described the way content elements are rendered.

By default these settings are done in the file ``setup.txt`` which can be found in the folder
``EXT:content_rendering_core/Configuration/TypoScript/Static/``.


.. _overriding-fluid-templates-using-lib-fluidcontent:

Using lib.fluidContent
======================

This option gives you the possibility to add another ``templateRootPath`` and can be defined the same as
``partialRootPaths`` and ``layoutRootPaths``.

.. code-block:: typoscript

   lib.fluidContent {
      templateRootPaths {
         200 = EXT:your_extension_key/Resources/Private/Templates/
      }
      partialRootPaths {
         200 = EXT:your_extension_key/Resources/Private/Partials/
      }
      layoutRootPaths {
         200 = EXT:your_extension_key/Resources/Private/Layouts/
      }
   }

A content element is using a ``templateName``, which is defined in ``setup.txt``. You can override this value, but the
template from the extension ``content_rendering_core`` will not be loaded then, since its name is still the default
value.

.. code-block:: typoscript

   tt_content {
      bullets {
         templateName = ChangedName.html
      }
   }

