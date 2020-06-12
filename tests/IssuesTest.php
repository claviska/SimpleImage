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
 * Class IssuesTest
 * @package JBZoo\PHPUnit
 */
class IssuesTest extends PHPUnit
{
    public function testIssue7()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $base = TestHelper::getOrig('issue-7/back.png');
        $overlay = TestHelper::getOrig('issue-7/overlay.png');

        $image = new Image($base);
        $image
            ->overlay($overlay)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testIssue8()
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual = TestHelper::getActual(__FUNCTION__ . '.png');
        $base = TestHelper::getOrig('issue-8/original.png');

        $img = new Image($base);

        if ($img->getHeight() != $img->getWidth()) {

            if ($img->getWidth() < 175) {
                $img->fitToWidth($img->getWidth());
            } else {
                $img->fitToWidth(175);
            }

        } else {
            $img->bestFit(175, 175);
        }

        $img->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }
}
