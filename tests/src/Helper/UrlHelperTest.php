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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests\Helper;

use EliasHaeussler\ComposerPackageUrlGenerator as Src;
use PHPUnit\Framework;

/**
 * UrlHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\UrlHelper::class)]
final class UrlHelperTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function normalizeUrlRemovesGitSuffix(): void
    {
        $actual = Src\Helper\UrlHelper::normalizeUrl('https://gitlab.example.com/foo/bar.git');

        self::assertStringEndsNotWith('.git', (string) $actual);
    }

    #[Framework\Attributes\Test]
    public function normalizeUrlConvertsGitStyleSyntaxToHttpsSyntax(): void
    {
        $actual = Src\Helper\UrlHelper::normalizeUrl('git@gitlab.example.com:foo/bar.git');

        self::assertSame('https://gitlab.example.com/foo/bar', (string) $actual);
    }

    #[Framework\Attributes\Test]
    public function normalizeUrlThrowsExceptionIfUrlIsSeriouslyMalformed(): void
    {
        $this->expectExceptionObject(new Src\Exception\UrlIsMalformed('foo://example.com:baz'));

        Src\Helper\UrlHelper::normalizeUrl('foo://example.com:baz');
    }

    #[Framework\Attributes\Test]
    public function normalizeUrlEnforcesHttpsSchemeForUrlsWithoutScheme(): void
    {
        $actual = Src\Helper\UrlHelper::normalizeUrl('//gitlab.example.com/foo/bar.git');

        self::assertSame('https://gitlab.example.com/foo/bar', (string) $actual);
    }

    #[Framework\Attributes\Test]
    public function normalizeUrlEnforcesHttpsSchemeForUrlsWithSshScheme(): void
    {
        $actual = Src\Helper\UrlHelper::normalizeUrl('ssh://gitlab.example.com/foo/bar.git');

        self::assertSame('https://gitlab.example.com/foo/bar', (string) $actual);
    }

    #[Framework\Attributes\Test]
    public function normalizeUrlRemovesUserInfoAndPort(): void
    {
        $actual = Src\Helper\UrlHelper::normalizeUrl('ssh://git@gitlab.example.com:24978/foo/bar.git');

        self::assertSame('https://gitlab.example.com/foo/bar', (string) $actual);
    }
}
