# Cached Error Pages

This extension caches and serves your error pages from the disk and keep your php-fpm pool from dead locks.

## Installation

Install and activate the extension in the extension manager.

### With composer

Add this section to the `repositories`:
```json
{
  "type": "git",
  "url": "ssh://git@git.gaya.fr/gaya/gaya-typo3-cached-error-pages.git"
}
```

Then require the package:

```
composer require gaya/cached-error-pages
```

## Configuration

You need to register the extension's PageErrorHandler in order to use it. 

In the Sites module, go the Error Handling tab and create a new Error handling configuration.

Enter the desired status code, choose `PHP Class` in _How to handle errors_, and enter `GAYA\CachedErrorPages\ErrorHandler\CachedPageContentErrorHandler` in the FQCN field.

In the _Show Content from Page_ field, choose the page from your page tree which will be used to display the error content.

> The CachedPageContentErrorHandler has the same configuration interface than the core's PageContentErrorHandler.

Your Site's yaml configuration file should end up with something like that:
```
errorHandling:
  -
    errorCode: '404'
    errorHandler: PHP
    errorContentSource: 't3://page?uid=6'
    errorPhpClassFQCN: GAYA\CachedErrorPages\ErrorHandler\CachedPageContentErrorHandler
```

## About the cached page 

The cache is written on the first hit to the error page.

The cache is cleared automatically when:
- The error page or its content is changed
- The "Clear frontend caches" is clicked in the backend 
- The "Clear all caches" is clicked in the backend or in the maintenance module 

## Author

This extension is maintained by [GAYA Manufacture digitale](https://www.gaya.fr), a TYPO3 web agency in Paris, France.