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
 * Class ResizeTest
 * @package JBZoo\PHPUnit
 */
class ResizeTest extends PHPUnit
{
    public function testResizeJpeg()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '_320_239.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '_320_239.jpg');
        $original = TestHelper::getOrig('butterfly.png');

        $img = new Image($original);
        $img->resize(320, 239)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testResizeGif()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '_320_239.gif');
        $actual = TestHelper::getActual(__FUNCTION__ . '_320_239.gif');
        $original = TestHelper::getOrig('butterfly.gif');

        $img = new Image($original);
        $img->resize(320, 239)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testResizeTransparent()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.gif');
        $actual = TestHelper::getActual(__FUNCTION__ . '.gif');
        $original = TestHelper::getOrig('1x1.gif');

        $img = new Image($original);
        $img->resize(50, 50)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testCrop()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->crop(160, 110, 460, 360)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testCropWrongCoord()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->crop(460, 360, 160, 110)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFitToWidth()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->fitToWidth(100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFitToHeight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->fitToHeight(100)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testThumbnailHeight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(100, 75)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testThumbnailWidth()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.gif');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(75)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testThumbnailCropTop()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.png');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(200, 50, true)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBestFitWidth()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(100, 400)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBestFitHeight()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(100, 40)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testBestFitNoChange()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(10000, 10000)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
