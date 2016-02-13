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
    const TOP_LEFT     = 'tl';
    const LEFT         = 'l';
    const BOTTOM_LEFT  = 'bl';
    const TOP          = 't';
    const CENTER       = 'c';
    const BOTTOM       = 'b';
    const TOP_RIGHT    = 'tr';
    const RIGHT        = 'r';
    const BOTTOM_RIGHT = 'bt';

    /**
     * Require GD library
     * @throws Exception
     */
    public static function checkGD()
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
     * @return integer[]
     * @throws Exception
     */
    public static function normalizeColor($origColor)
    {
        $result = array();

        if (is_string($origColor)) {
            $result = self::_normalizeColorString($origColor);

        } elseif (is_array($origColor) && (count($origColor) === 3 || count($origColor) === 4)) {
            $result = self::_normalizeColorArray($origColor);
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
    protected static function _normalizeColorString($origColor)
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

        $red   = hexdec($red);
        $green = hexdec($green);
        $blue  = hexdec($blue);

        return array($red, $green, $blue, 0);
    }

    /**
     * Normalize color from array
     *
     * @param array $origColor
     * @return integer[]
     * @throws Exception
     */
    protected static function _normalizeColorArray(array $origColor)
    {
        $result = array();

        if (Arr::key('r', $origColor) && Arr::key('g', $origColor) && Arr::key('b', $origColor)) {
            $result = array(
                self::color($origColor['r']),
                self::color($origColor['g']),
                self::color($origColor['b']),
                self::alpha(Arr::key('a', $origColor) ? $origColor['a'] : 0),
            );

        } elseif (Arr::key(0, $origColor) && Arr::key(1, $origColor) && Arr::key(2, $origColor)) {
            $result = array(
                self::color($origColor[0]),
                self::color($origColor[1]),
                self::color($origColor[2]),
                self::alpha(Arr::key(3, $origColor) ? $origColor[3] : 0),
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
    public static function range($value, $min, $max)
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
     * @param mixed $dstImg   Dist image resource
     * @param mixed $srcImg   Source image resource
     * @param array $dist     Left and Top offset of dist
     * @param array $src      Left and Top offset of source
     * @param array $srcSizes Width and Height  of source
     * @param int   $opacity
     */
    public static function imageCopyMergeAlpha($dstImg, $srcImg, array $dist, array $src, array $srcSizes, $opacity)
    {
        list($dstX, $dstY) = $dist;
        list($srcX, $srcY) = $src;
        list($srcWidth, $srcHeight) = $srcSizes;

        // Get image width and height and percentage
        $opacity /= 100;
        $width  = imagesx($srcImg);
        $height = imagesy($srcImg);

        // Turn alpha blending off
        Helper::addAlpha($srcImg, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minAlpha = 127;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $alpha = (imagecolorat($srcImg, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minAlpha) {
                    $minAlpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {

                // Get current alpha value (represents the TANSPARENCY!)
                $colorXY = imagecolorat($srcImg, $x, $y);
                $alpha   = ($colorXY >> 24) & 0xFF;

                // Calculate new alpha
                if ($minAlpha !== 127) {
                    $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minAlpha);
                } else {
                    $alpha += 127 * $opacity;
                }

                // Get the color index with new alpha
                $alphaColorXY = imagecolorallocatealpha(
                    $srcImg,
                    ($colorXY >> 16) & 0xFF,
                    ($colorXY >> 8) & 0xFF,
                    $colorXY & 0xFF,
                    $alpha
                );

                // Set pixel with the new color + opacity
                if (!imagesetpixel($srcImg, $x, $y, $alphaColorXY)) {
                    return;
                }
            }
        }

        // Copy it
        self::addAlpha($srcImg);
        self::addAlpha($dstImg);
        imagecopy($dstImg, $srcImg, $dstX, $dstY, $srcX, $srcY, $srcWidth, $srcHeight);
    }

    /**
     * Check opacity value
     *
     * @param $opacity
     * @return int
     */
    public static function opacity($opacity)
    {
        if ($opacity <= 1) {
            $opacity *= 100;
        }

        $opacity = Filter::int($opacity);
        $opacity = Vars::limit($opacity, 0, 100);

        return $opacity;
    }

    /**
     * Convert opacity value to alpha
     * @param int $opacity
     * @return int
     */
    public static function opacity2Alpha($opacity)
    {
        $opacity = self::opacity($opacity);
        $opacity /= 100;

        $aplha = 127 - (127 * $opacity);
        $aplha = self::alpha($aplha);

        return $aplha;
    }

    /**
     * @param int $color
     * @return int
     */
    public static function color($color)
    {
        return self::range($color, 0, 255);
    }

    /**
     * @param int $color
     * @return int
     */
    public static function alpha($color)
    {
        return self::range($color, 0, 127);
    }

    /**
     * @param int $color
     * @return int
     */
    public static function rotate($color)
    {
        return self::range($color, -360, 360);
    }

    /**
     * @param int $brightness
     * @return int
     */
    public static function brightness($brightness)
    {
        return self::range($brightness, -255, 255);
    }

    /**
     * @param int $contrast
     * @return int
     */
    public static function contrast($contrast)
    {
        return self::range($contrast, -100, 100);
    }

    /**
     * @param int $colorize
     * @return int
     */
    public static function colorize($colorize)
    {
        return self::range($colorize, -255, 255);
    }

    /**
     * @param int $smooth
     * @return int
     */
    public static function smooth($smooth)
    {
        return self::range($smooth, 1, 10);
    }

    /**
     * @param string $direction
     * @return string
     */
    public static function direction($direction)
    {
        $direction = trim(strtolower($direction));

        if (in_array($direction, array('x', 'y', 'xy', 'yx'), true)) {
            return $direction;
        }

        return 'x';
    }

    /**
     * @param string $blur
     * @return int
     */
    public static function blur($blur)
    {
        return self::range($blur, 1, 10);
    }

    /**
     * @param string $percent
     * @return int
     */
    public static function percent($percent)
    {
        return self::range($percent, 0, 100);
    }

    /**
     * @param string $percent
     * @return int
     */
    public static function quality($percent)
    {
        return self::range($percent, 0, 100);
    }

    /**
     * Convert string to binary data
     *
     * @param $imageString
     * @return string
     */
    public static function strToBin($imageString)
    {
        $cleanedString = str_replace(' ', '+', preg_replace('#^data:image/[^;]+;base64,#', '', $imageString));
        $result        = base64_decode($cleanedString, true);

        if (!$result) {
            $result = $imageString;
        }

        return $result;
    }

    /**
     * Check is format supported by lib
     *
     * @param string $format
     * @return bool
     */
    public static function isSupportedFormat($format)
    {
        if ($format) {
            return self::isJpeg($format) || self::isPng($format) || self::isGif($format);
        }

        return false;
    }

    /**
     * Check is var image GD resource
     *
     * @param mixed $image
     * @return bool
     */
    public static function isGdRes($image)
    {
        return is_resource($image) && strtolower(get_resource_type($image)) === 'gd';
    }

    /**
     * Check position name
     *
     * @param string $position
     * @return string
     */
    public static function position($position)
    {
        $position = trim(strtolower($position));
        $position = str_replace(array('-', '_'), ' ', $position);

        if (in_array($position, array(self::TOP, 'top', 't'), true)) {
            return self::TOP;

        } elseif (in_array($position, array(self::TOP_RIGHT, 'top right', 'right top', 'tr', 'rt'), true)) {
            return self::TOP_RIGHT;

        } elseif (in_array($position, array(self::RIGHT, 'right', 'r'), true)) {
            return self::RIGHT;

        } elseif (in_array($position, array(self::BOTTOM_RIGHT, 'bottom right', 'right bottom', 'br', 'rb'), true)) {
            return self::BOTTOM_RIGHT;

        } elseif (in_array($position, array(self::BOTTOM, 'bottom', 'b'), true)) {
            return self::BOTTOM;

        } elseif (in_array($position, array(self::BOTTOM_LEFT, 'bottom left', 'left bottom', 'bl', 'lb'), true)) {
            return self::BOTTOM_LEFT;

        } elseif (in_array($position, array(self::LEFT, 'left', 'l'), true)) {
            return self::LEFT;

        } elseif (in_array($position, array(self::TOP_LEFT, 'top left', 'left top', 'tl', 'lt'), true)) {
            return self::TOP_LEFT;
        }

        return self::CENTER;
    }

    /**
     * Determine position
     *
     * @param string $position Position name or code
     * @param array  $canvas   Width and Height of canvas
     * @param array  $box      Width and Height of box that will be located on canvas
     * @param array  $offset   Forced offset X, Y
     * @return array
     */
    public static function getPositionCoords($position, array $canvas, array $box, array $offset)
    {
        $positionCode = self::position($position);
        list($canvasW, $canvasH) = $canvas;
        list($boxW, $boxH) = $box;
        list($offsetX, $offsetY) = $offset;

        // Coords map:
        // 00  10  20  =>  tl  t   tr
        // 01  11  21  =>  l   c   r
        // 02  12  22  =>  bl  b   br

        // X coord
        $x0 = $offsetX + 0;                             //  bottom-left     left        top-left
        $x1 = $offsetX + ($canvasW / 2) - ($boxW / 2);  //  bottom          center      top
        $x2 = $offsetX + $canvasW - $boxW;              //  bottom-right    right       top-right

        // Y coord
        $y0 = $offsetY + 0;                             //  top-left        top         top-right
        $y1 = $offsetY + ($canvasH / 2) - ($boxH / 2);  //  left            center      right
        $y2 = $offsetY + $canvasH - $boxH;              //  bottom-left     bottom      bottom-right

        if ($positionCode === self::TOP_LEFT) {
            return array($x0, $y0);

        } elseif ($positionCode === self::LEFT) {
            return array($x0, $y1);

        } elseif ($positionCode === self::BOTTOM_LEFT) {
            return array($x0, $y2);

        } elseif ($positionCode === self::TOP) {
            return array($x1, $y0);

        } elseif ($positionCode === self::BOTTOM) {
            return array($x1, $y2);

        } elseif ($positionCode === self::TOP_RIGHT) {
            return array($x2, $y0);

        } elseif ($positionCode === self::RIGHT) {
            return array($x2, $y1);

        } elseif ($positionCode === self::BOTTOM_RIGHT) {
            return array($x2, $y2);

        } else {
            return array($x1, $y1);
        }
    }

    /**
     * Add alpha chanel to image resource
     *
     * @param mixed $image   Image GD resource
     * @param bool  $isBlend Add alpha blending
     */
    public static function addAlpha($image, $isBlend = true)
    {
        imagesavealpha($image, true);
        imagealphablending($image, $isBlend);
    }
}
