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
 * Class WatermarkTest
 * @package JBZoo\PHPUnit
 */
class WatermarkTest extends PHPUnit
{
    public function testWatermarkTopLeft()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top left')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkTopRight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top right')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkTop()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'top')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkBottomLeft()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom left')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkBottomRight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom right')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkBottom()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkLeft()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'left')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkRight()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'right')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkCenter()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'center')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermarkOpacity()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay, 'bottom', 200, 25, 25)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testWatermark()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $original = Helper::getOrig('butterfly.jpg');
        $overlay  = Helper::getOrig('overlay.png');

        $img = new Image();
        $img->loadFile($original)
            ->overlay($overlay)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

}
