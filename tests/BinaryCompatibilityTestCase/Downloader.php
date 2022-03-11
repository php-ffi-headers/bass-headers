<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass\Tests\BinaryCompatibilityTestCase;

use PHPUnit\Framework\Assert;

class Downloader
{
    /**
     * @param string $url
     * @return string
     */
    private static function temp(string $url): string
    {
        return \sys_get_temp_dir() . '/' . \hash('md5', $url);
    }

    /**
     * @param string $url
     * @return string
     */
    private static function archive(string $url): string
    {
        $temp = self::temp($url) . '.zip';

        if (\is_file($temp)) {
            return $temp;
        }

        \error_clear_last();

        $stream = @\fopen($url, 'rb');

        if ($error = \error_get_last()) {
            throw new \RuntimeException($error['message']);
        }

        \stream_copy_to_stream($stream, \fopen($temp, 'ab+'));

        return $temp;
    }

    /**
     * @param string $url
     * @param array $args
     * @return DownloaderResult
     * @throws \Throwable
     */
    public static function download(string $url, array $args = []): DownloaderResult
    {
        $url = \vsprintf($url, $args);

        try {
            $archive = self::archive($url);
        } catch (\Throwable $e) {
            if (\str_contains($e->getMessage(), 'Operation timed out')) {
                Assert::markTestIncomplete('Can not complete test: Downloading operation timed out');
            }

            if (\str_contains($e->getMessage(), '404')) {
                Assert::markTestSkipped('Can not complete test: ' . $url . ' not found');
            }

            throw $e;
        }

        return new DownloaderResult($archive);
    }
}
