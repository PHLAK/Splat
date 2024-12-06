<?php

namespace PHLAK\Splat;

class Pattern
{
    /** @var string The directory separator */
    protected static $directorySeparator = DIRECTORY_SEPARATOR;

    /** @var array<string, string> Memoization cache */
    private $cache = [];

    /** Create a new object. */
    public function __construct(
        private string $pattern
    ) {}

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
            '\\', '?', '*', '(', ')',  '[', ']', '!', '{', '}', ',',
        ], [
            '\\\\', '\\?', '\\*', '\\(', '\\)',  '\\[', '\\]', '\\!', '\\{', '\\}', '\\,',
        ], $string);
    }

    /** Convert the pattern a regular expression. */
    public function toRegex(Anchors $anchors = Anchors::BOTH): string
    {
        if (isset($this->cache[$anchors->name])) {
            return $this->cache[$anchors->name];
        }

        $pattern = '';
        $characterGroup = false;
        $lookaheadGroup = 0;
        $patternGroup = 0;

        for ($i = 0; $i < strlen($this->pattern); ++$i) {
            $char = $this->pattern[$i];

            switch ($char) {
                case '\\':
                    $pattern .= $characterGroup ? '\\\\' : '\\' . $this->pattern[++$i];

                    break;

                case '?':
                    $pattern .= $characterGroup ? $char : '.';

                    break;

                case '*':
                    if ($characterGroup) {
                        $pattern .= $char;

                        break;
                    }

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

                case '^':
                    $pattern .= '\^';

                    break;

                case '[':
                    $pattern .= $char;
                    $characterGroup = true;

                    break;

                case ']':
                    if ($characterGroup) {
                        $characterGroup = false;
                    }

                    $pattern .= $char;

                    break;

                case '!':
                    $pattern .= $characterGroup ? '^' : $char;

                    break;

                case '{':
                    $pattern .= '(';
                    ++$patternGroup;

                    break;

                case '}':
                    if ($patternGroup > 0) {
                        $pattern .= $characterGroup ? $char : ')';
                        --$patternGroup;
                    } else {
                        $pattern .= $char;
                    }

                    break;

                case ',':
                    if ($patternGroup > 0) {
                        $pattern .= $characterGroup ? $char : '|';
                    } else {
                        $pattern .= $char;
                    }

                    break;

                case '(':
                    if (isset($this->pattern[$i + 1]) && in_array($this->pattern[$i + 1], ['=', '!'])) {
                        $pattern .= sprintf('(?%s', $this->pattern[++$i]);
                        ++$lookaheadGroup;
                    } else {
                        $pattern .= $characterGroup ? $char : '\\' . $char;
                    }

                    break;

                case ')':
                    if ($lookaheadGroup > 0) {
                        --$lookaheadGroup;
                        $pattern .= $char;
                    } else {
                        $pattern .= $characterGroup ? $char : '\\' . $char;
                    }

                    break;

                default:
                    if (in_array($char, ['.', '|', '+', '$'])) {
                        $pattern .= $characterGroup ? $char : '\\' . $char;
                    } else {
                        $pattern .= $char;
                    }

                    break;
            }
        }

        if (in_array($anchors, [Anchors::BOTH, Anchors::START])) {
            $pattern = '^' . $pattern;
        }

        if (in_array($anchors, [Anchors::BOTH, Anchors::END])) {
            $pattern = $pattern . '$';
        }

        return $this->cache[$anchors->name] = sprintf('#%s#', $pattern);
    }
}
