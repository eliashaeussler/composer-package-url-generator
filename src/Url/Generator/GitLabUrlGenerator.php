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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Url\Generator;

use Composer\Composer;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function preg_match;
use function sprintf;
use function str_replace;
use function str_starts_with;

/**
 * GitLabUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class GitLabUrlGenerator extends VcsUrlGenerator
{
    /**
     * @var non-empty-array<string>
     */
    private readonly array $domains;

    public function __construct(Composer $composer)
    {
        /* @phpstan-ignore assign.propertyType */
        $this->domains = $composer->getPackage()->getConfig()['gitlab-domains'] ?? ['gitlab.com'];
    }

    protected function isApiUrl(Message\UriInterface $sourceUrl): bool
    {
        return str_starts_with($sourceUrl->getPath(), '/api/v4/');
    }

    protected function extractSourceUrlFromApiUrl(Message\UriInterface $apiUrl): ?Message\UriInterface
    {
        $path = $apiUrl->getPath();

        if (1 !== preg_match('#/api/v4/projects/(?<namespace>[^/]+)/repository/archive\.zip$#', $path, $matches)) {
            return null;
        }

        // https://gitlab.com/api/v4/projects/foo%2Fbaz/repository/archive.zip?sha=b9a16c6d0bc4f591d631a6ceb3c320859ce811c2 => https://gitlab.com/foo/baz
        return new Psr7\Uri(
            sprintf(
                'https://%s/%s',
                $apiUrl->getHost(),
                str_replace('%2F', '/', $matches['namespace']),
            ),
        );
    }

    protected function getDomains(): array
    {
        return $this->domains;
    }
}
