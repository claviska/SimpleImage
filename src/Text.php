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
     * @param resource $image    GD resource
     * @param string   $text     Some text to output on image as watermark
     * @param string   $fontFile TTF font file path
     * @param array    $params   Additional render params
     *
     * @throws Exception
     * @throws \JBZoo\Utils\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function render($image, string $text, string $fontFile, array $params = []): void
    {
        // Set vars
        $params = array_merge(self::$default, $params);
        $angle = Helper::rotate((float)$params['angle']);
        $position = Helper::position((string)$params['position']);

        $fSize = (int)$params['font-size'];

        $offsetX = (int)$params['offset-x'];
        $offsetY = (int)$params['offset-y'];

        $strokeSize = (int)$params['stroke-size'];
        $strokeSpacing = (int)$params['stroke-spacing'];

        $imageWidth = (int)imagesx($image);
        $imageHeight = (int)imagesy($image);

        $color = is_string($params['color']) ? (string)$params['color'] : (array)$params['color'];
        $strokeColor = is_string($params['stroke-color'])
            ? (string)$params['stroke-color']
            : (array)$params['stroke-color'];

        $colorArr = self::getColor($image, $color);
        [$textWidth, $textHeight] = self::getTextBoxSize($fSize, $angle, $fontFile, $text);
        $textCoords = Helper::getInnerCoords(
            $position,
            [$imageWidth, $imageHeight],
            [$textWidth, $textHeight],
            [$offsetX, $offsetY]
        );

        $textX = (int)($textCoords[0] ?? null);
        $textY = (int)($textCoords[1] ?? null);

        if ($strokeColor && $strokeSize) {
            if (is_array($color) || is_array($strokeColor)) {
                // Multi colored text and/or multi colored stroke
                $strokeColor = self::getColor($image, $strokeColor);
                $chars = str_split($text, 1);

                foreach ($chars as $key => $char) {
                    if ($key > 0) {
                        $textX = self::getStrokeX($fSize, $angle, $fontFile, $chars, $key, $strokeSpacing, $textX);
                    }

                    // If the next letter is empty, we just move forward to the next letter
                    if ($char === ' ') {
                        continue;
                    }

                    self::renderStroke(
                        $image,
                        $char,
                        [$fontFile, $fSize, current($colorArr), $angle],
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
                    [$fontFile, $fSize, current($colorArr), $angle],
                    [$textX, $textY],
                    [$strokeSize, $strokeColor]
                );
            }
        } elseif (is_array($color)) { // Multi colored text
            $chars = str_split($text, 1);
            foreach ($chars as $key => $char) {
                if ($key > 0) {
                    $textX = self::getStrokeX($fSize, $angle, $fontFile, $chars, $key, $strokeSpacing, $textX);
                }

                // If the next letter is empty, we just move forward to the next letter
                if ($char === ' ') {
                    continue;
                }

                $fontInfo = [$fontFile, $fSize, current($colorArr), $angle];
                self::internalRender($image, $char, $fontInfo, [$textX, $textY]);

                // #000 is 0, black will reset the array so we write it this way
                if (next($colorArr) === false) {
                    reset($colorArr);
                }
            }
        } else {
            self::internalRender($image, $text, [$fontFile, $fSize, $colorArr[0], $angle], [$textX, $textY]);
        }
    }

    /**
     * Determine text color
     *
     * @param resource     $image GD resource
     * @param string|array $colors
     * @return array
     * @throws \JBZoo\Utils\Exception
     */
    protected static function getColor($image, $colors): array
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
     * @param int    $fontSize
     * @param int    $angle
     * @param string $fontFile
     * @param string $text
     * @return array
     *
     * @throws Exception
     */
    protected static function getTextBoxSize(int $fontSize, int $angle, string $fontFile, string $text): array
    {
        // Determine textbox size
        $fontPath = FS::clean($fontFile);

        if (!FS::isFile($fontPath)) {
            throw new Exception("Unable to load font: {$fontFile}");
        }

        $box = imagettfbbox($fontSize, $angle, $fontFile, $text);

        $boxWidth = (int)abs($box[6] - $box[2]);
        $boxHeight = (int)abs($box[7] - $box[1]);

        return [$boxWidth, $boxHeight];
    }

    /**
     * Compact args for imagettftext()
     *
     * @param resource $image  A GD image object
     * @param string   $text   The text to output
     * @param array    $font   [$fontfile, $fontsize, $color, $angle]
     * @param array    $coords [X,Y] Coordinate of the starting position
     */
    protected static function internalRender($image, string $text, array $font, array $coords): void
    {
        [$coordX, $coordY] = $coords;
        [$file, $size, $color, $angle] = $font;

        imagettftext($image, $size, $angle, $coordX, $coordY, $color, $file, $text);
    }

    /**
     *  Same as imagettftext(), but allows for a stroke color and size
     *
     * @param resource $image  A GD image object
     * @param string   $text   The text to output
     * @param array    $font   [$fontfile, $fontsize, $color, $angle]
     * @param array    $coords [X,Y] Coordinate of the starting position
     * @param array    $stroke [$strokeSize, $strokeColor]
     */
    protected static function renderStroke($image, string $text, array $font, array $coords, array $stroke): void
    {
        [$coordX, $coordY] = $coords;
        [$file, $size, $color, $angle] = $font;
        [$strokeSize, $strokeColor] = $stroke;

        for ($x = ($coordX - abs($strokeSize)); $x <= ($coordX + abs($strokeSize)); $x++) {
            for ($y = ($coordY - abs($strokeSize)); $y <= ($coordY + abs($strokeSize)); $y++) {
                imagettftext($image, $size, $angle, (int)$x, (int)$y, $strokeColor, $file, $text);
            }
        }

        imagettftext($image, $size, $angle, $coordX, $coordY, $color, $file, $text);
    }

    /**
     * Get X offset for stroke rendering mode
     *
     * @param float  $fontSize
     * @param int    $angle
     * @param string $fontFile
     * @param array  $letters
     * @param int    $charKey
     * @param int    $strokeSpacing
     * @param int    $textX
     * @return int
     * @noinspection PhpTooManyParametersInspection
     */
    protected static function getStrokeX(
        float $fontSize,
        int $angle,
        string $fontFile,
        array $letters,
        int $charKey,
        int $strokeSpacing,
        int $textX
    ): int {
        $charSize = imagettfbbox($fontSize, $angle, $fontFile, $letters[$charKey - 1]);
        $textX += abs($charSize[4] - $charSize[0]) + $strokeSpacing;

        return (int)$textX;
    }
}
