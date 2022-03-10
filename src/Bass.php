<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers;

use FFI\Contracts\Headers\HeaderInterface;
use FFI\Contracts\Preprocessor\Exception\DirectiveDefinitionExceptionInterface;
use FFI\Contracts\Preprocessor\Exception\PreprocessorExceptionInterface;
use FFI\Contracts\Preprocessor\PreprocessorInterface;
use FFI\Headers\Bass\HeadersDownloader;
use FFI\Headers\Bass\Platform;
use FFI\Headers\Bass\Version;
use FFI\Contracts\Headers\VersionInterface;
use FFI\Preprocessor\Preprocessor;

class Bass implements HeaderInterface
{
    /**
     * @var non-empty-string
     */
    private const HEADERS_DIRECTORY = __DIR__ . '/../resources/headers';

    /**
     * @param PreprocessorInterface $pre
     * @param VersionInterface $version
     */
    public function __construct(
        public readonly PreprocessorInterface $pre,
        public readonly VersionInterface $version = Version::LATEST,
    ) {
        if (!$this->exists()) {
            HeadersDownloader::download($this->version, self::HEADERS_DIRECTORY);

            if (!$this->exists()) {
                throw new \RuntimeException('Could not initialize (download) header files');
            }
        }
    }

    /**
     * @return bool
     */
    private function exists(): bool
    {
        return \is_file($this->getHeaderPathname());
    }

    /**
     * @return non-empty-string
     */
    public function getHeaderPathname(): string
    {
        return self::HEADERS_DIRECTORY . '/' . $this->version->toString() . '/bass.h';
    }

    /**
     * @param Platform|null $platform
     * @param VersionInterface|non-empty-string $version
     * @param PreprocessorInterface $pre
     * @return self
     * @throws DirectiveDefinitionExceptionInterface
     */
    public static function create(
        Platform $platform = null,
        VersionInterface|string $version = Version::LATEST,
        PreprocessorInterface $pre = new Preprocessor(),
    ): self {
        $pre = clone $pre;

        $pre->add('wtypes.h', <<<'CPP'
            #define WINAPI
            #define CALLBACK
            #define __int64 long long

            typedef unsigned char BYTE;
            typedef unsigned short WORD;
            typedef unsigned char BOOL;
            typedef unsigned long DWORD;
            typedef struct HWND__{ int i; } *HWND;

            typedef struct _GUID {
              unsigned long  Data1;
              unsigned short Data2;
              unsigned short Data3;
              unsigned char  Data4[8];
            } GUID;
        CPP);

        $pre->add('stdint.h', '');

        if ($platform === Platform::WINDOWS) {
            $pre->define('_WIN32', '1');
        }

        if (!$version instanceof VersionInterface) {
            $version = Version::create($version);
        }

        if ($platform?->supportedBy($version) === false) {
            /** @psalm-suppress NullPropertyFetch */
            $message = \vsprintf('Platform [%s] not supported by version [%s]', [
                $platform->name,
                $version->toString(),
            ]);

            throw new \InvalidArgumentException($message);
        }

        return new self($pre, $version);
    }

    /**
     * @return non-empty-string
     * @throws PreprocessorExceptionInterface
     */
    public function __toString(): string
    {
        return $this->pre->process(new \SplFileInfo($this->getHeaderPathname())) . \PHP_EOL;
    }
}
