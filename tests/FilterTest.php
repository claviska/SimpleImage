<?php

/**
 * JBZoo Toolbox - Image.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Image
 */

declare(strict_types=1);

namespace JBZoo\PHPUnit;

use JBZoo\Image\Filter;
use JBZoo\Image\Image;

class FilterTest extends PHPUnit
{
    public function testFilterUndefined(): void
    {
        $this->expectException(\JBZoo\Image\Exception::class);

        $img = new Image();
        $img->addFilter('undefined');
    }

    public function testFilterSepia(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('sepia')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterPixelate(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('pixelate', 25)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterCustom(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter(static function ($image, $blockSize): void {
                \imagefilter($image, \IMG_FILTER_PIXELATE, $blockSize, true);
            }, 2)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterEdges(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('edges')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterEmboss(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('emboss')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterInvert(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('invert')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBlurGaussian(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_GAUS)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBlurSelective(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('blur', 10, Filter::BLUR_SEL)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBrightness100(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', 100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterBrightnessN100(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('brightness', -100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterContrast(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('contrast', -50)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterColorize(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('colorize', '#08c', .75)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterMeanRemove(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('meanRemove')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterSmooth(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('smooth', 6)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterDesaturate100(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFilterDesaturate50(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('desaturate', 50)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity05(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', .5)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity50(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 30)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity0(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 0)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testOpacity100(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('opacity', 150)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorate90(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 90)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorate45(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', 45)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipRorateRevert275White(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('rotate', -275, [255, 255, 255, 127])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorder(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderColor(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', ['color' => 'f00'])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderSize(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('border', ['size' => 5])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBorderColorSize(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
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
