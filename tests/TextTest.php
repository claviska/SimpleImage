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
use JBZoo\Utils\Image as ImageHelper;

/**
 * Class TextTest
 * @package JBZoo\PHPUnit
 */
class TextTest extends PHPUnit
{
    public function testText()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Смет.Денис =)', $font)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextColorRed()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font, [
            'color' => 'f00',
        ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextPosition()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'position' => ImageHelper::TOP_LEFT,
                'offset-x' => 150,
                'offset-y' => 100,
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextColorMultiple()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'color' => ['#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'],
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextStroke()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'stroke-color' => '#0dd',
                'stroke-size'  => 5,
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextStrokeMultiple()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'stroke-color' => ['#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'],
                'stroke-size'  => 2,
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextAll()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'stroke-color'   => ['#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'],
                'color'          => ['#0ff', '#f0f', '#0f0', '#ff0', '#ff7f00', '#f00'],
                'stroke-spacing' => 5,
                'font-size'      => 48,
                'stroke-size'    => 5,
                'offset-x'       => -140,
                'offset-y'       => 100,
                'position'       => 't',
                'angle'          => -10,
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextStrokeDisable()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'stroke-size'  => null,
                'stroke-color' => null,
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextStrokeDisableColors()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.jpg');
        $actual = TestHelper::getActual(__FUNCTION__ . '.jpg');
        $original = TestHelper::getOrig('butterfly.jpg');
        $font = TestHelper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            [
                'stroke-size'  => null,
                'stroke-color' => null,
                'color'        => ['#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'],
            ])
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testTextUndefinedFontFile()
    {
        $this->expectException(\JBZoo\Image\Exception::class);

        $original = TestHelper::getOrig('butterfly.jpg');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', 'Undefined');
    }
}
