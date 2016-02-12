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
 * Class TransformsTest
 * @package JBZoo\PHPUnit
 */
class TransformsTest extends PHPUnit
{
    public function testFlipX()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'x')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipY()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'y')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipXY()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'xy')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testFlipYX()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'yx')
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testAutoOrient()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->autoOrient()
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }
}
