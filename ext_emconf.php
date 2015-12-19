<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Backport of fluid_styled_content',
    'description' => 'Content rendering by Fluid templates',
    'category' => 'fe',
    'version' => '2.0.0',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearcacheonload' => true,
    'author' => 'Patrick Broens',
    'author_email' => 'patrick.broens@typo3.org',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.99.99',
            'typo3' => '6.2.0-6.2.99'
        ),
        'conflicts' => array(
            'css_styled_content' => '6.2.0-6.2.99'
        ),
        'suggests' => array()
    )
);
