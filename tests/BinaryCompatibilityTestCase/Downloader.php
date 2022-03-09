<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass\Tests\BinaryCompatibilityTestCase;

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
     */
    public static function download(string $url, array $args = []): DownloaderResult
    {
        $archive = self::archive(\vsprintf($url, $args));

        return new DownloaderResult($archive);
    }
}
