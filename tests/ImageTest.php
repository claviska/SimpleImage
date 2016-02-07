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
 * Class ImageTest
 * @package JBZoo\PHPUnit
 */
class ImageTest extends PHPUnit
{
    protected $_class = '\JBZoo\Image\Image';

    public function testCreateInstance()
    {
        $img = new Image();
        isClass($this->_class, $img);
    }

    public function testOpen()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isClass($this->_class, $img->open($original));
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testOpenUndefined()
    {
        $img = new Image();
        $img->open(Helper::getOrig('undefined.jpg'));
    }

    public function testCleanup()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isClass($this->_class, $img->open($original));

        $img->cleanup();
        isCount(1, array_filter($img->getInfo()));
    }

    public function testGetInfoJpeg()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(640, $info['width']);
        is(478, $info['height']);
        is('image/jpeg', $info['mime']);
        is('landscape', $info['orient']);
        isTrue(is_array($info['exif']));
        isTrue($img->isJpeg());
    }

    public function testGetInfoPng()
    {
        $original = Helper::getOrig('butterfly.png');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(640, $info['width']);
        is(478, $info['height']);
        is('image/png', $info['mime']);
        is('landscape', $info['orient']);
        isEmpty($info['exif']);
        isTrue($img->isPng());
    }

    public function testGetInfoGif()
    {
        $original = Helper::getOrig('butterfly.gif');

        $img  = new Image($original);
        $info = $img->getInfo();

        is(478, $info['width']);
        is(640, $info['height']);
        is('image/gif', $info['mime']);
        is('portrait', $info['orient']);
        isEmpty($info['exif']);

        isTrue($img->isGif());
    }

    public function testOrientation()
    {
        $img = new Image(Helper::getOrig('butterfly.gif'));
        isTrue($img->isPortrait());

        $img = new Image(Helper::getOrig('butterfly.jpg'));
        isTrue($img->isLandscape());

        $img = new Image(Helper::getOrig('basketball.gif'));
        isTrue($img->isSquare());
    }

    public function testSave()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $original = Helper::getOrig('butterfly.jpg');

        if (copy($original, $actual)) {
            $img  = new Image($actual);
            $info = $img->save(1)
                ->getInfo();

            is(1, $info['quality']);
            is($actual, $info['filename']);
            //isNotEmpty($info['exif']);
            Helper::isFileEq($actual, $excepted);

        } else {
            isTrue(false, 'Can\'t copy original file!');
        }
    }

    public function testConvertToGif()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.gif');
        $excepted = Helper::getExpected(__FUNCTION__ . '.gif');

        $img = new Image();
        $img->open($original)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testConvertToJpg()
    {
        $original   = Helper::getOrig('butterfly.jpg');
        $excepted   = Helper::getExpected(__FUNCTION__ . '.jpg');
        $actualJpg  = Helper::getActual(__FUNCTION__ . '.jpg');
        $actualJpeg = Helper::getActual(__FUNCTION__ . '.jpeg');

        $img = new Image();
        $img->open($original)->saveAs($actualJpg);
        Helper::isFileEq($actualJpg, $excepted);

        $img = new Image();
        $img->open($original)->saveAs($actualJpeg)->setQuality(100);
        Helper::isFileEq($actualJpeg, $excepted);
    }

    public function testConvertToPng()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');

        $img = new Image();
        $img->open($original)->saveAs($actual);
        Helper::isFileEq($actual, $excepted);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testConvertToUndefindFormat()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.qwerty');

        $img = new Image();
        $img->open($original)
            ->saveAs($actual);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testConvertToUndefindPath()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual('qwerty/' . __FUNCTION__ . '.png');

        $img = new Image();
        $img->open($original)
            ->saveAs($actual);
    }

    public function testCreateFromScratchOnlyWidth()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCreateFromScratchWidthAndHeight()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200, 100)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testCreateFromScratchFull()
    {
        $actual   = Helper::getActual(__FUNCTION__ . '.png');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');

        $img = new Image();
        $img->create(200, 100, '#08c')->saveAs($actual);
        Helper::isFileEq($actual, $excepted);
    }
}
