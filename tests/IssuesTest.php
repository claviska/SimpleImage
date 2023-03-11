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

class IssuesTest extends PHPUnit
{
    public function testIssue7(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $base     = TestHelper::getOrig('issue-7/back.png');
        $overlay  = TestHelper::getOrig('issue-7/overlay.png');

        $image = new Image($base);
        $image
            ->overlay($overlay)
            ->saveAs($actual);

        TestHelper::isFileEq($excepted, $actual);
    }

    public function testIssue8(): void
    {
        $excepted = TestHelper::getExpected(__FUNCTION__ . '.png');
        $actual   = TestHelper::getActual(__FUNCTION__ . '.png');
        $base     = TestHelper::getOrig('issue-8/original.png');

        $img = new Image($base);

        if ($img->getHeight() !== $img->getWidth()) {
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
