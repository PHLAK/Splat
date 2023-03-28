<?php

namespace Tests;

use PHLAK\Splat\Pattern;

/** @covers \PHLAK\Splat\Pattern */
class PatternTest extends TestCase
{
    public function test_it_is_stringable(): void
    {
        $this->assertEquals('foo.txt', (string) Pattern::make('foo.txt'));
        $this->assertEquals('**.txt', (string) Pattern::make('**.txt'));
        $this->assertEquals('foo.{yml,yaml}', (string) Pattern::make('foo.{yml,yaml}'));
    }

    public function test_it_can_convert_a_litteral_string_to_a_regular_expression(): void
    {
        $this->assertEquals('#^foo$#', Pattern::make('foo')->toRegex());
        $this->assertEquals('#^foo/bar$#', Pattern::make('foo/bar')->toRegex());
        $this->assertEquals('#^foo/bar\.txt$#', Pattern::make('foo/bar.txt')->toRegex());
    }

    public function test_it_converts_glob_patterns_to_regular_expression_patterns(): void
    {
        $this->assertEquals('#^$#', Pattern::make('')->toRegex());
        $this->assertEquals('#^.$#', Pattern::make('?')->toRegex());
        $this->assertEquals('#^[^/]*$#', Pattern::make('*')->toRegex());
        $this->assertEquals('#^.*$#', Pattern::make('**')->toRegex());
        $this->assertEquals('#^\#$#', Pattern::make('#')->toRegex());
        $this->assertEquals('#^\\?$#', Pattern::make('\\?')->toRegex());
        $this->assertEquals('#^(?=)$#', Pattern::make('(=)')->toRegex());
        $this->assertEquals('#^(?!)$#', Pattern::make('(!)')->toRegex());
    }

    public function test_it_can_escape_glob_patterns_when_converting_to_regular_expressions(): void
    {
        $this->assertEquals('#^\\\\$#', Pattern::make('\\\\')->toRegex());
        $this->assertEquals('#^\\?$#', Pattern::make('\?')->toRegex());
        $this->assertEquals('#^\\*$#', Pattern::make('\*')->toRegex());
        $this->assertEquals('#^\\*\\*$#', Pattern::make('\*\*')->toRegex());
        $this->assertEquals('#^\\#$#', Pattern::make('\#')->toRegex());
    }

    public function test_it_can_convert_a_complex_glob_pattern_to_a_regular_expressions(): void
    {
        $this->assertEquals('#^foo\.txt$#', Pattern::make('foo.txt')->toRegex());
        $this->assertEquals('#^foo/bar\.txt$#', Pattern::make('foo/bar.txt')->toRegex());
        $this->assertEquals('#^foo\?bar\.txt$#', Pattern::make('foo\?bar.txt')->toRegex());
        $this->assertEquals('#^[^/]*\.txt$#', Pattern::make('*.txt')->toRegex());
        $this->assertEquals('#^.*/[^/]*\.txt$#', Pattern::make('**/*.txt')->toRegex());
        $this->assertEquals('#^([^/]*|.*/[^/]*)\.txt$#', Pattern::make('{*,**/*}.txt')->toRegex());
        $this->assertEquals('#^file\.(yml|yaml)$#', Pattern::make('file.{yml,yaml}')->toRegex());
        $this->assertEquals('#^[fbw]oo\.txt$#', Pattern::make('[fbw]oo.txt')->toRegex());
        $this->assertEquals('#^[^fbw]oo\.txt$#', Pattern::make('[^fbw]oo.txt')->toRegex());
        $this->assertEquals('#^[[?*\\\\]$#', Pattern::make('[[?*\]')->toRegex());
        $this->assertEquals('#^[.\\\\]$#', Pattern::make('[.\]')->toRegex());
        $this->assertEquals('#^foo}bar\.txt$#', Pattern::make('foo}bar.txt')->toRegex());
        $this->assertEquals('#^foo\^bar\.txt$#', Pattern::make('foo^bar.txt')->toRegex());
        $this->assertEquals('#^foo,bar\.txt$#', Pattern::make('foo,bar.txt')->toRegex());
        $this->assertEquals('#^foo/.*/[^/]*\.txt$#', Pattern::make('foo/**/*.txt')->toRegex());
    }

    public function test_it_can_convert_a_glob_pattern_with_lookaheads(): void
    {
        $this->assertEquals('#^[^/]*\.txt(?=\.gz)$#', Pattern::make('*.txt(=.gz)')->toRegex());
        $this->assertEquals('#^[^/]*\.txt(?!\.gz)$#', Pattern::make('*.txt(!.gz)')->toRegex());
        $this->assertEquals('#^[^/]*\.txt(?=\.(gz|xz))$#', Pattern::make('*.txt(=.{gz,xz})')->toRegex());
        $this->assertEquals('#^[^/]*\.txt(?!\.(gz|xz))$#', Pattern::make('*.txt(!.{gz,xz})')->toRegex());
    }

    public function test_regular_expression_start_and_end_anchors_are_configurable(): void
    {
        $this->assertEquals('#foo#', Pattern::make('foo')->toRegex(Pattern::NO_ANCHORS));
        $this->assertEquals('#^foo#', Pattern::make('foo')->toRegex(Pattern::START_ANCHOR));
        $this->assertEquals('#foo$#', Pattern::make('foo')->toRegex(Pattern::END_ANCHOR));
        $this->assertEquals('#^foo$#', Pattern::make('foo')->toRegex(Pattern::BOTH_ANCHORS));
        $this->assertEquals('#^foo$#', Pattern::make('foo')->toRegex(Pattern::START_ANCHOR | Pattern::END_ANCHOR));
    }

    public function test_it_can_use_back_slash_as_the_directory_separator(): void
    {
        Pattern::directorySeparator('\\');

        $this->assertEquals('#^[^\\\\]*$#', Pattern::make('*')->toRegex());
        $this->assertEquals('#^.*$#', Pattern::make('**')->toRegex());
    }

    public function test_it_can_escape_a_glob_string(): void
    {
        $this->assertEquals('\\\\', Pattern::escape('\\'));
        $this->assertEquals('\\?', Pattern::escape('?'));
        $this->assertEquals('\\*', Pattern::escape('*'));
        $this->assertEquals('\\[', Pattern::escape('['));
        $this->assertEquals('\\]', Pattern::escape(']'));
        $this->assertEquals('\\^', Pattern::escape('^'));
        $this->assertEquals('\\{', Pattern::escape('{'));
        $this->assertEquals('\\}', Pattern::escape('}'));
        $this->assertEquals('\\,', Pattern::escape(','));
        $this->assertEquals('\\(', Pattern::escape('('));
        $this->assertEquals('\\)', Pattern::escape(')'));

        $this->assertEquals(
            '\\\\\\?\\*\\(\\)\\[\\]\\^\\{\\}\\,',
            Pattern::escape('\\?*()[]^{},')
        );
    }
}
