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

declare(strict_types=1);

namespace JBZoo\Image;

use JBZoo\Utils\Filter as VarFilter;
use JBZoo\Utils\Image as Helper;
use JBZoo\Utils\Vars;

/**
 * Class Filter
 * @package JBZoo\Image
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class Filter
{
    public const BLUR_SEL  = 0;
    public const BLUR_GAUS = 1;

    private const DEFAULT_BACKGROUND = '#000000';
    private const MAX_PERCENT        = 100;

    /**
     * Add sepia effect (emulation)
     *
     * @param \GdImage $image Image GD resource
     */
    public static function sepia(\GdImage $image): void
    {
        self::grayscale($image);
        \imagefilter($image, \IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Add grayscale effect
     *
     * @param \GdImage $image Image GD resource
     */
    public static function grayscale(\GdImage $image): void
    {
        \imagefilter($image, \IMG_FILTER_GRAYSCALE);
    }

    /**
     * Pixelate effect
     *
     * @param \GdImage $image     Image GD resource
     * @param int      $blockSize Size in pixels of each resulting block
     */
    public static function pixelate(\GdImage $image, int $blockSize = 10): void
    {
        $blockSize = VarFilter::int($blockSize);
        \imagefilter($image, \IMG_FILTER_PIXELATE, $blockSize);
    }

    /**
     * Edge Detect
     *
     * @param \GdImage $image Image GD resource
     */
    public static function edges(\GdImage $image): void
    {
        \imagefilter($image, \IMG_FILTER_EDGEDETECT);
    }

    /**
     * Emboss
     *
     * @param \GdImage $image Image GD resource
     */
    public static function emboss(\GdImage $image): void
    {
        \imagefilter($image, \IMG_FILTER_EMBOSS);
    }

    /**
     * Negative
     *
     * @param \GdImage $image Image GD resource
     */
    public static function invert(\GdImage $image): void
    {
        \imagefilter($image, \IMG_FILTER_NEGATE);
    }

    /**
     * Blur effect
     *
     * @param \GdImage $image  Image GD resource
     * @param int      $passes Number of times to apply the filter
     * @param int      $type   BLUR_SEL|BLUR_GAUS
     */
    public static function blur(\GdImage $image, int $passes = 1, int $type = self::BLUR_SEL): void
    {
        $passes = Helper::blur($passes);

        $filterType = \IMG_FILTER_SELECTIVE_BLUR;
        if (self::BLUR_GAUS === $type) {
            $filterType = \IMG_FILTER_GAUSSIAN_BLUR;
        }

        for ($i = 0; $i < $passes; $i++) {
            \imagefilter($image, $filterType);
        }
    }

    /**
     * Change brightness
     *
     * @param \GdImage $image Image GD resource
     * @param int      $level Darkest = -255, lightest = 255
     */
    public static function brightness(\GdImage $image, int $level): void
    {
        \imagefilter($image, \IMG_FILTER_BRIGHTNESS, Helper::brightness($level));
    }

    /**
     * Change contrast
     *
     * @param \GdImage $image Image GD resource
     * @param int      $level Min = -100, max = 100
     */
    public static function contrast(\GdImage $image, int $level): void
    {
        \imagefilter($image, \IMG_FILTER_CONTRAST, Helper::contrast($level));
    }

    /**
     * Set colorize
     *
     * @param \GdImage $image       Image GD resource
     * @param string   $color       Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float    $opacity     0-100
     *
     * @throws \JBZoo\Utils\Exception
     */
    public static function colorize(\GdImage $image, string $color, float $opacity): void
    {
        $rgba = Helper::normalizeColor($color);
        $alpha = Helper::opacity2Alpha($opacity);

        $red = Helper::color($rgba[0]);
        $green = Helper::color($rgba[1]);
        $blue = Helper::color($rgba[2]);

        \imagefilter($image, \IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
    }

    /**
     * Mean Remove
     *
     * @param \GdImage $image Image GD resource
     */
    public static function meanRemove(\GdImage $image): void
    {
        \imagefilter($image, \IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Smooth effect
     *
     * @param \GdImage $image  Image GD resource
     * @param int      $passes Number of times to apply the filter (1 - 2048)
     */
    public static function smooth(\GdImage $image, int $passes = 1): void
    {
        \imagefilter($image, \IMG_FILTER_SMOOTH, Helper::smooth($passes));
    }

    /**
     * Desaturate
     *
     * @param \GdImage $image   Image GD resource
     * @param int      $percent Level of desaturization.
     * @return \GdImage
     */
    public static function desaturate(\GdImage $image, int $percent = 100): \GdImage
    {
        // Determine percentage
        $percent = Helper::percent($percent);
        $width = \imagesx($image);
        $height = \imagesy($image);

        if ($percent === self::MAX_PERCENT) {
            self::grayscale($image);
        } elseif ($newImage = \imagecreatetruecolor($width, $height)) { // Make a desaturated copy of the image
            \imagealphablending($newImage, false);
            \imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
            \imagefilter($newImage, \IMG_FILTER_GRAYSCALE);

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
        } else {
            throw new Exception("Can't handle image resource by 'imagecreatetruecolor'");
        }

        return $image;
    }

    /**
     * Changes the opacity level of the image
     *
     * @param \GdImage  $image   Image GD resource
     * @param float|int $opacity 0-1 or 0-100
     *
     * @return \GdImage
     */
    public static function opacity(\GdImage $image, float|int $opacity): \GdImage
    {
        // Determine opacity
        $opacity = Helper::opacity($opacity);

        $width = \imagesx($image);
        $height = \imagesy($image);

        if ($newImage = \imagecreatetruecolor($width, $height)) {
            // Set a White & Transparent Background Color
            if ($background = \imagecolorallocatealpha($newImage, 0, 0, 0, 127)) {
                \imagefill($newImage, 0, 0, $background);

                // Copy and merge
                Helper::imageCopyMergeAlpha(
                    $newImage,
                    $image,
                    [0, 0],
                    [0, 0],
                    [$width, $height],
                    $opacity
                );

                \imagedestroy($image);

                return $newImage;
            }

            throw new Exception('Image resourced can\'t be handle by "imagecolorallocatealpha"');
        }

        throw new Exception('Image resourced can\'t be handle by "imagecreatetruecolor"');
    }

    /**
     * Rotate an image
     *
     * @param \GdImage     $image   Image GD resource
     * @param int          $angle   -360 < x < 360
     * @param array|string $bgColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return \GdImage
     * @throws \JBZoo\Utils\Exception
     */
    public static function rotate(
        \GdImage $image,
        int $angle,
        array|string $bgColor = self::DEFAULT_BACKGROUND
    ): \GdImage {
        // Perform the rotation
        $angle = Helper::rotate($angle);
        $rgba = Helper::normalizeColor($bgColor);

        $newBgColor = (int)\imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
        $newImage = \imagerotate($image, -($angle), $newBgColor);
        if (!$newImage) {
            throw new Exception("Image can't be rotated");
        }

        Helper::addAlpha($newImage);

        return $newImage;
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param \GdImage $image     GD resource
     * @param string   $direction Direction of flipping - x|y|yx|xy
     * @return \GdImage
     */
    public static function flip(\GdImage $image, string $direction): \GdImage
    {
        $direction = Helper::direction($direction);
        $width = \imagesx($image);
        $height = \imagesy($image);

        if ($newImage = \imagecreatetruecolor($width, $height)) {
            Helper::addAlpha($newImage);

            if ($direction === 'y') {
                for ($y = 0; $y < $height; $y++) {
                    \imagecopy($newImage, $image, 0, $y, 0, $height - $y - 1, $width, 1);
                }
            } elseif ($direction === 'x') {
                for ($x = 0; $x < $width; $x++) {
                    \imagecopy($newImage, $image, $x, 0, $width - $x - 1, 0, 1, $height);
                }
            } elseif ($direction === 'xy' || $direction === 'yx') {
                $newImage = self::flip($image, 'x');
                $newImage = self::flip($newImage, 'y');
            }

            return $newImage;
        }

        throw new Exception("Image resource can't be handle by \"imagecreatetruecolor\"");
    }

    /**
     * Fill image with color
     *
     * @param \GdImage     $image GD resource
     * @param array|string $color Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                            Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @throws \JBZoo\Utils\Exception
     */
    public static function fill(\GdImage $image, array|string $color = self::DEFAULT_BACKGROUND): void
    {
        $width = \imagesx($image);
        $height = \imagesy($image);

        $rgba = Helper::normalizeColor($color);
        $fillColor = (int)\imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);

        Helper::addAlpha($image, false);
        \imagefilledrectangle($image, 0, 0, $width, $height, $fillColor);
    }

    /**
     * Add text to an image
     *
     * @param \GdImage $image    GD resource
     * @param string   $text     Some text to output on image as watermark
     * @param string   $fontFile TTF font file path
     * @param array    $params
     * @throws Exception
     * @throws \JBZoo\Utils\Exception
     */
    public static function text(\GdImage $image, string $text, string $fontFile, array $params = []): void
    {
        Text::render($image, $text, $fontFile, $params);
    }

    /**
     * Add border to an image
     *
     * @param \GdImage $image  Image GD resource
     * @param array    $params Some
     * @throws \JBZoo\Utils\Exception
     */
    public static function border(\GdImage $image, array $params = []): void
    {
        $params = \array_merge([
            'color' => '#333',
            'size'  => 1,
        ], $params);

        $size = Vars::range((int)$params['size'], 1, 1000);
        $rgba = Helper::normalizeColor((string)$params['color']);
        $width = \imagesx($image);
        $height = \imagesy($image);

        $posX1 = 0;
        $posY1 = 0;
        $posX2 = $width - 1;
        $posY2 = $height - 1;

        $color = (int)\imagecolorallocatealpha($image, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);

        for ($i = 0; $i < $size; $i++) {
            \imagerectangle($image, $posX1++, $posY1++, $posX2--, $posY2--, $color);
        }
    }
}
