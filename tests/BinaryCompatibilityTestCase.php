<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass\Tests;

use FFI\Env\Runtime;
use FFI\Headers\Bass;
use FFI\Headers\Bass\Platform;
use FFI\Headers\Bass\Tests\BinaryCompatibilityTestCase\Downloader;
use FFI\Headers\Bass\Tests\BinaryCompatibilityTestCase\DownloaderResult;
use FFI\Headers\Bass\Version;

/**
 * @requires extension ffi
 */
class BinaryCompatibilityTestCase extends TestCase
{
    public function setUp(): void
    {
        if (!Runtime::isAvailable()) {
            $this->markTestSkipped('An ext-ffi extension must be available and enabled');
        }

        parent::setUp();
    }

    /**
     * @requires OSFAMILY Linux
     * @dataProvider versionsDataProvider
     */
    public function testLinuxBinaryCompatibility(Version $version): void
    {
        if (!$version->supportedOn(Platform::LINUX)) {
            $this->markTestSkipped('Linux not supported by version ' . $version->toString());
        }

        $binary = __DIR__ . '/storage/libbass-' . $version->toString() . '.so';

        if (!\is_file($binary)) {
            Downloader::download('https://www.un4seen.com/files/bass%s-linux.zip', [
                \str_replace('.', '', $version->toString()),
            ])
                ->extract('x64/libbass.so', $binary);
        }

        $this->expectNotToPerformAssertions();
        $headers = (string)Bass::create(Platform::LINUX, $version);

        \FFI::cdef($headers, $binary);
    }

    /**
     * @requires OSFAMILY Windows
     * @dataProvider versionsDataProvider
     */
    public function testWindowsBinaryCompatibility(Version $version): void
    {
        $binary = __DIR__ . '/storage/bass-' . $version->toString() . '.dll';

        if (!\is_file($binary)) {
            Downloader::download('https://www.un4seen.com/files/bass%s.zip', [
                \str_replace('.', '', $version->toString()),
            ])
                ->whenExists('x64/bass.dll',
                    static fn (DownloaderResult $ctx) => $ctx->extract('x64/bass.dll', $binary),
                    static fn (DownloaderResult $ctx) => $ctx->extract('bass.dll', $binary),
                );
        }

        $this->expectNotToPerformAssertions();
        $headers = (string)Bass::create(Platform::WINDOWS, $version);

        \FFI::cdef($headers, $binary);
    }

    /**
     * @requires OSFAMILY Darwin
     * @dataProvider versionsDataProvider
     */
    public function testDarwinBinaryCompatibility(Version $version): void
    {
        if (!$version->supportedOn(Platform::DARWIN)) {
            $this->markTestSkipped('OSX not supported by version ' . $version->toString());
        }

        $binary = __DIR__ . '/storage/libbass-' . $version->toString() . '.dylib';

        if (!\is_file($binary)) {
            Downloader::download('https://www.un4seen.com/files/bass%s-osx.zip', [
                \str_replace('.', '', $version->toString()),
            ])
                ->extract('libbass.dylib', $binary);
        }

        $this->expectNotToPerformAssertions();
        $headers = (string)Bass::create(Platform::DARWIN, $version);

        \FFI::cdef($headers, $binary);
    }
}
