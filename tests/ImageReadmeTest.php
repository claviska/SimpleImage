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

declare(strict_types=1);

namespace JBZoo\PHPUnit;

/**
 * Class ImageReadmeTest
 *
 * @package JBZoo\PHPUnit
 */
class ImageReadmeTest extends AbstractReadmeTest
{
    protected $packageName = 'Image';

    protected function setUp(): void
    {
        parent::setUp();
        $this->params['strict_types'] = true;
        $this->params['travis'] = false;
    }
}
