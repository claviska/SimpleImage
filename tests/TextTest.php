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

use JBZoo\Utils\Image as ImageHelper;
use JBZoo\Image\Image;

/**
 * Class TextTest
 * @package JBZoo\PHPUnit
 */
class TextTest extends PHPUnit
{

    public function testText()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Смет.Денис =)', $font)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextColorRed()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font, array(
            'color' => 'f00',
        ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextPosition()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'position' => ImageHelper::TOP_LEFT,
                'offset-x' => 150,
                'offset-y' => 100,
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextColorMultiple()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'color' => array('#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'),
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextStroke()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'stroke-color' => '#0dd',
                'stroke-size'  => 5,
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextStrokeMultiple()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'stroke-color' => array('#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'),
                'stroke-size'  => 2,
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextAll()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'stroke-color'   => array('#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'),
                'color'          => array('#0ff', '#f0f', '#0f0', '#ff0', '#ff7f00', '#f00'),
                'stroke-spacing' => 5,
                'font-size'      => 48,
                'stroke-size'    => 5,
                'offset-x'       => -140,
                'offset-y'       => 100,
                'position'       => 't',
                'angle'          => -10,
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextStrokeDisable()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'stroke-size'  => null,
                'stroke-color' => null,
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testTextStrokeDisableColors()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');
        $font     = Helper::getOrig('font.ttf');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', $font,
            array(
                'stroke-size'  => null,
                'stroke-color' => null,
                'color'        => array('#f00', '#ff7f00', '#ff0', '#0f0', '#0ff', '#f0f'),
            ))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testTextUndefinedFontFile()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        $img->addFilter('text', 'Nice Butterfly', 'Undefined');
    }
}
