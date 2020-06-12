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

namespace JBZoo\PHPUnit;

use JBZoo\Image\Filter;
use JBZoo\Image\Image;

/**
 * Class FilterTest
 * @package JBZoo\PHPUnit
 */
class FilterTest extends PHPUnit
{
    public function testFilterUndefined()
    {
        $this->expectException(\JBZoo\Image\Exception::class);

        $img = new Image();
        $img->addFilter('undefined');
    }

    public function testFilterSepia()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('sepia')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterPixelate()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('pixelate', 25)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterCustom()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter(function ($image, $blockSize) {
                imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
            }, 2)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterEdges()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('edges')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterEmboss()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('emboss')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterInvert()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('invert')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBlurGaussian()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_GAUS)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBlurSelective()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_SEL)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBrightness100()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', 100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBrightnessN100()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', -100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterContrast()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('contrast', -50)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterColorize()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('colorize', '#08c', .75)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterMeanRemove()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('meanRemove')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterSmooth()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('smooth', 6)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterDesaturate100()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterDesaturate50()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 50)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity_05()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', .5)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity_50()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 30)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity_0()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 0)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity_100()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 150)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorate90()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 90)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorate45()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 45)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorateRevert275White()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', -275, [255, 255, 255, 127])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorder()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderColor()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', ['color' => 'f00'])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderSize()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', ['size' => 5])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderColorSize()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', [
                'color' => 'ff0',
                'size'  => '5',
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
