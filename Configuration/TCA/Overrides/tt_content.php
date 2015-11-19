<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $languageFilePrefix = 'LLL:EXT:content_rendering_core/Resources/Private/Language/Database.xlf:';
    $frontendLanguageFilePrefix = 'LLL:EXT:content_rendering_core/Resources/Private/Language/locallang_ttc.xlf:';

    // Add the CType "textmedia"
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

    $GLOBALS['TCA']['tt_content']['types']['table'] = [
        'showitem' => '
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

    // Add category tab when categories column exits
    if (!empty($GLOBALS['TCA']['tt_content']['columns']['categories'])) {
        $GLOBALS['TCA']['tt_content']['types']['textmedia']['showitem'] .=
        ',--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,
                categories';
    }

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
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'bullets_type', 'bullets', 'after:layout');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'uploadslayout', 'uploads_description,uploads_type');
});
