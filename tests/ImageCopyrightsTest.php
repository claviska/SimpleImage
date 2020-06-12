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

use Symfony\Component\Finder\Finder;

/**
 * Class ImageCopyrightsTest
 * @package JBZoo\PHPUnit
 */
class ImageCopyrightsTest extends AbstractCodestyleTest
{
    public function testCyrillic(): void
    {
        $finder = (new Finder())
            ->files()
            ->in($this->projectRoot)
            ->exclude($this->excludePaths)
            ->notPath(basename(__FILE__))
            ->ignoreDotFiles(false)
            ->notName('TextTest.php')
            ->notName('FilterTest.php')
            ->notName('StringTest.php')
            ->notName('SlugTest.php')
            ->notName('ImageTest.php')
            ->notName('Slug.php')
            ->notName('Filter.php')
            ->notName('/\.md$/')
            ->notName('/\.min\.(js|css)$/')
            ->notName('/\.min\.(js|css)\.map$/');

        foreach ($finder as $file) {
            $content = openFile($file->getPathname());

            /** @noinspection NotOptimalRegularExpressionsInspection */
            if (preg_match('#[А-Яа-яЁё]#ius', (string)$content)) {
                fail('File contains cyrillic symbols: ' . $file); // Short message in terminal
            }
        }

        isTrue(true); // One assert is a minimum for test complete
    }
}
