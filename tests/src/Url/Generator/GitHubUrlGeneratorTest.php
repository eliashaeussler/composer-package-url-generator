<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests\Url\Generator;

use Composer\Package;
use EliasHaeussler\ComposerPackageUrlGenerator as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * GitHubUrlGeneratorTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Url\Generator\GitHubUrlGenerator::class)]
final class GitHubUrlGeneratorTest extends Framework\TestCase
{
    private Src\Url\Generator\GitHubUrlGenerator $subject;
    private Package\CompletePackage $package;

    public function setUp(): void
    {
        $this->subject = new Src\Url\Generator\GitHubUrlGenerator();
        $this->package = new Package\CompletePackage('foo/baz', '1.0.0', '1.0.0');
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlThrowsExceptionIfNoSourceUrlsAndDistUrlsAreAvailable(): void
    {
        $this->expectExceptionObject(new Src\Exception\NoSourceUrlAvailable('foo/baz'));

        $this->subject->generateSourceUrl($this->package);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlThrowsExceptionIfNoSupportedSourceUrlsAndDistUrlsAreAvailable(): void
    {
        $this->package->setDistUrl('https://api.github.com/foo');

        $this->expectExceptionObject(new Src\Exception\NoSourceUrlAvailable('foo/baz'));

        $this->subject->generateSourceUrl($this->package);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlReturnsSourceUrlFromPackage(): void
    {
        $this->package->setSourceUrl('https://github.com/foo/baz.git');

        self::assertEquals(
            new Psr7\Uri('https://github.com/foo/baz'),
            $this->subject->generateSourceUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://api.github.com/repos/foo/baz/zipball/a70f5c95fb43bc83f07c9c948baa0dc1829bf201');

        self::assertEquals(
            new Psr7\Uri('https://github.com/foo/baz'),
            $this->subject->generateSourceUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsHomepageFromPackage(): void
    {
        $this->package->setHomepage('https://example.com');

        self::assertEquals(
            new Psr7\Uri('https://example.com'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlSkipsUnsupportedApiUrl(): void
    {
        $this->package->setDistUrl('https://api.github.com/foo');

        self::assertNull($this->subject->generateHomepageUrl($this->package));
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsSourceUrlFromPackage(): void
    {
        $this->package->setSourceUrl('https://github.com/foo/baz.git');

        self::assertEquals(
            new Psr7\Uri('https://github.com/foo/baz'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://api.github.com/repos/foo/baz/zipball/a70f5c95fb43bc83f07c9c948baa0dc1829bf201');

        self::assertEquals(
            new Psr7\Uri('https://github.com/foo/baz'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsFalseIfNeitherSourceUrlsNorDistUrlsContainsGitHubUrls(): void
    {
        self::assertFalse($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnySourceUrlContainsGitHubUrl(): void
    {
        $this->package->setSourceUrl('https://github.com/foo/baz.git');

        self::assertTrue($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnyDistUrlContainsGitHubUrl(): void
    {
        $this->package->setSourceUrl('https://api.github.com/repos/foo/baz/zipball/a70f5c95fb43bc83f07c9c948baa0dc1829bf201');

        self::assertTrue($this->subject->supports($this->package));
    }
}
