<?php
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cached_error_pages'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cached_error_pages'] = [
        'groups' => ['pages']
    ];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cached_error_pages']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cached_error_pages']['backend'] =
        \TYPO3\CMS\Core\Cache\Backend\FileBackend::class;
}
