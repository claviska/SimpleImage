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

$default = include __DIR__ . '/vendor/jbzoo/codestyle/src/phan/default.php';

$config = array_merge($default, [
    'directory_list' => [
        'src',

        'vendor/jbzoo/utils',
    ]
]);

$index = array_search('UnusedSuppressionPlugin', $config['plugins'], true);
if ($index !== false) {
    unset($config['plugins'][$index]);
}

return $config;
