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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests\Exception;

use EliasHaeussler\ComposerPackageUrlGenerator as Src;
use PHPUnit\Framework;

/**
 * NoSourceUrlAvailableTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\NoSourceUrlAvailable::class)]
final class NoSourceUrlAvailableTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForUnavailableSourceUrl(): void
    {
        $actual = new Src\Exception\NoSourceUrlAvailable('foo/baz');

        self::assertSame('The package "foo/baz" does not provide a source url.', $actual->getMessage());
        self::assertSame(1701800466, $actual->getCode());
    }
}
