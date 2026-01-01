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
 * GitLabUrlGeneratorTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Url\Generator\GitLabUrlGenerator::class)]
final class GitLabUrlGeneratorTest extends Framework\TestCase
{
    use Tests\ComposerTrait;

    private Package\CompletePackage $package;
    private Src\Url\Generator\GitLabUrlGenerator $subject;

    public function setUp(): void
    {
        $this->package = new Package\RootPackage('foo/baz', '1.0.0', '1.0.0');
        $this->package->setConfig([
            'gitlab-domains' => [
                'gitlab.com',
                'gitlab.example.com',
            ],
        ]);

        $composer = self::getComposer();
        $composer->setPackage($this->package);

        $this->subject = new Src\Url\Generator\GitLabUrlGenerator($composer);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlThrowsExceptionIfNoSupportedSourceUrlsAndDistUrlsAreAvailable(): void
    {
        $this->package->setDistUrl('https://gitlab.com/api/v4/foo');

        $this->expectExceptionObject(new Src\Exception\NoSourceUrlAvailable('foo/baz'));

        $this->subject->generateSourceUrl($this->package);
    }

    #[Framework\Attributes\Test]
    public function generateSourceUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://gitlab.com/api/v4/projects/foo%2Fbaz/repository/archive.zip?sha=b9a16c6d0bc4f591d631a6ceb3c320859ce811c2');

        self::assertEquals(
            new Psr7\Uri('https://gitlab.com/foo/baz'),
            $this->subject->generateSourceUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlSkipsUnsupportedApiUrl(): void
    {
        $this->package->setDistUrl('https://gitlab.com/api/v4/foo');

        self::assertNull($this->subject->generateHomepageUrl($this->package));
    }

    #[Framework\Attributes\Test]
    public function generateHomepageUrlExtractsSourceUrlFromApiUrl(): void
    {
        $this->package->setDistUrl('https://gitlab.com/api/v4/projects/foo%2Fbaz/repository/archive.zip?sha=b9a16c6d0bc4f591d631a6ceb3c320859ce811c2');

        self::assertEquals(
            new Psr7\Uri('https://gitlab.com/foo/baz'),
            $this->subject->generateHomepageUrl($this->package),
        );
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnySourceUrlContainsDefaultGitLabUrl(): void
    {
        $this->package->setSourceUrl('https://gitlab.com/foo/baz.git');

        self::assertTrue($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnySourceUrlContainsConfiguredGitLabUrl(): void
    {
        $this->package->setSourceUrl('https://gitlab.example.com/foo/baz.git');

        self::assertTrue($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnyDistUrlContainsDefaultGitLabUrl(): void
    {
        $this->package->setDistUrl('https://gitlab.com/api/v4/projects/foo%2Fbaz/repository/archive.zip?sha=b9a16c6d0bc4f591d631a6ceb3c320859ce811c2');

        self::assertTrue($this->subject->supports($this->package));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueIfAnyDistUrlContainsConfiguredGitLabUrl(): void
    {
        $this->package->setDistUrl('https://gitlab.example.com/api/v4/projects/foo%2Fbaz/repository/archive.zip?sha=b9a16c6d0bc4f591d631a6ceb3c320859ce811c2');

        self::assertTrue($this->subject->supports($this->package));
    }
}
