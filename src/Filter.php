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

use JBZoo\Utils\Filter as VarFilter;

/**
 * Class Helper
 * @package JBZoo\Image
 */
class Filter
{
    const BLUR_SEL  = 0;
    const BLUR_GAUS = 1;

    /**
     * Add sepia effect
     * @param $image
     */
    public static function sepia($image)
    {
        self::grayscale($image);
        imagefilter($image, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Add sepia effect
     * @param $image
     */
    public static function grayscale($image)
    {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Pixelate
     * @param mixed $image
     * @param int   $blockSize Size in pixels of each resulting block
     */
    public static function pixelate($image, $blockSize = 10)
    {
        $blockSize = VarFilter::int($blockSize);
        imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
    }

    /**
     * Edge Detect
     * @param $image
     */
    public static function edges($image)
    {
        imagefilter($image, IMG_FILTER_EDGEDETECT);
    }

    /**
     * Emboss
     * @param $image
     */
    public static function emboss($image)
    {
        imagefilter($image, IMG_FILTER_EMBOSS);
    }

    /**
     * Negative
     * @param mixed $image
     */
    public static function invert($image)
    {
        imagefilter($image, IMG_FILTER_NEGATE);
    }

    /**
     * Blur
     * @param mixed $image
     * @param int   $type   BLUR_SEL|BLUR_GAUS
     * @param int   $passes Number of times to apply the filter
     */
    public static function blur($image, $passes = 1, $type = self::BLUR_SEL)
    {
        $passes = Helper::blur($passes);

        if (self::BLUR_GAUS === $type) {
            $filterType = IMG_FILTER_GAUSSIAN_BLUR;
        } else {
            $filterType = IMG_FILTER_SELECTIVE_BLUR;
        }

        for ($i = 0; $i < $passes; $i++) {
            imagefilter($image, $filterType);
        }
    }

    /**
     * Brightness
     * @param mixed $image
     * @param int   $level Darkest = -255, lightest = 255
     */
    public static function brightness($image, $level)
    {
        imagefilter($image, IMG_FILTER_BRIGHTNESS, Helper::brightness($level));
    }

    /**
     * Contrast
     * @param mixed $image
     * @param int   $level Min = -100, max = 100
     */
    public static function contrast($image, $level)
    {
        imagefilter($image, IMG_FILTER_CONTRAST, Helper::contrast($level));
    }

    /**
     * Colorize
     *
     * @param mixed     $image
     * @param string    $color      Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float|int $opacity    0-100
     * @return $this
     *
     * @throws Exception
     */
    public static function colorize($image, $color, $opacity)
    {
        $rgba  = Helper::normalizeColor($color);
        $alpha = Helper::opacity2Alpha($opacity);

        $red   = Helper::color($rgba['r']);
        $green = Helper::color($rgba['g']);
        $blue  = Helper::color($rgba['b']);

        imagefilter($image, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
    }

    /**
     * Mean Remove
     * @param mixed $image
     */
    public static function meanRemove($image)
    {
        imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Smooth
     * @param mixed $image
     * @param int   $level
     */
    public static function smooth($image, $level)
    {
        imagefilter($image, IMG_FILTER_SMOOTH, Helper::smooth($level));
    }

    /**
     * Desaturate
     *
     * @param mixed $image
     * @param int   $percent Level of desaturization.
     * @return mixed
     */
    public static function desaturate($image, $percent = 100)
    {
        // Determine percentage
        $percent = Helper::percent($percent);
        $width   = imagesx($image);
        $height  = imagesy($image);

        if ($percent === 100) {
            self::grayscale($image);

        } else {
            // Make a desaturated copy of the image
            $newImage = imagecreatetruecolor($width, $height);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
            imagefilter($newImage, IMG_FILTER_GRAYSCALE);

            // Merge with specified percentage
            Helper::imageCopyMergeAlpha($image, $newImage, 0, 0, 0, 0, $width, $height, $percent);
            return $newImage;
        }
    }

    /**
     * Changes the opacity level of the image
     *
     * @param mixed     $image
     * @param float|int $opacity 0-1 or 0-100
     * @return $this
     */
    public static function opacity($image, $opacity)
    {
        // Determine opacity
        $opacity = Helper::opacity($opacity);

        $width  = imagesx($image);
        $height = imagesy($image);

        $newImage = imagecreatetruecolor($width, $height);

        // Set a White & Transparent Background Color
        $bg = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $bg);

        // Copy and merge
        Helper::imageCopyMergeAlpha($newImage, $image, 0, 0, 0, 0, $width, $height, $opacity);
        imagedestroy($image);

        return $newImage;
    }

    /**
     * Rotate an image
     *
     * @param mixed        $image
     * @param int          $angle   -360 < x < 360
     * @param string|array $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     * @throws Exception
     */
    public static function rotate($image, $angle, $bgColor = '#000000')
    {
        // Perform the rotation
        $angle = Helper::rotate($angle);
        $rgba  = Helper::normalizeColor($bgColor);

        $bgColor  = imagecolorallocatealpha($image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $newImage = imagerotate($image, -($angle), $bgColor);

        imagesavealpha($newImage, true);
        imagealphablending($newImage, true);

        return $newImage;
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param mixed  $image
     * @param string $dir x|y|yx|xy
     * @return $this
     */
    public static function flip($image, $dir)
    {
        $dir    = Helper::direction($dir);
        $width  = imagesx($image);
        $height = imagesy($image);

        $newImage = imagecreatetruecolor($width, $height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        if ($dir === 'y') {
            for ($y = 0; $y < $height; $y++) {
                imagecopy($newImage, $image, 0, $y, 0, $height - $y - 1, $width, 1);
            }

        } elseif ($dir === 'x') {
            for ($x = 0; $x < $width; $x++) {
                imagecopy($newImage, $image, $x, 0, $width - $x - 1, 0, 1, $height);
            }

        } elseif ($dir === 'xy' || $dir === 'yx') {
            $newImage = self::flip($image, 'x');
            $newImage = self::flip($newImage, 'y');
        }

        return $newImage;
    }

    /**
     * Fill image with color
     *
     * @param mixed  $image
     * @param string $color     Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                          Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return $this
     * @throws Exception
     */
    public static function fill($image, $color = '#000000')
    {
        $width  = imagesx($image);
        $height = imagesy($image);

        $rgba      = Helper::normalizeColor($color);
        $fillColor = imagecolorallocatealpha(
            $image,
            (int)$rgba['r'],
            (int)$rgba['g'],
            (int)$rgba['b'],
            (int)$rgba['a']
        );

        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefilledrectangle($image, 0, 0, $width, $height, $fillColor);
    }
}
