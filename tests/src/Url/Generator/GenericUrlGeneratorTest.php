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
 * GenericUrlGeneratorTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Url\Generator\GenericUrlGenerator::class)]
final class GenericUrlGeneratorTest extends Framework\TestCase
{
    private Src\Url\Generator\GenericUrlGenerator $subject;
    private Package\CompletePackage $package;

    public function setUp(): void
    {
        $this->subject = new Src\Url\Generator\GenericUrlGenerator();
        $this->package = new Package\CompletePackage('foo/baz', '1.0.0', '1.0.0');
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlThrowsExceptionIfNoSourceUrlsAreAvailable(): void
    {
        $this->expectExceptionObject(new Src\Exception\NoSourceUrlAvailable('foo/baz'));

        $this->subject->generateSourceUrl($this->package);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlReturnsSourceUrlFromPackage(): void
    {
        $this->package->setSourceUrl('https://my.git.com/foo/baz.git');

        self::assertEquals(
            new Psr7\Uri('https://my.git.com/foo/baz'),
            $this->subject->generateSourceUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsNullIfPackageIsNotSupported(): void
    {
        $package = new Package\Package('foo/baz', '1.0.0', '1.0.0');

        self::assertNull($this->subject->generateHomepageUrl($package));
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsNullIfNoHomepageUrlIsAvailable(): void
    {
        self::assertNull($this->subject->generateHomepageUrl($this->package));
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlReturnsHomepageUrlFromPackage(): void
    {
        $this->package->setHomepage('https://example.com');

        self::assertEquals(
            new Psr7\Uri('https://example.com'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }
}
