<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass\Tests;

use FFI\Headers\Bass;
use FFI\Headers\Bass\Platform;
use FFI\Headers\Bass\Version;
use FFI\Headers\Testing\Downloader;

class BinaryCompatibilityTestCase extends TestCase
{
    private function skipIfPlatformNotSupported(Version $version, Platform $platform): void
    {
        if (!$version->supportedOn($platform)) {
            $this->markTestSkipped($platform->name . ' not supported by version ' . $version->toString());
        }
    }

    /**
     * @requires OSFAMILY Linux
     * @dataProvider versionsDataProvider
     */
    public function testLinuxBinaryCompatibility(Version $version): void
    {
        $this->skipIfPlatformNotSupported($version, Platform::LINUX);

        if (!\is_file($binary = __DIR__ . '/storage/libbass-' . $version->toString() . '.so')) {
            Downloader::zip('https://www.un4seen.com/files/bass%s-linux.zip', [
                \str_replace('.', '', $version->toString()),
            ])
                ->extract('x64/libbass.so', $binary);
        }

        $this->assertHeadersCompatibleWith(Bass::create(Platform::LINUX, $version), $binary);
    }

    /**
     * @requires OSFAMILY Windows
     * @dataProvider versionsDataProvider
     */
    public function testWindowsBinaryCompatibility(Version $version): void
    {
        if (!\is_file($binary = __DIR__ . '/storage/bass-' . $version->toString() . '.dll')) {
            $result = Downloader::zip('https://www.un4seen.com/files/bass%s.zip', [
                \str_replace('.', '', $version->toString()),
            ]);

            // x64 and >= 2.4
            if (\PHP_INT_SIZE === 8 && $result->exists('x64/bass.dll')) {
                $result->extract('x64/bass.dll', $binary);
            // x86 and < 2.4
            } elseif (\PHP_INT_SIZE === 4 && $result->exists('bass.dll')) {
                $result->extract('bass.dll', $binary);
            } else {
                $this->markTestSkipped('Incompatible OS bits');
            }
        }

        $this->assertHeadersCompatibleWith(Bass::create(Platform::WINDOWS, $version), $binary);
    }

    /**
     * @requires OSFAMILY Darwin
     * @dataProvider versionsDataProvider
     */
    public function testDarwinBinaryCompatibility(Version $version): void
    {
        $this->skipIfPlatformNotSupported($version, Platform::DARWIN);

        if (!\is_file($binary = __DIR__ . '/storage/libbass-' . $version->toString() . '.dylib')) {
            $result = Downloader::zip('https://www.un4seen.com/files/bass%s-osx.zip', [
                \str_replace('.', '', $version->toString()),
            ]);

            // x64 and >= 2.4
            if (\PHP_INT_SIZE === 8 && \version_compare($version->toString(), '2.4', '>=')) {
                $result->extract('libbass.dylib', $binary);
            // x86 and < 2.4
            } elseif (\PHP_INT_SIZE === 4 && \version_compare($version->toString(), '2.4', '<')) {
                $result->extract('libbass.dylib', $binary);
            } else {
                $this->markTestSkipped('Incompatible OS bits');
            }
        }

        $this->assertHeadersCompatibleWith(Bass::create(Platform::DARWIN, $version), $binary);
    }
}
