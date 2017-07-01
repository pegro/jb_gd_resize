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

namespace Org\Bednarik\Xclass;

class GifBuilder extends \TYPO3\CMS\Frontend\Imaging\GifBuilder {

    function imageMagickConvert($imagefile, $newExt = '', $w = '', $h = '', $params = '', $frame = '', $options = '', $mustCreate = 0) {
        if ($this->NO_IMAGE_MAGICK) {
            if ($info = $this->getImageDimensions($imagefile)) {
                $newExt = strtolower(trim($newExt));
                // If no extension is given the original extension is used
                if (!$newExt) {
                    $newExt = $info[2];
                }
                if ($newExt == 'web') {
                    if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->webImageExt, $info[2])) {
                        $newExt = $info[2];
                    } else {
                        $newExt = $this->gif_or_jpg($info[2], $info[0], $info[1]);
                    }
                }
                if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->imageFileExt, $newExt)) {


                    $origW = $info[0];
                    $origH = $info[1];

                    $data = $this->getImageScale($info, $w, $h, $options);
                    $w = $data['origW'];
                    $h = $data['origH'];

                    $wh_noscale = (!$w && !$h) || ($data[0] == $info[0] && $data[1] == $info[1]) || $options['noScale'];
                    if ($wh_noscale && !$data['crs'] && !$params && $newExt == $info[2] && !$mustCreate) {
                        if (!empty($options['noScale'])) {
                            $info[0] = $data[0];
                            $info[1] = $data[1];
                        }
                        $info[3] = $imagefile;
                        return $info;
                    }

                    $info[0] = $data[0]; // w
                    $info[1] = $data[1]; // h

                    $ext = $info[2];
                    $path = $info[3];

                    $frame = $this->noFramePrepended ? '' : '[' . intval($frame) . ']';
                    $command = $this->scalecmd . ' ' . $info[0] . 'x' . $info[1] . '! ' . $params . ' ';
                    $cropscale = ($data['crs'] ? 'crs-V' . $data['cropV'] . 'H' . $data['cropH'] : '');

                    if ($this->alternativeOutputKey) {
                        $theOutputName = \TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($command . $cropscale . basename($imagefile) . $this->alternativeOutputKey . $frame);
                    } else {
                        $theOutputName = \TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($command . $cropscale . $imagefile . filemtime($imagefile) . $frame);
                    }
                    if ($this->imageMagickConvert_forceFileNameBody) {
                        $theOutputName = $this->imageMagickConvert_forceFileNameBody;
                        $this->imageMagickConvert_forceFileNameBody = '';
                    }

                    $this->createTempSubDir('pics/');
                    $output = $this->absPrefix . $this->tempPath . 'pics/' . $this->filenamePrefix . $theOutputName . '.' . $ext;

                    $GLOBALS['TEMP_IMAGES_ON_PAGE'][] = $output;

                    if ($this->dontCheckForExistingTempFile || !file_exists($output)) {
                        if (($ext == 'jpg') || ($ext == 'jpeg') || ($ext == 'png') || ($ext == 'gif')) {
                            $newIm = imagecreatetruecolor($info[0], $info[1]);
                            if ($ext == 'jpg' || $ext == 'jpeg') {
                                $im = imagecreatefromjpeg($path);
                            } elseif ($ext == 'gif') {
                                $im = imagecreatefromgif($path);
                                $transparentcolor = imagecolortransparent($im);
                                if ($transparentcolor >= 0) {
                                    imagefill($newIm, 0, 0, $transparentcolor);
                                    imagecolortransparent($newIm, $transparentcolor);
                                }
                            } else {
                                $im = imagecreatefrompng($path);

                                $trnprt_indx = imagecolortransparent($im);

                                if ($trnprt_indx >= 0) {
                                    $trnprt_color = imagecolorsforindex($im, $trnprt_indx);
                                    $trnprt_indx = imagecolorallocate($newIm, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                                    imagefill($newIm, 0, 0, $trnprt_indx);
                                    imagecolortransparent($newIm, $trnprt_indx);
                                } else {
                                    imagealphablending($newIm, false);
                                    $color = imagecolorallocatealpha($newIm, 0, 0, 0, 127);
                                    imagefill($newIm, 0, 0, $color);
                                    imagesavealpha($newIm, true);
                                }
                            }

                            imagecopyresampled($newIm, $im, 0, 0, 0, 0, $info[0], $info[1], $origW, $origH);

                            $this->createTempSubDir('pics/');

                            if ($ext == 'jpg' || $ext == 'jpeg') {
                                imagejpeg($newIm, $output);
                            } elseif ($ext == 'gif') {
                                if ($transparentcolor >= 0) {
                                    imagetruecolortopalette($newIm, false, 255);
                                    imagecolortransparent($newIm, $transparentcolor);
                                }
                                imagegif($newIm, $output);
                            } else {
                                imagepng($newIm, $output);
                            }
                        }
                    }

                    if (@file_exists($output)) {
                        $info[3] = $output;
                        $info[2] = $newExt;
                        if ($params) { // params could realisticly change some imagedata!
                            $info = $this->getImageDimensions($info[3]);
                        }
                        return $info;
                    }
                } else {
                    $x = Array($w, $h, pathinfo($imagefile, PATHINFO_EXTENSION), $imagefile);
                    return $x;
                }
            }
        }
        return parent::imageMagickConvert($imagefile, $newExt, $w, $h, $params, $frame, $options, $mustCreate);
    }
}