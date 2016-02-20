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

use JBZoo\Image\Exception;
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
        isClass($this->_class, $img->loadFile($original));
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testOpenUndefined()
    {
        $img = new Image();
        $img->loadFile('undefined.jpg');
    }

    public function testCleanup()
    {
        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isClass($this->_class, $img->loadFile($original));

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
        $img->loadFile($original)
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
        $img->loadFile($original)
            ->saveAs($actualJpg);

        Helper::isFileEq($actualJpg, $excepted);

        $img = new Image();
        $img->loadFile($original)->saveAs($actualJpeg)->setQuality(100);
        Helper::isFileEq($actualJpeg, $excepted);
    }

    public function testConvertToPng()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.png');

        $img = new Image();
        $img->loadFile($original)
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testConvertToUndefindFormat()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.qwerty');

        $img = new Image();
        $img->loadFile($original)
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
        $img->loadFile($original)
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
        $img->create(200, 100, array(0, 136, 204, 64))
            ->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    public function testGetBase64()
    {
        $original = Helper::getOrig('smile.gif');

        $img = new Image($original);
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64());
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64(null));
        isContain('data:image/gif;base64,R0lGODlhEAAQAMYAAHB', $img->getBase64('gif'));
        isContain('data:image/png;base64,iVBORw0KGgoAAAANSUh', $img->getBase64('png'));
        isContain('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ', $img->getBase64('jpeg'));
        isContain('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ', $img->getBase64('jpg'));

        isContain('R0lGODlhEAAQAMYAAHB', $img->getBase64(null, null, false));
        isContain('R0lGODlhEAAQAMYAAHB', $img->getBase64('gif', null, false));
        isContain('iVBORw0KGgoAAAANSUh', $img->getBase64('png', null, false));
        isContain('/9j/4AAQSkZJRgABAQ', $img->getBase64('jpeg', null, false));
        isContain('/9j/4AAQSkZJRgABAQ', $img->getBase64('jpg', null, false));
    }

    public function testGetBinary()
    {
        $original = Helper::getOrig('smile.gif');

        $img = new Image($original);

        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary()));
        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary(null)));
        isContain('47494638396110001000c60000707070a0a0a0eada22f0f0f0cbbd1e2e2b06f3', bin2hex($img->getBinary('gif')));
        isContain('89504e470d0a1a0a0000000d4948445200000010000000100803000000282d0f', bin2hex($img->getBinary('png')));
        isContain('ffd8ffe000104a46494600010100000100010000fffe003a43524541544f523a', bin2hex($img->getBinary('jpeg')));
        isContain('ffd8ffe000104a46494600010100000100010000fffe003a43524541544f523a', bin2hex($img->getBinary('jpg')));
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testSaveUndefined()
    {
        $img = new Image();
        $img->save();
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testToBase64Undefined()
    {
        $img = new Image();
        $img->getBase64();
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testSaveAsUndefined()
    {
        $img = new Image();
        $img->saveAs('');
    }

    public function testGetPath()
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $_SERVER['HTTP_HOST']     = 'test.dev';
        $_SERVER['SERVER_PORT']   = 80;
        $_SERVER['REQUEST_URI']   = '/test.php?foo=bar';
        $_SERVER['QUERY_STRING']  = 'foo=bar';
        $_SERVER['PHP_SELF']      = '/test.php';

        $original = Helper::getOrig('butterfly.jpg');

        $img = new Image($original);
        isSame('resources/butterfly.jpg', $img->getPath());
        isSame('http://test.dev/resources/butterfly.jpg', $img->getUrl());
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testGetPathUndefined()
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $_SERVER['HTTP_HOST']     = 'test.dev';
        $_SERVER['SERVER_PORT']   = 80;
        $_SERVER['REQUEST_URI']   = '/test.php?foo=bar';
        $_SERVER['QUERY_STRING']  = 'foo=bar';
        $_SERVER['PHP_SELF']      = '/test.php';

        $img = new Image();
        isSame('', $img->getUrl());
    }

    /**
     * @requires PHP 5.4
     */
    public function testOpenAsString()
    {
        $imgStr = 'R0lGODlhEAAQAOZeAHBwcKCgoOraIvDw8Mu9Hi4rBvPFJvTKJpyRF/bXJfPAJ/jeJU5IC0B'
            . 'AQOq0J+ewKPnmJffZJVc6FvPCJruuGz46CYyDFPXPJpCQkH10EqugGSYaC+GoKAAAALCwsNicKUlGQvbUJtGaKWBgYODg4LWB'
            . 'JDUnF/jhJa9+JXFgSl4/GUxHQjwpEdadKUxDOEM9NmNdVcaPJ9rLIPbRJtifKUEwGuSsKNmhKV1XDXdPG6p2JXhRG1U5FiMdF'
            . 'lpWU76DJ8DAwMuVKtDQ0GlFFrR8JEQtELF8KO24J3VNGT0zKG1lEIxgHdugKXBNGykbCzw3Mt6kKNOcKfXMJt6mKG5LG2JAFT'
            . 'gwJW5fSlNNRenSI/THJo5iIPC8J/rpJf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAF4ALAAAAAAQABAAAAfZ'
            . 'gF6CXiQAIBsbSSNAg40YTlstDw8cMVUrA40jKjcKWiEhMwZHRCYkggE8UxMLXa6uCVw/T14DPVETWQwarhoWXRFcOwEBTVwJC'
            . 'B0VrhUdFF0HNC8AKAoQAhkErggMsA5FDSIHr+SvCwoS4QcUAuVdBBYR6AAlBhYZ5QIFBBcOLB45uJwogOAVAYIQJgSB4cXKhw'
            . 'MCcBRQwqCCNilcqATw4mEIhwtdBFCQ0QXCBS5GQAwCgISJgQgLFiQw4ECHi0yDAtRY8sHGAyglJPjA2WgAhgZXUmABIKRRIAA7';

        $base64 = 'data:image/gif;base64,' . $imgStr;
        $bin    = base64_decode($imgStr, true);

        $actualClean  = Helper::getActual(__FUNCTION__ . '_clean.gif');
        $actualBase64 = Helper::getActual(__FUNCTION__ . '_base64.gif');
        $actualBin    = Helper::getActual(__FUNCTION__ . '_bin.gif');
        $excepted     = Helper::getExpected(__FUNCTION__ . '.gif');

        $img = new Image($imgStr);
        $img->saveAs($actualClean);
        Helper::isFileEq($actualClean, $excepted);

        $img = new Image($base64);
        $img->saveAs($actualBase64);
        Helper::isFileEq($actualBase64, $excepted);

        $img = new Image($bin);
        $img->saveAs($actualBin);
        Helper::isFileEq($actualBin, $excepted);
    }

    public function testUnsupportedFormat()
    {
        $excepted = Helper::getExpected(__FUNCTION__ . '.png');
        $actual   = Helper::getActual(__FUNCTION__ . '.tmp');
        $original = Helper::getOrig('1x1.tmp');

        if (copy($original, $actual)) {
            $img  = new Image($actual);
            $info = $img
                ->thumbnail(100, 200)
                ->save()
                ->getInfo();

            is('image/gif', $info['mime']);
            is(100, $info['width']);
            is(200, $info['height']);

            Helper::isFileEq($actual, $excepted);

        } else {
            fail('Can\'t copy original file!');
        }
    }

    public function testOpenImageResource()
    {
        $original = Helper::getOrig('butterfly.jpg');
        $actual   = Helper::getActual(__FUNCTION__ . '.jpg');
        $excepted = Helper::getExpected(__FUNCTION__ . '.jpg');

        $imgRes = imagecreatefromjpeg($original);

        $img = new Image($imgRes);
        $img->saveAs($actual);

        Helper::isFileEq($actual, $excepted);
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testLoadStringUndefined()
    {
        $img = new Image();
        $img->loadString('');
    }

    /**
     * @expectedException \JBZoo\Image\Exception
     */
    public function testLoadResourceUndefined()
    {
        $img = new Image();
        $img->loadResource('');
    }

    public function testPHP53_getimagesizefromstring()
    {
        try {
            $img = new Image();
            $img->loadString('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
        } catch (Exception $e) {
            isTrue(strpos(PHP_VERSION, '5.3') === 0);
        }
    }
}
