<?php

namespace PHLAK\Utilities;

class Glob
{
    /** @const Do not add start or end anchors */
    public const NO_ANCHORS = 0;

    /** @const Add start anchor (i.e. '/^.../') */
    public const START_ANCHOR = 1;

    /** @const Add end anchor (i.e. '/...$/') */
    public const END_ANCHOR = 2;

    /** @const Add start and end anchors (i.e. '/^...$/') */
    public const BOTH_ANCHORS = self::START_ANCHOR | self::END_ANCHOR;

    /** @var string The glob pattern */
    protected $pattern;

    /** @var string The directory separator */
    protected static $directorySeparator = DIRECTORY_SEPARATOR;

    /** Create a new object. */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /** Create a new object from a glob pattern. */
    public static function pattern(string $pattern): self
    {
        return new static($pattern);
    }

    /** Set the directory separator string. */
    public static function directorySeparator(string $separator): void
    {
        self::$directorySeparator = $separator;
    }

    /** Escape glob pattern characters from a string */
    public static function escape(string $string): string
    {
        return str_replace([
            '\\', '?', '*', '[', ']', '^', '{', '}', ','
        ], [
            '\\\\', '\\?', '\\*', '\\[', '\\]', '\\^', '\\{', '\\}', '\\,'
        ], $string);
    }

    /** Test if a string matches the glob pattern. */
    public function match(string $string): bool
    {
        return (bool) preg_match($this->toRegex(), $string);
    }

    /** Test if a string starts with the glob pattern. */
    public function matchStart(string $string): bool
    {
        return (bool) preg_match($this->toRegex(self::START_ANCHOR), $string);
    }

    /** Test if a string ends with the glob pattern. */
    public function matchEnd(string $string): bool
    {
        return (bool) preg_match($this->toRegex(self::END_ANCHOR), $string);
    }

    /** Test if any part of a string matches the glob pattern. */
    public function matchWithin(string $string): bool
    {
        return (bool) preg_match($this->toRegex(self::NO_ANCHORS), $string);
    }

    /** Convert the glob a regular expression pattern. */
    public function toRegex(int $options = self::BOTH_ANCHORS): string
    {
        $pattern = '';
        $characterGroup = 0;
        $patternGroup = 0;

        for ($i = 0; $i < strlen($this->pattern); ++$i) {
            $char = $this->pattern[$i];

            switch ($char) {
                case '\\':
                    $pattern .= '\\' . $this->pattern[++$i];
                    break;

                case '?':
                    $pattern .= '.';
                    break;

                case '*':
                    if (isset($this->pattern[$i + 1]) && $this->pattern[$i + 1] === '*') {
                        $pattern .= '.*';
                        ++$i;
                    } else {
                        $pattern .= sprintf('[^%s]*', addslashes(self::$directorySeparator));
                    }
                    break;

                case '#':
                    $pattern .= '\#';
                    break;

                case '[':
                    $pattern .= $char;
                    ++$characterGroup;
                    break;

                case ']':
                    if ($characterGroup > 0) {
                        --$characterGroup;
                    }

                    $pattern .= $char;
                    break;

                case '^':
                    if ($characterGroup > 0) {
                        $pattern .= $char;
                    } else {
                        $pattern .= '\\' . $char;
                    }
                    break;

                case '{':
                    $pattern .= '(';
                    ++$patternGroup;
                    break;

                case '}':
                    if ($patternGroup > 0) {
                        $pattern .= ')';
                        --$patternGroup;
                    } else {
                        $pattern .= $char;
                    }
                    break;

                case ',':
                    if ($patternGroup > 0) {
                        $pattern .= '|';
                    } else {
                        $pattern .= $char;
                    }
                    break;

                default:
                    if (in_array($char, ['.', '(', ')', '|', '+', '$'])) {
                        $pattern .= '\\' . $char;
                    } else {
                        $pattern .= $char;
                    }
                    break;
            }
        }

        if ($options & self::START_ANCHOR) {
            $pattern = '^' . $pattern;
        }

        if ($options & self::END_ANCHOR) {
            $pattern = $pattern . '$';
        }

        return sprintf('#%s#', $pattern);
    }

    /** Return the glob pattern as a string. */
    public function __toString(): string
    {
        return $this->pattern;
    }
}
