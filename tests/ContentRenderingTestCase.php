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

final class ContentRenderingTestCase extends TestCase
{
    /**
     * @testdox Testing that the headers are successfully built
     *
     * @dataProvider configDataProvider
     */
    public function testRenderable(Platform $platform, Version $version): void
    {
        if (!$platform->supportedBy($version)) {
            $this->expectExceptionMessage('not supported by version');
        } else {
            $this->expectNotToPerformAssertions();
        }

        (string)Bass::create($platform, $version);
    }

    /**
     * @testdox Testing that headers contain correct syntax
     *
     * @dataProvider configDataProvider
     */
    public function testCompilation(Platform $platform, Version $version): void
    {
        if (!$platform->supportedBy($version)) {
            $this->expectExceptionMessage('not supported by version');
        }

        $this->assertHeadersSyntaxValid(
            Bass::create($platform, $version)
        );
    }
}
