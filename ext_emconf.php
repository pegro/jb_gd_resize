<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "jb_gd_resize".
 *
 * Auto generated 03-03-2016 21:08
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'JB GD Resize',
	'description' => 'Resize FE images with GD2. Useful on servers without ImageMagick/GraphicsMagick. Requires PHP with GD2 support enabled. Compatible with TYPO3 7.0+',
	'category' => 'fe',
	'version' => '2.1.0',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 0,
	'author' => 'Jan Bednarik',
	'author_email' => 'info@bednarik.org',
	'author_company' => '',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.0.0-7.6.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

