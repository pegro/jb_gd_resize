<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

//turn image processing on
$TYPO3_CONF_VARS['GFX']['image_processing'] = '1';
//disable imagemagick
$TYPO3_CONF_VARS['GFX']['im'] = '0';
//enable use of gd
$TYPO3_CONF_VARS['GFX']['gdlib'] = '1';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails'] = 1;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'] = Array(
    'className' => 'Org\\Bednarik\\Xclass\\GraphicalFunctions'
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder'] = Array(
    'className' => 'Org\\Bednarik\\Xclass\\GifBuilder'
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\View\\ThumbnailView'] = Array(
    'className' => 'Org\\Bednarik\\Xclass\\ThumbnailView'
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalPreviewHelper'] = Array(
    'className' => 'Org\\Bednarik\\Xclass\\LocalPreviewHelper'
);
?>
