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
}
