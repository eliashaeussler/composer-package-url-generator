<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023-2026 Elias Häußler <elias@haeussler.dev>
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
use EliasHaeussler\ComposerPackageUrlGenerator\Tests;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * VcsUrlGeneratorTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Url\Generator\VcsUrlGenerator::class)]
final class VcsUrlGeneratorTest extends Framework\TestCase
{
    use Tests\ComposerTrait;

    private Tests\Fixtures\DummyVcsUrlGenerator $subject;
    private Package\CompletePackage $package;

    public function setUp(): void
    {
        $this->subject = new Tests\Fixtures\DummyVcsUrlGenerator();
        $this->package = new Package\RootPackage('foo/baz', '1.0.0', '1.0.0');
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
        $this->package->setSourceUrl('https://www.example.com');

        $this->subject->isApiUrl = true;
        $this->subject->extractedApiUrl = null;

        $this->expectExceptionObject(new Src\Exception\NoSourceUrlAvailable('foo/baz'));

        $this->subject->generateSourceUrl($this->package);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlReturnsSourceUrlFromPackage(): void
    {
        $this->package->setSourceUrl('https://any-vcs.com/foo/baz.git');

        self::assertEquals(
            new Psr7\Uri('https://any-vcs.com/foo/baz'),
            $this->subject->generateSourceUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://www.example.com');

        $this->subject->isApiUrl = true;
        $this->subject->extractedApiUrl = new Psr7\Uri('https://any-vcs.com/foo/baz');

        self::assertEquals(
            new Psr7\Uri('https://any-vcs.com/foo/baz'),
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
        $this->package->setDistUrl('https://www.example.com');

        $this->subject->isApiUrl = true;
        $this->subject->extractedApiUrl = null;

        self::assertNull($this->subject->generateHomepageUrl($this->package));
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsSourceUrlFromPackage(): void
    {
        $this->package->setSourceUrl('https://any-vcs.com/foo/baz.git');

        self::assertEquals(
            new Psr7\Uri('https://any-vcs.com/foo/baz'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://www.example.com');

        $this->subject->isApiUrl = true;
        $this->subject->extractedApiUrl = new Psr7\Uri('https://any-vcs.com/foo/baz');

        self::assertEquals(
            new Psr7\Uri('https://any-vcs.com/foo/baz'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsFalseIfNeitherSourceUrlsNorDistUrlsContainVcsDomains(): void
    {
        self::assertFalse($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnySourceUrlContainsConfiguredDomain(): void
    {
        $this->subject->domains = ['any-vcs.com'];

        $this->package->setSourceUrl('https://any-vcs.com/foo/baz.git');

        self::assertTrue($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnyDistUrlContainsConfiguredDomain(): void
    {
        $this->subject->domains = ['any-vcs.com'];

        $this->package->setDistUrl('https://any-vcs.com/foo/baz.git');

        self::assertTrue($this->subject->supports($this->package));
    }
}
