<?php
defined('TYPO3_MODE') or die();


$languageFilePrefix = 'LLL:EXT:content_rendering_core/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:content_rendering_core/Resources/Private/Language/locallang_ttc.xlf:';

// Add additional fields for bullets + upload CTypes
$additionalColumns = [
    'bullets_type' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.bullets_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$languageFilePrefix . 'tt_content.bullets_type.0', 0],
                [$languageFilePrefix . 'tt_content.bullets_type.1', 1],
                [$languageFilePrefix . 'tt_content.bullets_type.2', 2]
            ],
            'default' => 0
        ]
    ],
    'uploads_description' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.uploads_description',
        'config' => [
            'type' => 'check',
            'default' => 0,
            'items' => [
                ['LLL:EXT:lang/locallang_core.xml:labels.enabled', 1]
            ]
        ]
    ],
    'uploads_type' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.uploads_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$languageFilePrefix . 'tt_content.uploads_type.0', 0],
                [$languageFilePrefix . 'tt_content.uploads_type.1', 1],
                [$languageFilePrefix . 'tt_content.uploads_type.2', 2]
            ],
            'default' => 0
        ]
    ],
    'assets' => [
        'label' => $languageFilePrefix . 'tt_content.asset_references',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('assets', [
            'appearance' => [
                'createNewRelationLinkTitle' => $languageFilePrefix . 'tt_content.asset_references.addFileReference'
            ],
            // custom configuration for displaying fields in the overlay/reference table
            // behaves the same as the image field.
            'foreign_types' => $GLOBALS['TCA']['tt_content']['columns']['image']['config']['foreign_types']
        ], $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'])
    ],
    'table_caption' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.table_caption',
        'config' => [
            'type' => 'input'
        ]
    ],
    'table_delimiter' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.table_delimiter',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$languageFilePrefix . 'tt_content.table_delimiter.124', 124],
                [$languageFilePrefix . 'tt_content.table_delimiter.59', 59],
                [$languageFilePrefix . 'tt_content.table_delimiter.44', 44],
                [$languageFilePrefix . 'tt_content.table_delimiter.58', 58],
                [$languageFilePrefix . 'tt_content.table_delimiter.9', 9]
            ],
            'default' => 124
        ]
    ],
    'table_enclosure' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.table_enclosure',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$languageFilePrefix . 'tt_content.table_enclosure.0', 0],
                [$languageFilePrefix . 'tt_content.table_enclosure.39', 39],
                [$languageFilePrefix . 'tt_content.table_enclosure.34', 34]
            ],
            'default' => 0
        ]
    ],
    'table_header_position' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.table_header_position',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$languageFilePrefix . 'tt_content.table_header_position.0', 0],
                [$languageFilePrefix . 'tt_content.table_header_position.1', 1],
                [$languageFilePrefix . 'tt_content.table_header_position.2', 2]
            ],
            'default' => 0
        ]
    ],
    'table_tfoot' => [
        'exclude' => true,
        'label' => $languageFilePrefix . 'tt_content.table_tfoot',
        'config' => [
            'type' => 'check',
            'default' => 0,
            'items' => [
                ['LLL:EXT:lang/locallang_core.xml:labels.enabled', 1]
            ]
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);

// Add a new palette for default appearance options
$GLOBALS['TCA']['tt_content']['palettes']['appearanceLinks'] = array(
    'canNotCollapse' => 1,
    'showitem' => '
        sectionIndex;LLL:EXT:cms/locallang_ttc.xlf:sectionIndex_formlabel,
        linkToTop;LLL:EXT:cms/locallang_ttc.xlf:linkToTop_formlabel
    '
);

/***********************************
 * CE "Bullets" (tt_content.bullets)
 ***********************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['bullets']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,rowDescription,
        bodytext;' . $frontendLanguageFilePrefix . 'bodytext.ALT.bulletlist_formlabel,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'bullets_type', 'bullets', 'after:layout');

/*******************************
 * CE "Divider" (tt_content.div)
 *******************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['div']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        header;' . $frontendLanguageFilePrefix . 'header.ALT.div_formlabel,rowDescription,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/*********************************
 * CE "Header" (tt_content.header)
 *********************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['header']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.headers;headers,rowDescription,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';
/*****************************
 * CE "HTML" (tt_content.html)
 *****************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['html']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        header;' . $frontendLanguageFilePrefix . 'header.ALT.html_formlabel,rowDescription,
        bodytext;' . $frontendLanguageFilePrefix . 'bodytext.ALT.html_formlabel,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/**************************************
 * CE "Insert Plugin" (tt_content.list)
 **************************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['list']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,rowDescription,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.plugin,
        list_type;' . $frontendLanguageFilePrefix . 'list_type_formlabel,
        select_key;' . $frontendLanguageFilePrefix . 'select_key_formlabel,
        pages;' . $frontendLanguageFilePrefix . 'pages.ALT.list_formlabel,
        recursive,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/**************************************
 * CE "Special Menus" (tt_content.menu)
 **************************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['menu']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,rowDescription,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.menu;menu,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.accessibility,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.menu_accessibility;menu_accessibility,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/*******************************************
 * CE "Insert Records" (tt_content.shortcut)
 *******************************************/

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['shortcut']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        header;' . $frontendLanguageFilePrefix . 'header.ALT.shortcut_formlabel,rowDescription,
        records;' . $frontendLanguageFilePrefix . 'records_formlabel,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/*******************************
 * CE "Table" (tt_content.table)
 *******************************/

// Add a new palette
$GLOBALS['TCA']['tt_content']['palettes']['tableconfiguration'] = [
    'showitem' => '
        table_delimiter,
        table_enclosure
    '
];


$GLOBALS['TCA']['tt_content']['palettes']['tablelayout'] = [
    'showitem' => '
        cols,
        table_header_position,
        table_tfoot
    '
];

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['table']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,rowDescription,
        bodytext;;;nowrap:wizards[table];' . $frontendLanguageFilePrefix . 'field.table.bodytext,
        --palette--;;tableconfiguration,
        table_caption,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.table_layout;tablelayout,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

/******************************************
 * CE "Text & Media" (tt_content.textmedia)
 ******************************************/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        $languageFilePrefix . 'tt_content.CType.textmedia',
        'textmedia',
        'content-textpic'
    ],
    'header',
    'after'
);
$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['default'] = 'textmedia';

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['textmedia'] = 'mimetypes-x-content-text-media';

$GLOBALS['TCA']['tt_content']['palettes']['mediaAdjustments'] = [
    'showitem' => '
        imagewidth;' . $languageFilePrefix . 'tt_content.palette.textmedia.imagewidth,
        imageheight;' . $languageFilePrefix . 'tt_content.palette.textmedia.imageheight,
        imageborder;' . $languageFilePrefix . 'tt_content.palette.textmedia.imageborder
    '
];

$GLOBALS['TCA']['tt_content']['palettes']['gallerySettings'] = [
    'showitem' => '
        imageorient;' . $frontendLanguageFilePrefix . 'imageorient_formlabel,
        imagecols;' . $frontendLanguageFilePrefix . 'imagecols_formlabel
    '
];

$GLOBALS['TCA']['tt_content']['types']['textmedia'] = [
    'showitem' => '
            --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
            --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,
            bodytext;' . $frontendLanguageFilePrefix . 'bodytext_formlabel;;richtext:rte_transform[mode=ts_css],
        --div--;' . $frontendLanguageFilePrefix . 'tabs.media,
            assets,
            --palette--;' . $frontendLanguageFilePrefix . 'palette.imagelinks;imagelinks,
        --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
            layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
            --palette--;' . $languageFilePrefix . 'tt_content.palette.mediaAdjustments;mediaAdjustments,
            --palette--;' . $languageFilePrefix . 'tt_content.palette.gallerySettings;gallerySettings,
            --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
        --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
            hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
            --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
        --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
    ',
];

/**************************************
 * CE "File Links" (tt_content.uploads)
 **************************************/

// Add the fields "uploads_description" and "uploads_type" to TCA for palette "uploadslayout"
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'uploadslayout',
    'uploads_description, uploads_type'
);

// Restructure the form layout (tabs, palettes and fields)
$GLOBALS['TCA']['tt_content']['types']['uploads']['showitem'] = '
        --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.header;header,rowDescription,
        --palette--;' . $frontendLanguageFilePrefix . 'media;uploads,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
        layout;' . $frontendLanguageFilePrefix . 'layout_formlabel,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.uploads_layout;uploadslayout,
         --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
        hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
    --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
';

