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

use JBZoo\Image\Helper as ImageHelper;

/**
 * Class HelperTest
 * @package JBZoo\PHPUnit
 */
class HelperTest extends PHPUnit
{
    public function testCheckSystem()
    {
        isTrue(ImageHelper::checkGD());
    }

    public function testisJpeg()
    {
        isTrue(ImageHelper::isJpeg('jpeg'));
        isTrue(ImageHelper::isJpeg('JPG'));
        isTrue(ImageHelper::isJpeg('image/JPG'));
        isTrue(ImageHelper::isJpeg('image/JPeG'));

        isFalse(ImageHelper::isJpeg('png'));
        isFalse(ImageHelper::isJpeg('gif'));
    }

    public function testIsGif()
    {
        isTrue(ImageHelper::isGif('gif'));
        isTrue(ImageHelper::isGif('image/gif'));

        isFalse(ImageHelper::isGif('png'));
        isFalse(ImageHelper::isGif('jpeg'));
        isFalse(ImageHelper::isGif('jpg'));
    }

    public function testIsPng()
    {
        isTrue(ImageHelper::isPng('PnG'));
        isTrue(ImageHelper::isPng('image/PNG'));

        isFalse(ImageHelper::isPng('jpg'));
        isFalse(ImageHelper::isPng('jpeg'));
        isFalse(ImageHelper::isPng('gif'));
    }

    public function testNormalizeColor()
    {
        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor('#0088cc')
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor('0088cc')
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor('08c')
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor('#08c')
        );


        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor(array('r' => 0, 'g' => '136', 'b' => '204'))
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor(array('r' => '0', 'g' => '   136   ', 'b' => ' 204 '))
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor(array('r' => '0', 'g' => '   136   ', 'b' => ' 204 ', 'a' => '0'))
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 0),
            ImageHelper::normalizeColor(array('r' => '0', 'g' => '   136   ', 'b' => ' 204 ', 'a' => '0'))
        );

        isSame(
            array('r' => 0, 'g' => 136, 'b' => 204, 'a' => 1),
            ImageHelper::normalizeColor(array('0', '   136   ', ' 204 ', '1'))
        );

        isSame(
            array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 127),
            ImageHelper::normalizeColor(array('1000', '   1036   ', ' 2004 ', '1000'))
        );
    }

    public function testOpacity()
    {
        isSame(0, ImageHelper::opacity(-10));
        isSame(0, ImageHelper::opacity(0));
        isSame(1, ImageHelper::opacity(0.01));
        isSame(99, ImageHelper::opacity(0.99));
        isSame(100, ImageHelper::opacity(1));
        isSame(2, ImageHelper::opacity(2));
        isSame(10, ImageHelper::opacity(10));
        isSame(100, ImageHelper::opacity(200));

        isSame(80, ImageHelper::opacity(0.8));
        isSame(100, ImageHelper::opacity(800));
    }

    public function testOpacity2Alpha()
    {
        isSame(127, ImageHelper::opacity2Alpha(-200));
        isSame(127, ImageHelper::opacity2Alpha(-127));

        isSame(127, ImageHelper::opacity2Alpha(-50));
        isSame(127, ImageHelper::opacity2Alpha(-25));
        isSame(127, ImageHelper::opacity2Alpha(-1));
        isSame(127, ImageHelper::opacity2Alpha(-0.5));

        isSame(127, ImageHelper::opacity2Alpha(0));

        isSame(63, ImageHelper::opacity2Alpha(0.5));
        isSame(125, ImageHelper::opacity2Alpha(0.01));
        isSame(1, ImageHelper::opacity2Alpha(0.99));
        isSame(0, ImageHelper::opacity2Alpha(1));
        isSame(124, ImageHelper::opacity2Alpha(2));
        isSame(95, ImageHelper::opacity2Alpha(25));
        isSame(63, ImageHelper::opacity2Alpha(50));
        isSame(1, ImageHelper::opacity2Alpha(99));
        isSame(0, ImageHelper::opacity2Alpha(100));

        isSame(0, ImageHelper::opacity2Alpha(127));
        isSame(0, ImageHelper::opacity2Alpha(200));
    }

    public function testRotate()
    {
        isSame(-360, ImageHelper::rotate(-700));
        isSame(0, ImageHelper::rotate(0));
        isSame(360, ImageHelper::rotate(700));
    }

    public function testBrightness()
    {
        isSame(-255, ImageHelper::brightness(-700));
        isSame(0, ImageHelper::brightness(0));
        isSame(255, ImageHelper::brightness(700));
    }

    public function testContrast()
    {
        isSame(-100, ImageHelper::contrast(-700));
        isSame(0, ImageHelper::contrast(0));
        isSame(100, ImageHelper::contrast(700));
    }

    public function testColorize()
    {
        isSame(-255, ImageHelper::colorize(-700));
        isSame(0, ImageHelper::colorize(0));
        isSame(255, ImageHelper::colorize(700));
    }

    public function testSmooth()
    {
        isSame(1, ImageHelper::smooth(0));
        isSame(10, ImageHelper::smooth(700));
    }

    public function testBlur()
    {
        isSame(1, ImageHelper::blur(0));
        isSame(3, ImageHelper::blur(3));
        isSame(10, ImageHelper::blur(10));
    }

    public function testDirection()
    {
        isSame('x', ImageHelper::direction(''));
        isSame('x', ImageHelper::direction('X'));
        isSame('y', ImageHelper::direction('Y'));
        isSame('xy', ImageHelper::direction('xy'));
        isSame('yx', ImageHelper::direction('Yx'));
    }
}
