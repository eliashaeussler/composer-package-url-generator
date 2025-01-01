<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\ComposerPackageUrlGenerator;

use Composer\Composer;
use Composer\Package;
use Composer\Semver;

use function usort;

/**
 * PackageUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class PackageUrlGenerator
{
    /**
     * @var list<Url\Generator\UrlGenerator>
     */
    private readonly array $generators;

    /**
     * @var array<string, Package\PackageInterface>
     */
    private array $packageMap = [];

    /**
     * @param list<Url\Generator\UrlGenerator> $generators
     */
    public function __construct(
        private readonly Composer $composer,
        array $generators,
    ) {
        usort($generators, self::sortGenerators(...));

        $this->generators = $generators;
    }

    public static function create(Composer $composer): self
    {
        $urlGenerators = [
            new Url\Generator\GenericUrlGenerator(),
        ];

        return new self($composer, $urlGenerators);
    }

    /**
     * @throws Exception\NoSupportedUrlGeneratorFound
     * @throws Exception\PackageIsNotInstalled
     */
    public function forPackage(string|Package\PackageInterface $package): Url\PackageUrls
    {
        $generator = $this->findGeneratorForPackage($package);
        $package = $this->findPackage($package);

        return new Url\PackageUrls(
            $package,
            $generator->generateSourceUrl($package),
            $generator->generateHomepageUrl($package),
        );
    }

    /**
     * @throws Exception\NoSupportedUrlGeneratorFound
     * @throws Exception\PackageIsNotInstalled
     */
    public function findGeneratorForPackage(string|Package\PackageInterface $package): Url\Generator\UrlGenerator
    {
        $package = $this->findPackage($package);

        foreach ($this->generators as $generator) {
            if ($generator->supports($package)) {
                return $generator;
            }
        }

        throw new Exception\NoSupportedUrlGeneratorFound($package->getName());
    }

    /**
     * @throws Exception\PackageIsNotInstalled
     */
    private function findPackage(string|Package\PackageInterface $package): Package\PackageInterface
    {
        if ($package instanceof Package\PackageInterface) {
            return $package;
        }

        if (!isset($this->packageMap[$package])) {
            $this->packageMap[$package] = $this->composer->getRepositoryManager()->findPackage(
                $package,
                new Semver\Constraint\MatchAllConstraint(),
            ) ?? throw new Exception\PackageIsNotInstalled($package);
        }

        return $this->packageMap[$package];
    }

    private static function sortGenerators(Url\Generator\UrlGenerator $a, Url\Generator\UrlGenerator $b): int
    {
        return ($a::getPriority() <=> $b::getPriority()) * -1;
    }
}
