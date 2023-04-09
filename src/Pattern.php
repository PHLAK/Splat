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

    /** @var string The directory separator */
    protected static $directorySeparator = DIRECTORY_SEPARATOR;

    /** @var array<int, string> Memoization cache */
    private $cache = [];

    /** Create a new object. */
    public function __construct(
        private string $pattern
    ) {
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

        $length = strlen($this->pattern);
        $pattern = '';
        $inCharacterGroup = false;
        $patternGroup = 0;
        $lookaheadGroup = 0;

        // note: in PHP strings, '\\' means one backslash, '\\\\' means two backslashes

        for ($i = 0; $i < $length; ++$i) {
            $char = $this->pattern[$i];

            switch ($char) {
                case '\\':
                    if (isset($this->pattern[$i + 1])) {
                        // the "\" escapes the next character
                        $pattern .= '\\' . $this->pattern[++$i];
                    } else {
                        // the "\" is the last character of the pattern, which is erroneous, let's discard it
                        // rather than letting it unexpectedly escape a character appended after the loop

                        // previous code would trigger an "undefined array key" PHP warning,
                        // and unexpectedly escape the character appended after the loop

                        // let's make the erroneous array access, to trigger the PHP warning as the previous code does
                        $this->pattern[$i + 1]; // @phpstan-ignore-line

                        // TODO: as we are able to detect this mistake,
                        // consider throwing a custom exception in future Splat versions
                    }

                    continue 2;

                case '#': // pattern delimiter
                    $pattern .= '\\#';

                    continue 2;
            }

            if ($inCharacterGroup) {
                switch ($char) {
                    case ']':
                        $pattern .= $char;
                        $inCharacterGroup = false;

                        break;

                    default:
                        $pattern .= $char;

                        break;
                }
            } else {
                switch ($char) {
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

                    case '[':
                        $pattern .= $char;
                        $inCharacterGroup = true;

                        // "]" does not need to be escaped when it is the first character
                        // of the class (optionally preceded by the negation "^"), e.g. []abc] or [^]abc]
                        // TODO: add unit tests for this
                        if (isset($this->pattern[$i + 1]) && $this->pattern[$i + 1] === '^') {
                            $pattern .= '^';
                            ++$i;
                        }
                        if (isset($this->pattern[$i + 1]) && $this->pattern[$i + 1] === ']') {
                            $pattern .= '\\]';
                            ++$i;
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
                        if (in_array($char, ['.', '|', '+', '^', '$'])) {
                            $pattern .= '\\' . $char;
                        } else {
                            $pattern .= $char;
                        }

                        break;
                }
            }
        }

        /*
        if ($inCharacterGroup || $patternGroup > 0 || $lookaheadGroup > 0) {
            // error: unclosed group,
            // which will trigger a "preg_match(): compilation failed" PHP warning later

            // TODO: as we are able to detect these mistakes,
            // consider throwing a custom exception in future Splat versions
        }
        */

        if ($options & self::START_ANCHOR) {
            $pattern = '^' . $pattern;
        }

        if ($options & self::END_ANCHOR) {
            $pattern = $pattern . '$';
        }

        return $this->cache[$options] = sprintf('#%s#', $pattern);
    }
}
