.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt



.. _introduction:

============
Introduction
============


.. _what-does-it-do:

What does it do?
================

The extension "content_rendering_core" is a backport for TYPO3 version 6.2 of the extension "fluid_styled_content",
introduced in TYPO3 7LTS. It handles the basic frontend rendering of TYPO3 CMS.

The extension "fluid_styled_content" was introduced as a replacement for "css_styled_content". The extension
"css_styled_content" renders content elements using TypoScript. Since the introduction of the templating engine "Fluid"
in the TYPO3 core, there was a demand to have this rendering done by "Fluid" templates, which makes it easier for
integrators to adapt or add their own content element rendering.

This extension provides a basic set of content elements which you can use for your website. These can be used
out-of-the-box, but can be adapted to your, or your client, needs. You are not bound to using only these content
elements. For custom made functionality it is possible to add extra content elements to the basic set. How to adapt,
enhance or add content elements will be described in this document.

The rendering of the provided set of content elements is based on HTML5. Nowadays most of the websites are built with
this core technology markup language of the Internet used for structuring and presenting content for the World Wide Web.
If your website is using another markup, like HTML4 or XHTML, it is easy to exchange the provided templates with your
own.


.. _history:

A little bit of history
=======================

At the beginning of TYPO3 CMS content elements were rendered by the static template called “*content (default)*”. This
was mainly based on font-tags for styling and tables for positioning which was needed to achieve the visual
constructions in old versions of web browsers.

Some time later the extension "css_styled_content" was introduced, which focused on reducing the amount of TypoScript
and providing XHTML/HTML5 markup which could be styled by Cascading Style Sheets (CSS), a style sheet language used for
describing the look and formatting of a document written in a markup language. Still this extension was heavily based on
TypoScript and did allow custom modifications up to some point.

Since the introduction of the templating engine Fluid, more websites are using this for page templating. Newer TYPO3 CMS
packages (extensions) are also using Fluid as their base templating engine. The content elements which were provided
with TYPO3 CMS by default were still using TypoScript and partly PHP code.

Since TYPO3 CMS version 7LTS the default content elements have been moved to the extension "fluid_styled_content",
also using Fluid as their templating engine. The benefits are that hardly any knowledge of TypoScript is needed to make
changes. Integrators can easily exchange the base content element Fluid templates with their own. In Fluid more complex
functionality that exceed the simple output of values has to be implemented with ViewHelpers. Every ViewHelper has its
own PHP class. Several basic ViewHelpers are provided by Fluid. When using your own Fluid templates, developers can add
extra functionality with their own ViewHelpers, extending the possibilities of the content elements.

This extension backports this functionality to TYPO3 version 6.2.


.. _support:

Support
=======

Please see/report problems on GitHub `https://github.com/patrickbroens/TYPO3.ContentRenderingCore/issues
<https://github.com/patrickbroens/TYPO3.ContentRenderingCore/issues>`_.


.. _sponsoring:

Sponsoring
==========

This extension is made without any financial contribution. If you like it, and want to sponsor further development and
maintenance, or just because you are in a good mood, you can donate money at `http://paypal.me/PatrickBroens
<http://paypal.me/PatrickBroens>`_.

