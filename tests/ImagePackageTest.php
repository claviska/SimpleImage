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

final class ImagePackageTest extends \JBZoo\Codestyle\PHPUnit\AbstractPackageTest
{
    protected string $packageName = 'Image';

    public function testCyrillic(): void
    {
        skip('The project uses Cyrillic in the code');
    }
}
