<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Cached error pages',
    'description' => 'Caches and serves your error pages from the disk and keep your php-fpm pool from dead locks',
    'category' => 'fe',
    'author' => 'Rémy DANIEL',
    'author_email' => 'contact@gaya.fr',
    'author_company' => 'GAYA Manufacture digitale',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
