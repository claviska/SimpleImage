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

class TransformsTest extends PHPUnit
{
    public function testFlipX(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'x')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipY(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'y')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipXY(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'xy')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testFlipYX(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->addFilter('flip', 'yx')
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testAutoOrient(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image();
        $img->loadFile($original)
            ->autoOrient()
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
