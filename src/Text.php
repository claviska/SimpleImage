<?php
/**
 * JBZoo Image
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   Image
 * @license   MIT
 * @copyright Copyright (C) JBZoo.com,  All rights reserved.
 * @link      https://github.com/JBZoo/Image
 */

namespace JBZoo\Image;

use JBZoo\Utils\FS;

/**
 * Class Text
 * @package JBZoo\Image
 */
class Text
{
    protected static $_default = array(
        'position'       => 'bottom',
        'angle'          => 0,
        'font-size'      => 32,
        'color'          => '#ffffff',
        'offset-x'       => 0,
        'offset-y'       => 20,
        'stroke-color'   => '#222',
        'stroke-size'    => 2,
        'stroke-spacing' => 3,
    );

    /**
     * Add text to an image
     *
     * @param mixed  $image    GD resource
     * @param string $text     Some text to output on image as watermark
     * @param string $fontFile TTF font file path
     * @param array  $params
     * @return mixed
     * @throws Exception
     */
    public static function text($image, $text, $fontFile, $params = array())
    {
        // Set vars
        $params        = array_merge(self::$_default, $params);
        $angle         = Helper::rotate($params['angle']);
        $position      = Helper::position($params['position']);
        $fontSize      = $params['font-size'];
        $color         = $params['color'];
        $offsetX       = $params['offset-x'];
        $offsetY       = $params['offset-y'];
        $strokeColor   = $params['stroke-color'];
        $strokeSize    = $params['stroke-size'];
        $strokeSpacing = $params['stroke-spacing'];
        $imageWidth    = imagesx($image);
        $imageHeight   = imagesy($image);

        $colorArr = self::_getColor($image, $color);
        list($textWidth, $textHeight) = self::_getTextboxSize($fontSize, $angle, $fontFile, $text);
        list($textX, $textY) = Helper::getPositionCoords(
            $position,
            array($imageWidth, $imageHeight),
            array($textWidth, $textHeight),
            array($offsetX, $offsetY)
        );

        if ($strokeColor && $strokeSize) {
            if (is_array($color) || is_array($strokeColor)) {

                // Multi colored text and/or multi colored stroke
                $strokeColor = self::_getColor($image, $strokeColor);
                $letters     = str_split($text, 1);

                foreach ($letters as $charKey => $char) {

                    if ($charKey > 0) {
                        $charSize = imagettfbbox($fontSize, $angle, $fontFile, $letters[$charKey - 1]);
                        $textX += abs($charSize[4] - $charSize[0]) + $strokeSpacing;
                    }

                    // If the next letter is empty, we just move forward to the next letter
                    if ($char === ' ') {
                        continue;
                    }

                    self::_imageTTFStrokeText(
                        $image,
                        $fontSize,
                        $angle,
                        array($textX, $textY),
                        current($colorArr),
                        current($strokeColor),
                        $strokeSize,
                        $fontFile,
                        $char
                    );

                    // #000 is 0, black will reset the array so we write it this way
                    if (next($colorArr) === false) {
                        reset($colorArr);
                    }

                    // #000 is 0, black will reset the array so we write it this way
                    if (next($strokeColor) === false) {
                        reset($strokeColor);
                    }
                }

            } else {
                $rgba        = Helper::normalizeColor($strokeColor);
                $strokeColor = imagecolorallocatealpha($image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
                self::_imageTTFStrokeText(
                    $image,
                    $fontSize,
                    $angle,
                    array($textX, $textY),
                    $colorArr[0],
                    $strokeColor,
                    $strokeSize,
                    $fontFile,
                    $text
                );
            }

        } else {
            if (is_array($color)) { // Multi colored text
                $letters = str_split($text, 1);
                foreach ($letters as $charKey => $char) {
                    if ($charKey > 0) {
                        $charSize = imagettfbbox($fontSize, $angle, $fontFile, $letters[$charKey - 1]);
                        $textX += abs($charSize[4] - $charSize[0]) + $strokeSpacing;
                    }

                    // If the next letter is empty, we just move forward to the next letter
                    if ($char === ' ') {
                        continue;
                    }

                    imagettftext($image, $fontSize, $angle, $textX, $textY, current($colorArr), $fontFile, $char);

                    // #000 is 0, black will reset the array so we write it this way
                    if (next($colorArr) === false) {
                        reset($colorArr);
                    }
                }

            } else {
                imagettftext($image, $fontSize, $angle, $textX, $textY, $colorArr[0], $fontFile, $text);
            }
        }
    }

    /**
     * Determine text color
     *
     * @param mixed        $image GD resource
     * @param string|array $colors
     * @return array
     * @throws Exception
     */
    protected static function _getColor($image, $colors)
    {
        $colors = (array)$colors;

        $result = array();
        foreach ($colors as $color) {
            $rgba     = Helper::normalizeColor($color);
            $result[] = imagecolorallocatealpha($image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        }

        return $result;
    }

    /**
     * Determine textbox size
     *
     * @param string $fontSize
     * @param int    $angle
     * @param string $fontFile
     * @param string $text
     * @return array
     *
     * @throws Exception
     */
    protected static function _getTextboxSize($fontSize, $angle, $fontFile, $text)
    {
        // Determine textbox size
        $fontPath = FS::clean($fontFile);

        if (!FS::isFile($fontPath)) {
            throw new Exception('Unable to load font: ' . $fontFile);
        }

        $box = imagettfbbox($fontSize, $angle, $fontFile, $text);

        $boxWidth  = abs($box[6] - $box[2]);
        $boxHeight = abs($box[7] - $box[1]);

        return array($boxWidth, $boxHeight);
    }

    /**
     *  Same as imagettftext(), but allows for a stroke color and size
     *
     * @param  mixed  $image       A GD image object
     * @param  float  $size        The font size
     * @param  float  $angle       The angle in degrees
     * @param  int    $coords      X,Y-coordinate of the starting position
     * @param  int    $textColor   The color index of the text
     * @param  int    $strokeColor The color index of the stroke
     * @param  int    $strokeSize  The stroke size in pixels
     * @param  string $fontfile    The path to the font to use
     * @param  string $text        The text to output
     *
     * @return array                This method has the same return values as imagettftext()
     */
    protected static function _imageTTFStrokeText(
        $image,
        $size,
        $angle,
        array $coords,
        $textColor,
        $strokeColor,
        $strokeSize,
        $fontfile,
        $text
    )
    {
        list($coordX, $coordY) = $coords;

        for ($x = ($coordX - abs($strokeSize)); $x <= ($coordX + abs($strokeSize)); $x++) {
            for ($y = ($coordY - abs($strokeSize)); $y <= ($coordY + abs($strokeSize)); $y++) {
                imagettftext($image, $size, $angle, $x, $y, $strokeColor, $fontfile, $text);
            }
        }

        return imagettftext($image, $size, $angle, $coordX, $coordY, $textColor, $fontfile, $text);
    }
}
