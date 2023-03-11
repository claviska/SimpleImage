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

use JBZoo\Utils\FS;

class TestHelper
{
    public static function getActual(string $filename): string
    {
        $filename = self::camelCase2Human($filename);

        return FS::clean(__DIR__ . "/../build/images/{$filename}");
    }

    public static function getExpected(string $filename): string
    {
        $filename = self::camelCase2Human($filename);

        return FS::clean(__DIR__ . "/expected/{$filename}");
    }

    public static function getOrig($filename): string
    {
        return FS::clean(__DIR__ . "/resources/{$filename}");
    }

    public static function isFileEq(string $expected, string $actual): void
    {
        $expected = \realpath($expected);
        $actual   = \realpath($actual);

        isNotEmpty($expected);
        isNotEmpty($actual);

        isFile($expected);
        isFile($actual);

        // Because realpath cache is!
        \clearstatcache(false, $actual);
        \clearstatcache(false, $expected);

        $actualSize   = \filesize($actual);
        $expectedSize = \filesize($expected);

        $relPathExpected = FS::getRelative($expected, __DIR__ . '/..');
        $relPathActual   = FS::getRelative($actual, __DIR__ . '/..');
        $errorMessage    = "Expected: ./{$relPathExpected} ({$expectedSize});\nActual:   ./{$relPathActual} ({$actualSize})";

        // isSame($expectedSize, $actualSize, "Invalid size:\n{$errorMessage}");
        // isFileEq($expected, $actual, "Invalid bin:\n{$errorMessage}");
    }

    /**
     * @return mixed|string
     */
    public static function camelCase2Human(string $input)
    {
        $original = $input;

        if (\str_contains($input, '\\')) {
            $input = \explode('\\', $input);
            $input = \end($input);
        }

        $input = \preg_replace('#^(test)#i', '', $input);
        $input = \preg_replace('#(test)$#i', '', $input);

        $output = \preg_replace(['/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'], '_$0', $input);
        $output = \preg_replace('#_{1,}#', '_', $output);

        $output = \trim($output);
        $output = \strtolower($output);

        if ($output === '') {
            return $original;
        }

        return $output;
    }
}
