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
 * ThumbnailView.php
 */

namespace Org\Bednarik\Xclass;

class ThumbnailView extends \TYPO3\CMS\Backend\View\ThumbnailView {

    function main() {
        if (is_object($this->image)) {
            // Check file extension:
            if ($this->image->getExtension() == 'ttf') {
                // Make font preview... (will not return)
                $this->fontGif($this->image);
            } elseif ($this->image->getType() != \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE && !\TYPO3\CMS\Core\Utility\GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $this->image->getExtension())) {
                $this->errorGif('Not imagefile!', 'No ext!', $this->image->getName());
            }
            // ... so we passed the extension test meaning that we are going to make a thumbnail here:
            // default
            if (!$this->size) {
                $this->size = $this->sizeDefault;
            }

            // I added extra check, so that the size input option could not be fooled to pass other values.
            // That means the value is exploded, evaluated to an integer and the imploded to [value]x[value].
            // Furthermore you can specify: size=340 and it'll be translated to 340x340.
            // explodes the input size (and if no "x" is found this will add size again so it is the same for both dimensions)
            $sizeParts = explode('x', $this->size . 'x' . $this->size);
            // Cleaning it up, only two parameters now.
            $sizeParts = array(\TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($sizeParts[0], 1, 1000), \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($sizeParts[1], 1, 1000));
            // Imploding the cleaned size-value back to the internal variable
            $this->size = implode('x', $sizeParts);
            $sizeMax = max($sizeParts); // Getting max value

            // Init
            $outpath = PATH_site . $this->outdir;

            $thmMode = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails_png'], 0);
            $outext = $this->image->getExtension() != 'jpg' || $thmMode & 2 ? ($thmMode & 1 ? 'png' : 'gif') : 'jpg';

            $outfile = 'tmb_' . substr(md5($this->input . $this->mtime . $this->size), 0, 10) . '.' . $outext;
            $this->output = $outpath . $outfile;

            // If thumbnail does not exist, we generate it
            if (!@file_exists($this->output)) {

                // $sizeParts - dimensions
                // $outext - file extension
                $newIm = imagecreatetruecolor($sizeParts[0], $sizeParts[1]);
                if ($outext == 'jpg') {
                    $im = imagecreatefromjpeg($this->input);
                } elseif ($outext == 'gif') {
                    $im = imagecreatefromgif($this->input);

                    $transparentcolor = imagecolortransparent($im);
                    if ($transparentcolor >= 0) {
                        imagefill($newIm, 0, 0, $transparentcolor);
                        imagecolortransparent($newIm, $transparentcolor);
                    }
                } else {
                    $im = imagecreatefrompng($this->input);
                }

                imagecopyresampled($newIm, $im, 0, 0, 0, 0, $sizeParts[0], $sizeParts[1], imagesx($im), imagesy($im));

                if ($outext == 'jpg') {
                    imagejpeg($newIm, $this->output);
                } elseif ($outext == 'gif') {
                    if ($transparentcolor >= 0) {
                        imagetruecolortopalette($newIm, false, 255);
                        imagecolortransparent($newIm, $transparentcolor);
                    }
                    imagegif($newIm, $this->output);
                } else {
                    imagepng($newIm, $this->output);
                }
            }

            // The thumbnail is read and output to the browser
            if ($fd = @fopen($this->output, 'rb')) {
                $fileModificationTime = filemtime($this->output);
                header('Content-type: image/' . $outext);
                header('Last-Modified: ' . date('r', $fileModificationTime));
                header('Etag: ' . md5($this->output) . '-' . $fileModificationTime);
                // Expiration time is choosen arbitrary to 1 month
                header('Expires: ' . date('r', ($fileModificationTime + 30 * 24 * 60 * 60)));
                fpassthru($fd);
                fclose($fd);
            } else {
                $this->errorGif('Read problem!', '', $this->output);
            }

        } else {
            $this->errorGif('No valid', 'inputfile!', basename($this->input));
        }
    }
}

?>
