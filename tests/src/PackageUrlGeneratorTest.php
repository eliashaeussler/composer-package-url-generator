<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests;

use Composer\Composer;
use Composer\Factory;
use Composer\IO;
use Composer\Package;
use Composer\Semver;
use EliasHaeussler\ComposerPackageUrlGenerator as Src;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * PackageUrlGeneratorTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\PackageUrlGenerator::class)]
final class PackageUrlGeneratorTest extends Framework\TestCase
{
    private Composer $composer;
    private Src\PackageUrlGenerator $subject;

    protected function setUp(): void
    {
        $this->composer = self::getComposer();
        $this->subject = Src\PackageUrlGenerator::create($this->composer);
    }

    // @todo Add test case for sorting of generators

    #[Framework\Attributes\Test]
    public function forPackageThrowsExceptionIfGivenPackageIsNotInstalled(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\PackageIsNotInstalled('foo/baz'),
        );

        $this->subject->forPackage('foo/baz');
    }

    #[Framework\Attributes\Test]
    public function forPackageThrowsExceptionIfNoSuitableGeneratorIsAvailable(): void
    {
        $subject = new Src\PackageUrlGenerator($this->composer, []);

        $this->expectExceptionObject(
            new Src\Exception\NoSupportedUrlGeneratorFound('phpunit/phpunit'),
        );

        $subject->forPackage('phpunit/phpunit');
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('forPackageReturnsPackageUrlsForGivenPackageDataProvider')]
    public function forPackageReturnsPackageUrlsForGivenPackage(string|Package\PackageInterface $package): void
    {
        $actual = $this->subject->forPackage($package);

        self::assertSame('phpunit/phpunit', $actual->package->getName());
        self::assertEquals(
            new Psr7\Uri('https://github.com/sebastianbergmann/phpunit'),
            $actual->sourceUrl,
        );
        self::assertEquals(
            new Psr7\Uri('https://phpunit.de/'),
            $actual->homepageUrl,
        );
    }

    /**
     * @return Generator<string, array{string|Package\PackageInterface}>
     */
    public static function forPackageReturnsPackageUrlsForGivenPackageDataProvider(): Generator
    {
        $packageName = 'phpunit/phpunit';
        $package = self::getComposer()->getRepositoryManager()->findPackage($packageName, new Semver\Constraint\MatchAllConstraint());

        self::assertInstanceOf(Package\PackageInterface::class, $package);

        yield 'package name' => [$packageName];
        yield 'package object' => [$package];
    }

    private static function getComposer(): Composer
    {
        return Factory::create(new IO\NullIO());
    }
}
