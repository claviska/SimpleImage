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

use JBZoo\Utils\Filter;
use JBZoo\Utils\Vars;
use JBZoo\Utils\Arr;

/**
 * Class Helper
 * @package JBZoo\Image
 */
class Helper
{
    /**
     * Require GD library
     * @throws Exception
     */
    public static function checkSystem()
    {
        // Require GD library
        if (!extension_loaded('gd')) {
            throw new Exception('Required extension GD is not loaded.'); // @codeCoverageIgnore
        }

        return true;
    }

    /**
     * @param string $format
     * @return bool
     */
    public static function isJpeg($format)
    {
        $format = strtolower($format);
        return 'image/jpg' === $format || 'jpg' === $format || 'image/jpeg' === $format || 'jpeg' === $format;
    }

    /**
     * @param string $format
     * @return bool
     */
    public static function isGif($format)
    {
        $format = strtolower($format);
        return 'image/gif' === $format || 'gif' === $format;
    }

    /**
     * @param string $format
     * @return bool
     */
    public static function isPng($format)
    {
        $format = strtolower($format);
        return 'image/png' === $format || 'png' === $format;
    }

    /**
     * Converts a hex color value to its RGB equivalent
     *
     * @param string|array $origColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return array|bool
     * @throws Exception
     */
    public static function normalizeColor($origColor)
    {
        $result = array();

        if (is_string($origColor)) {
            $result = self::normalizeColorString($origColor);

        } elseif (is_array($origColor) && (count($origColor) === 3 || count($origColor) === 4)) {
            $result = self::normalizeColorArray($origColor);
        }

        if (count($result) !== 4) {
            throw new Exception('Undefined color format (string): ' . $origColor); // @codeCoverageIgnore
        }

        return array('r' => $result[0], 'g' => $result[1], 'b' => $result[2], 'a' => $result[3]);
    }

    /**
     * Normalize color from string
     *
     * @param string $origColor
     * @return integer[]
     * @throws Exception
     */
    public static function normalizeColorString($origColor)
    {
        $color = trim($origColor, '#');
        $color = trim($color);

        if (strlen($color) === 6) {
            list($red, $green, $blue) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5],
            );

        } elseif (strlen($color) === 3) {
            list($red, $green, $blue) = array(
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2],
            );

        } else {
            throw new Exception('Undefined color format (string): ' . $origColor); // @codeCoverageIgnore
        }

        $red   = Filter::int(hexdec($red));
        $green = Filter::int(hexdec($green));
        $blue  = Filter::int(hexdec($blue));

        return array($red, $green, $blue, 0);
    }

    /**
     * Normalize color from array
     *
     * @param array $origColor
     * @return integer[]
     * @throws Exception
     */
    public static function normalizeColorArray(array $origColor)
    {
        $result = array();

        if (Arr::key('r', $origColor) && Arr::key('g', $origColor) && Arr::key('b', $origColor)) {
            $result = array(
                self::keepWithin($origColor['r'], 0, 255),
                self::keepWithin($origColor['g'], 0, 255),
                self::keepWithin($origColor['b'], 0, 255),
                self::keepWithin(isset($origColor['a']) ? $origColor['a'] : 0, 0, 127),
            );

        } elseif (Arr::key(0, $origColor) && Arr::key(1, $origColor) && Arr::key(2, $origColor)) {
            $result = array(
                self::keepWithin($origColor[0], 0, 255),
                self::keepWithin($origColor[1], 0, 255),
                self::keepWithin($origColor[2], 0, 255),
                self::keepWithin(isset($origColor[3]) ? $origColor[3] : 0, 0, 127),
            );
        }

        return $result;
    }

    /**
     * Ensures $value is always within $min and $max range.
     * If lower, $min is returned. If higher, $max is returned.
     *
     * @param mixed $value
     * @param int   $min
     * @param int   $max
     *
     * @return int
     */
    public static function keepWithin($value, $min, $max)
    {
        $value = Filter::int($value);
        $min   = Filter::int($min);
        $max   = Filter::int($max);

        return Vars::limit($value, $min, $max);
    }

    /**
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     * @link http://www.php.net/manual/en/function.imagecopymerge.php#88456
     *
     * @param mixed $dstIm     Image resource
     * @param mixed $srcIm     Source resource
     * @param int   $dstX      Left offset of dist
     * @param int   $dstY      Top offset
     * @param int   $srcX      Left offset of source
     * @param int   $srcY      Top offset of source
     * @param int   $srcWidth  Source width
     * @param int   $srcHeight Source height
     * @param int   $pct       Opacity
     */
    public static function imageCopyMergeAlpha($dstIm, $srcIm, $dstX, $dstY, $srcX, $srcY, $srcWidth, $srcHeight, $pct)
    {
        // Get image width and height and percentage
        $pct /= 100;
        $width  = imagesx($srcIm);
        $height = imagesy($srcIm);

        // Turn alpha blending off
        imagealphablending($srcIm, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $alpha = (imagecolorat($srcIm, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {

                // Get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($srcIm, $x, $y);
                $alpha   = ($colorxy >> 24) & 0xFF;

                // Calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }

                // Get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha(
                    $srcIm,
                    ($colorxy >> 16) & 0xFF,
                    ($colorxy >> 8) & 0xFF,
                    $colorxy & 0xFF,
                    $alpha
                );

                // Set pixel with the new color + opacity
                if (!imagesetpixel($srcIm, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }

        // Copy it
        imagesavealpha($dstIm, true);
        imagealphablending($dstIm, true);
        imagesavealpha($srcIm, true);
        imagealphablending($srcIm, true);
        imagecopy($dstIm, $srcIm, $dstX, $dstY, $srcX, $srcY, $srcWidth, $srcHeight);
    }

    /**
     * Check opacity value
     *
     * @param $opacity
     * @return int
     */
    public static function opacity($opacity)
    {
        $opacity = abs($opacity);

        if ($opacity < 1) {
            $opacity = $opacity * 100;
        }

        $opacity = Filter::int($opacity);
        $opacity = Vars::limit($opacity, 0, 100);

        return $opacity;
    }
}
