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

use JBZoo\Image\Image;

/**
 * Class ResizeTest
 * @package JBZoo\PHPUnit
 */
class ResizeTest extends PHPUnit
{
    public function testResizeJpeg()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '_320_239.jpg');
        $original = Helper::getOrig('butterfly.png');

        $img = new Image($original);
        $img->resize(320, 239)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testResizeGif()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.gif');
        $actual   = Helper::getActual(__FUNCTION__ . '_320_239.gif');
        $original = Helper::getOrig('butterfly.gif');

        $img = new Image($original);
        $img->resize(320, 239)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testResizeTransparent()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.gif');
        $actual   = Helper::getActual(__FUNCTION__ . '.gif');
        $original = Helper::getOrig('1x1.gif');

        $img = new Image($original);
        $img->resize(50, 50)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCrop()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->crop(160, 110, 460, 360)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCropWrongCoord()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->crop(460, 360, 160, 110)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFitToWidth()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->fitToWidth(100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFitToHeight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->fitToHeight(100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testThumbnailHeight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(100, 75)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testThumbnailWidth()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.gif');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(75)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testThumbnailCropTop()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.png');

        $img = new Image();
        $img->loadFile($original)
            ->thumbnail(200, 50, true)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBestFitWidth()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(100, 400)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBestFitHeight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(100, 40)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testBestFitNoChange()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->bestFit(10000, 10000)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }
}
