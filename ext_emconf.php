<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Rendering Core',
    'description' => 'Content rendering by Fluid templates',
    'category' => 'fe',
    'version' => '1.0.0',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearcacheonload' => true,
    'author' => 'Patrick Broens',
    'author_email' => 'patrick.broens@typo3.org',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.99.99',
            'typo3' => '6.2.0-6.99.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
