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

use JBZoo\Image\Image;

/**
 * Class WatermarkTest
 * @package JBZoo\PHPUnit
 */
class WatermarkTest extends PHPUnit
{
    public function testWatermarkTopLeft()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkTopRight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top right')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkTop()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottomLeft()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottomRight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom right')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkBottom()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkLeft()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'left')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkRight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'right')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkCenter()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'center')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermarkOpacity()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom', 200, 25, 25)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testWatermark()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');
        $overlay = TestHelper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

}
