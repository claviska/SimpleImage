<?php

/**
 * JBZoo Toolbox - Image
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Image
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Image
 */

namespace JBZoo\Image;

use JBZoo\Utils\FS;
use JBZoo\Utils\Image as Helper;

/**
 * Class Text
 * @package JBZoo\Image
 */
class Text
{
    /**
     * @var array
     */
    protected static $default = [
        'position'       => 'bottom',
        'angle'          => 0,
        'font-size'      => 32,
        'color'          => '#ffffff',
        'offset-x'       => 0,
        'offset-y'       => 20,
        'stroke-color'   => '#222',
        'stroke-size'    => 2,
        'stroke-spacing' => 3,
    ];

    /**
     * Add text to an image
     *
     * @param mixed  $image  GD resource
     * @param string $text   Some text to output on image as watermark
     * @param string $fFile  TTF font file path
     * @param array  $params Additional render params
     *
     * @throws Exception
     * @throws \JBZoo\Utils\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function render($image, $text, $fFile, array $params = []): void
    {
        // Set vars
        $params = array_merge(self::$default, $params);
        $angle = Helper::rotate($params['angle']);
        $position = Helper::position($params['position']);
        $fSize = $params['font-size'];
        $color = $params['color'];
        $offsetX = $params['offset-x'];
        $offsetY = $params['offset-y'];
        $strokeColor = $params['stroke-color'];
        $strokeSize = $params['stroke-size'];
        $strokeSpacing = $params['stroke-spacing'];
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        $colorArr = self::getColor($image, $color);
        [$textWidth, $textHeight] = self::getTextBoxSize($fSize, $angle, $fFile, $text);
        [$textX, $textY] = Helper::getInnerCoords(
            $position,
            [$imageWidth, $imageHeight],
            [$textWidth, $textHeight],
            [$offsetX, $offsetY]
        );

        if ($strokeColor && $strokeSize) {
            if (is_array($color) || is_array($strokeColor)) {
                // Multi colored text and/or multi colored stroke
                $strokeColor = self::getColor($image, $strokeColor);
                $chars = str_split($text, 1);

                foreach ($chars as $key => $char) {
                    if ($key > 0) {
                        $textX = self::getStrokeX($fSize, $angle, $fFile, $chars, $key, $strokeSpacing, $textX);
                    }

                    // If the next letter is empty, we just move forward to the next letter
                    if ($char === ' ') {
                        continue;
                    }

                    self::renderStroke(
                        $image,
                        $char,
                        [$fFile, $fSize, current($colorArr), $angle],
                        [$textX, $textY],
                        [$strokeSize, current($strokeColor)]
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
                $rgba = Helper::normalizeColor($strokeColor);
                $strokeColor = imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
                self::renderStroke(
                    $image,
                    $text,
                    [$fFile, $fSize, current($colorArr), $angle],
                    [$textX, $textY],
                    [$strokeSize, $strokeColor]
                );
            }
        } elseif (is_array($color)) { // Multi colored text
            $chars = str_split($text, 1);
            foreach ($chars as $key => $char) {
                if ($key > 0) {
                    $textX = self::getStrokeX($fSize, $angle, $fFile, $chars, $key, $strokeSpacing, $textX);
                }

                // If the next letter is empty, we just move forward to the next letter
                if ($char === ' ') {
                    continue;
                }

                $fontInfo = [$fFile, $fSize, current($colorArr), $angle];
                self::internalRender($image, $char, $fontInfo, [$textX, $textY]);

                // #000 is 0, black will reset the array so we write it this way
                if (next($colorArr) === false) {
                    reset($colorArr);
                }
            }
        } else {
            self::internalRender($image, $text, [$fFile, $fSize, $colorArr[0], $angle], [$textX, $textY]);
        }
    }

    /**
     * Determine text color
     *
     * @param mixed        $image GD resource
     * @param string|array $colors
     * @return array
     * @throws \JBZoo\Utils\Exception
     */
    protected static function getColor($image, $colors)
    {
        $colors = (array)$colors;

        $result = [];
        foreach ($colors as $color) {
            $rgba = Helper::normalizeColor($color);
            $result[] = imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
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
    protected static function getTextBoxSize($fontSize, $angle, $fontFile, $text)
    {
        // Determine textbox size
        $fontPath = FS::clean($fontFile);

        if (!FS::isFile($fontPath)) {
            throw new Exception('Unable to load font: ' . $fontFile);
        }

        $box = imagettfbbox((float)$fontSize, $angle, $fontFile, $text);

        $boxWidth = abs($box[6] - $box[2]);
        $boxHeight = abs($box[7] - $box[1]);

        return [$boxWidth, $boxHeight];
    }

    /**
     * Compact args for imagettftext()
     *
     * @param mixed  $image  A GD image object
     * @param string $text   The text to output
     * @param array  $font   [$fontfile, $fontsize, $color, $angle]
     * @param array  $coords [X,Y] Coordinate of the starting position
     *
     * @return array
     */
    protected static function internalRender($image, $text, array $font, array $coords)
    {
        [$coordX, $coordY] = $coords;
        [$file, $size, $color, $angle] = $font;

        return imagettftext($image, $size, $angle, $coordX, $coordY, $color, $file, $text);
    }

    /**
     *  Same as imagettftext(), but allows for a stroke color and size
     *
     * @param mixed  $image  A GD image object
     * @param string $text   The text to output
     * @param array  $font   [$fontfile, $fontsize, $color, $angle]
     * @param array  $coords [X,Y] Coordinate of the starting position
     * @param array  $stroke [$strokeSize, $strokeColor]
     *
     * @return array
     */
    protected static function renderStroke($image, $text, array $font, array $coords, array $stroke)
    {
        [$coordX, $coordY] = $coords;
        [$file, $size, $color, $angle] = $font;
        [$strokeSize, $strokeColor] = $stroke;

        for ($x = ($coordX - abs($strokeSize)); $x <= ($coordX + abs($strokeSize)); $x++) {
            for ($y = ($coordY - abs($strokeSize)); $y <= ($coordY + abs($strokeSize)); $y++) {
                imagettftext($image, $size, $angle, (int)$x, (int)$y, $strokeColor, $file, $text);
            }
        }

        return imagettftext($image, $size, $angle, $coordX, $coordY, $color, $file, $text);
    }

    /**
     * Get X offset for stroke rendering mode
     *
     * @param string|float $fontSize
     * @param int          $angle
     * @param string       $fontFile
     * @param array        $letters
     * @param int          $charKey
     * @param int          $strokeSpacing
     * @param int          $textX
     * @return int
     */
    protected static function getStrokeX($fontSize, $angle, $fontFile, $letters, $charKey, $strokeSpacing, $textX): int
    {
        $charSize = imagettfbbox((float)$fontSize, $angle, $fontFile, $letters[$charKey - 1]);
        $textX += abs($charSize[4] - $charSize[0]) + $strokeSpacing;

        return (int)$textX;
    }
}
