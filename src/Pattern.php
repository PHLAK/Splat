<?php

namespace PHLAK\Splat;

class Pattern
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

    /** @var array Memoization cache */
    private $cache = [];

    /** Create a new object. */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /** Return the pattern as a string. */
    public function __toString(): string
    {
        return $this->pattern;
    }

    /** Create a new object statically. */
    public static function make(string $pattern): self
    {
        return new self($pattern);
    }

    /** Set the directory separator string. */
    public static function directorySeparator(string $separator): void
    {
        static::$directorySeparator = $separator;
    }

    /** Escape glob pattern characters from a string. */
    public static function escape(string $string): string
    {
        return str_replace([
            '\\', '?', '*', '(', ')',  '[', ']', '^', '{', '}', ',',
        ], [
            '\\\\', '\\?', '\\*', '\\(', '\\)',  '\\[', '\\]', '\\^', '\\{', '\\}', '\\,',
        ], $string);
    }

    /** Convert the pattern a regular expression. */
    public function toRegex(int $options = self::BOTH_ANCHORS): string
    {
        if (isset($this->cache[$options])) {
            return $this->cache[$options];
        }

        $pattern = '';
        $characterGroup = 0;
        $lookaheadGroup = 0;
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
                        $pattern .= sprintf('[^%s]*', addslashes(static::$directorySeparator));
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

                case '(':
                    if (isset($this->pattern[$i + 1]) && in_array($this->pattern[$i + 1], ['=', '!'])) {
                        $pattern .= sprintf('(?%s', $this->pattern[++$i]);
                        ++$lookaheadGroup;
                    } else {
                        $pattern .= '\\' . $char;
                    }

                    break;

                case ')':
                    if ($lookaheadGroup > 0) {
                        --$lookaheadGroup;
                        $pattern .= $char;
                    } else {
                        $pattern .= '\\' . $char;
                    }

                    break;

                default:
                    if (in_array($char, ['.', '|', '+', '$'])) {
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

        return $this->cache[$options] = sprintf('#%s#', $pattern);
    }
}
