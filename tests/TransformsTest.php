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
 * Class TransformsTest
 * @package JBZoo\PHPUnit
 */
class TransformsTest extends PHPUnit
{
    public function testFlipX()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'x')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipY()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'y')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipXY()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'xy')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipYX()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'yx')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testAutoOrient()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->autoOrient()
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
