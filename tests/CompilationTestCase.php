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
use FFI\Headers\Bass\Version;

/**
 * @requires extension ffi
 */
class CompilationTestCase extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        if (!Runtime::isAvailable()) {
            $this->markTestSkipped('An ext-ffi extension must be available and enabled');
        }

        parent::setUp();
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testCompilation(Platform $platform, Version $version): void
    {
        if (!$platform->supportedBy($version)) {
            $this->expectExceptionMessage('not supported by version');
        }

        $headers = (string)Bass::create($platform, $version);

        try {
            \FFI::cdef($headers);
        } catch (\FFI\Exception $e) {
            $this->assertStringStartsWith('Failed resolving C function', $e->getMessage());
        }
    }
}
