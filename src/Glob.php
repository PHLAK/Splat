<?php

namespace PHLAK\Splat;

use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Glob
{
    /**
     * Get a list of files in a directory by a glob pattern.
     *
     * @param string|Pattern $pattern
     *
     * @throws DirectoryNotFoundException
     */
    public static function in($pattern, string $path): Finder
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return Finder::create()->in($path)->filter(function (SplFileInfo $file) use ($pattern): bool {
            return self::match($pattern, $file->getRelativePathname());
        });
    }

    /**
     * Test if a string matches the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function match($pattern, string $string): bool
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return (bool) preg_match($pattern->toRegex(), $string);
    }

    /**
     * Test if a string starts with the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function matchStart($pattern, string $string): bool
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return (bool) preg_match($pattern->toRegex(Pattern::START_ANCHOR), $string);
    }

    /**
     * Test if a string ends with the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function matchEnd($pattern, string $string): bool
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return (bool) preg_match($pattern->toRegex(Pattern::END_ANCHOR), $string);
    }

    /**
     * Test if any part of a string matches the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function matchWithin($pattern, string $string): bool
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return (bool) preg_match($pattern->toRegex(Pattern::NO_ANCHORS), $string);
    }

    /**
     * Filter an array of strings to values matching the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function filter($pattern, array $array): array
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return array_filter($array, function (string $string) use ($pattern): bool {
            return self::match($pattern, $string);
        });
    }

    /**
     * Filter an array of strings to values not matching the glob pattern.
     *
     * @param string|Pattern $pattern
     */
    public static function reject($pattern, array $array): array
    {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return array_filter($array, function (string $string) use ($pattern): bool {
            return ! self::match($pattern, $string);
        });
    }
}
