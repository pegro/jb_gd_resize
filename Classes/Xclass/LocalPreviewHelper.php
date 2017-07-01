<?php
/**
 *  Copyright notice
 *
 *  (c) 2012 Jan Bednarik (info@bednarik.org)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * LocalPreviewHelper.php
 */

namespace Org\Bednarik\Xclass;

use \TYPO3\CMS\Core\Resource, \TYPO3\CMS\Core\Utility;

class LocalPreviewHelper extends \TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper {

    public function process(\TYPO3\CMS\Core\Resource\Processing\TaskInterface $task) {
        $targetFile = $task->getTargetFile();

        // Merge custom configuration with default configuration
        $configuration = array_merge(array('width' => 64, 'height' => 64), $task->getConfiguration());
        $configuration['width'] = Utility\MathUtility::forceIntegerInRange($configuration['width'], 1, 1000);
        $configuration['height'] = Utility\MathUtility::forceIntegerInRange($configuration['height'], 1, 1000);

        $originalFileName = $targetFile->getOriginalFile()->getForLocalProcessing(FALSE);

        // Create a temporary file in typo3temp/
        if ($targetFile->getOriginalFile()->getExtension() === 'jpg') {
            $targetFileExtension = '.jpg';
        } else {
            $targetFileExtension = '.png';
        }

        // Create the thumb filename in typo3temp/preview_....jpg
        $temporaryFileName = Utility\GeneralUtility::tempnam('preview_') . $targetFileExtension;
        // Check file extension
        if ($targetFile->getOriginalFile()->getType() != Resource\File::FILETYPE_IMAGE &&
            !Utility\GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $targetFile->getOriginalFile()->getExtension())
        ) {
            // Create a default image
            $this->processor->getTemporaryImageWithText($temporaryFileName, 'Not imagefile!', 'No ext!', $targetFile->getOriginalFile()->getName());
        } else {
            // Create the temporary file
            if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['im']) {
                $newIm = imagecreatetruecolor($configuration['width'], $configuration['height']);
                if ($targetFile->getOriginalFile()->getExtension() == 'jpg') {
                    $im = imagecreatefromjpeg($originalFileName);
                } elseif ($targetFile->getOriginalFile()->getExtension() == 'gif') {
                    $im = imagecreatefromgif($this->processor->wrapFileName($originalFileName));

                    $transparentcolor = imagecolortransparent($im);
                    if ($transparentcolor >= 0) {
                        imagefill($newIm, 0, 0, $transparentcolor);
                        imagecolortransparent($newIm, $transparentcolor);
                    }
                } else {
                    $im = imagecreatefrompng($this->processor->wrapFileName($originalFileName));
                }

                imagecopyresampled($newIm, $im, 0, 0, 0, 0, $configuration['width'], $configuration['height'], imagesx($im), imagesy($im));

                if ($targetFile->getOriginalFile()->getExtension() == 'jpg') {
                    imagejpeg($newIm, $temporaryFileName);
                } elseif ($targetFile->getOriginalFile()->getExtension() == 'gif') {
                    if ($transparentcolor >= 0) {
                        imagetruecolortopalette($newIm, false, 255);
                        imagecolortransparent($newIm, $transparentcolor);
                    }
                    imagegif($newIm, $temporaryFileName);
                } else {
                    imagepng($newIm, $temporaryFileName);
                }

                if (!file_exists($temporaryFileName)) {
                    // Create a error gif
                    $this->processor->getTemporaryImageWithText($temporaryFileName, 'No thumb', 'generated!', $targetFile->getOriginalFile()->getName());
                }
            }
        }

        return array(
            'filePath' => $temporaryFileName,
        );
    }

}

?>
