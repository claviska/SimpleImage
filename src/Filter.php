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

/**
 * Class Helper
 * @package JBZoo\Image
 */
class Filter
{
    /**
     * @param $image
     * @return $this
     */
    public static function sepia($image)
    {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagefilter($image, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Pixelate
     * @param mixed $image
     * @param int   $blockSize Size in pixels of each resulting block
     */
    public static function pixelate($image, $blockSize = 10)
    {
        imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
    }
}
