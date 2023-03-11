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

use JBZoo\Image\Image;

class WatermarkTest extends PHPUnit
{
    public function testWatermarkTopLeft(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkTopRight(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top right')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkTop(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottomLeft(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottomRight(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottom(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkLeft(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkRight(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'right')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkCenter(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'center')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkOpacity(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom', 200, 25, 25)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermark(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay  = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
