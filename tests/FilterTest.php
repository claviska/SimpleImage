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

namespace JBZoo\PHPUnit;

use JBZoo\Image\Filter;
use JBZoo\Image\Image;

/**
 * Class FilterTest
 * @package JBZoo\PHPUnit
 */
class FilterTest extends PHPUnit
{

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testFilterUndefined()
    {
        $img = new Image();
        $img->addFilter('undefined');
    }

    public function testFilterSepia()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('sepia')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterPixelate()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('pixelate', 25)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterCustom()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter(function ($image, $blockSize) {
                imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
            }, 2)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterEdges()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('edges')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterEmboss()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('emboss')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterInvert()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('invert')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterBlurGaussian()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_GAUS)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterBlurSelective()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_SEL)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterBrightness100()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', 100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterBrightnessN100()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', -100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterContrast()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('contrast', -50)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterColorize()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('colorize', '#08c', .75)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterMeanRemove()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('meanRemove')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterSmooth()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('smooth', 6)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterDesaturate100()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFilterDesaturate50()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 50)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testOpacity_05()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', .5)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testOpacity_50()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 30)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testOpacity_0()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 0)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testOpacity_100()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 150)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipRorate90()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 90)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipRorate45()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 45)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipRorateRevert275White()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', -275, array(255, 255, 255, 127))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBorder()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBorderColor()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', array('color' => 'f00'))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBorderSize()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', array('size' => 5))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBorderColorSize()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', array(
                'color' => 'ff0',
                'size'  => '5',
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }
}
