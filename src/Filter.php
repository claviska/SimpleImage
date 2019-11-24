<?php
/**
 * JBZoo Image
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Image
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Image
 */

namespace JBZoo\Image;

use JBZoo\Utils\Filter as VarFilter;
use JBZoo\Utils\Image as Helper;

/**
 * Class Helper
 * @package JBZoo\Image
 */
class Filter
{
    const BLUR_SEL  = 0;
    const BLUR_GAUS = 1;

    /**
     * Add sepia effect (emulation)
     *
     * @param resource $image Image GD resource
     */
    public static function sepia($image)
    {
        self::grayscale($image);
        imagefilter($image, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Add grayscale effect
     *
     * @param resource $image Image GD resource
     */
    public static function grayscale($image)
    {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Pixelate effect
     *
     * @param resource $image     Image GD resource
     * @param int      $blockSize Size in pixels of each resulting block
     */
    public static function pixelate($image, $blockSize = 10)
    {
        $blockSize = VarFilter::int($blockSize);
        imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
    }

    /**
     * Edge Detect
     *
     * @param resource $image Image GD resource
     */
    public static function edges($image)
    {
        imagefilter($image, IMG_FILTER_EDGEDETECT);
    }

    /**
     * Emboss
     *
     * @param resource $image Image GD resource
     */
    public static function emboss($image)
    {
        imagefilter($image, IMG_FILTER_EMBOSS);
    }

    /**
     * Negative
     *
     * @param resource $image Image GD resource
     */
    public static function invert($image)
    {
        imagefilter($image, IMG_FILTER_NEGATE);
    }

    /**
     * Blur effect
     *
     * @param resource $image  Image GD resource
     * @param int      $type   BLUR_SEL|BLUR_GAUS
     * @param int      $passes Number of times to apply the filter
     */
    public static function blur($image, $passes = 1, $type = self::BLUR_SEL)
    {
        $passes = Helper::blur($passes);

        $filterType = IMG_FILTER_SELECTIVE_BLUR;
        if (self::BLUR_GAUS === $type) {
            $filterType = IMG_FILTER_GAUSSIAN_BLUR;
        }

        for ($i = 0; $i < $passes; $i++) {
            imagefilter($image, $filterType);
        }
    }

    /**
     * Change brightness
     *
     * @param resource $image Image GD resource
     * @param int      $level Darkest = -255, lightest = 255
     */
    public static function brightness($image, $level)
    {
        imagefilter($image, IMG_FILTER_BRIGHTNESS, Helper::brightness($level));
    }

    /**
     * Change contrast
     *
     * @param resource $image Image GD resource
     * @param int      $level Min = -100, max = 100
     */
    public static function contrast($image, $level)
    {
        imagefilter($image, IMG_FILTER_CONTRAST, Helper::contrast($level));
    }

    /**
     * Set colorize
     *
     * @param resource  $image      Image GD resource
     * @param string    $color      Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float|int $opacity    0-100
     * @return $this
     *
     * @throws \JBZoo\Utils\Exception
     */
    public static function colorize($image, $color, $opacity)
    {
        $rgba = Helper::normalizeColor($color);
        $alpha = Helper::opacity2Alpha($opacity);

        $red = Helper::color($rgba[0]);
        $green = Helper::color($rgba[1]);
        $blue = Helper::color($rgba[2]);

        imagefilter($image, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
    }

    /**
     * Mean Remove
     *
     * @param resource $image Image GD resource
     */
    public static function meanRemove($image)
    {
        imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Smooth effect
     *
     * @param resource $image  Image GD resource
     * @param int      $passes Number of times to apply the filter (1 - 2048)
     */
    public static function smooth($image, $passes = 1)
    {
        imagefilter($image, IMG_FILTER_SMOOTH, Helper::smooth($passes));
    }

    /**
     * Desaturate
     *
     * @param resource $image   Image GD resource
     * @param int      $percent Level of desaturization.
     * @return resource|null
     */
    public static function desaturate($image, $percent = 100)
    {
        // Determine percentage
        $percent = Helper::percent($percent);
        $width = imagesx($image);
        $height = imagesy($image);

        if ($percent === 100) {
            self::grayscale($image);

        } else {
            // Make a desaturated copy of the image
            $newImage = imagecreatetruecolor($width, $height);
            imagealphablending($newImage, false);
            imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
            imagefilter($newImage, IMG_FILTER_GRAYSCALE);

            // Merge with specified percentage
            Helper::imageCopyMergeAlpha(
                $image,
                $newImage,
                [0, 0],
                [0, 0],
                [$width, $height],
                $percent
            );

            return $newImage;
        }

        return null;
    }

    /**
     * Changes the opacity level of the image
     *
     * @param resource  $image   Image GD resource
     * @param float|int $opacity 0-1 or 0-100
     *
     * @return mixed
     */
    public static function opacity($image, $opacity)
    {
        // Determine opacity
        $opacity = Helper::opacity($opacity);

        $width = imagesx($image);
        $height = imagesy($image);

        $newImage = imagecreatetruecolor($width, $height);

        // Set a White & Transparent Background Color
        $bg = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $bg);

        // Copy and merge
        Helper::imageCopyMergeAlpha(
            $newImage,
            $image,
            [0, 0],
            [0, 0],
            [$width, $height],
            $opacity
        );

        imagedestroy($image);

        return $newImage;
    }

    /**
     * Rotate an image
     *
     * @param resource     $image   Image GD resource
     * @param int          $angle   -360 < x < 360
     * @param string|array $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return resource
     * @throws \JBZoo\Utils\Exception
     */
    public static function rotate($image, $angle, $bgColor = '#000000')
    {
        // Perform the rotation
        $angle = Helper::rotate($angle);
        $rgba = Helper::normalizeColor($bgColor);

        $bgColor = imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
        $newImage = imagerotate($image, -($angle), $bgColor);

        Helper::addAlpha($newImage);

        return $newImage;
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param mixed  $image GD resource
     * @param string $dir   Direction of fliping - x|y|yx|xy
     * @return resource
     */
    public static function flip($image, $dir)
    {
        $dir = Helper::direction($dir);
        $width = imagesx($image);
        $height = imagesy($image);

        $newImage = imagecreatetruecolor($width, $height);
        Helper::addAlpha($newImage);

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
     * @param mixed  $image     GD resource
     * @param string $color     Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                          Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @throws \JBZoo\Utils\Exception
     */
    public static function fill($image, $color = '#000000')
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $rgba = Helper::normalizeColor($color);
        $fillColor = imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);

        Helper::addAlpha($image, false);
        imagefilledrectangle($image, 0, 0, $width, $height, $fillColor);
    }

    /**
     * Add text to an image
     *
     * @param mixed  $image    GD resource
     * @param string $text     Some text to output on image as watermark
     * @param string $fontFile TTF font file path
     * @param array  $params
     * @throws \JBZoo\Image\Exception
     */
    public static function text($image, $text, $fontFile, $params = [])
    {
        Text::render($image, $text, $fontFile, $params);
    }

    /**
     * Add border to an image
     *
     * @param resource $image  Image GD resource
     * @param array    $params Some
     * @return resource
     * @throws \JBZoo\Utils\Exception
     */
    public static function border($image, array $params = [])
    {
        $params = array_merge([
            'color' => '#333',
            'size'  => 1,
        ], $params);

        $size = Helper::range($params['size'], 1, 1000);
        $rgba = Helper::normalizeColor($params['color']);
        $width = imagesx($image);
        $height = imagesy($image);

        $x1 = 0;
        $y1 = 0;
        $x2 = $width - 1;
        $y2 = $height - 1;

        $color = imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);

        for ($i = 0; $i < $size; $i++) {
            imagerectangle($image, $x1++, $y1++, $x2--, $y2--, $color);
        }
    }
}
