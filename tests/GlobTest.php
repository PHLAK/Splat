<?php

namespace Tests;

use PHLAK\Utilities\Glob;
use PHPUnit\Framework\TestCase;

class GlobTest extends TestCase
{
    public function test_it_is_stringable(): void
    {
        $this->assertEquals('foo.txt', (string) Glob::pattern('foo.txt'));
        $this->assertEquals('**.txt', (string) Glob::pattern('**.txt'));
        $this->assertEquals('foo.{yml,yaml}', (string) Glob::pattern('foo.{yml,yaml}'));
    }

    public function test_it_can_convert_a_litteral_string_to_a_regular_expression(): void
    {
        $this->assertEquals('#^foo$#', Glob::pattern('foo')->toRegex());
        $this->assertEquals('#^foo/bar$#', Glob::pattern('foo/bar')->toRegex());
        $this->assertEquals('#^foo/bar\.txt$#', Glob::pattern('foo/bar.txt')->toRegex());
    }

    public function test_it_converts_glob_patterns_to_regular_expression_patterns(): void
    {
        $this->assertEquals('#^$#', Glob::pattern('')->toRegex());
        $this->assertEquals('#^.$#', Glob::pattern('?')->toRegex());
        $this->assertEquals('#^[^/]*$#', Glob::pattern('*')->toRegex());
        $this->assertEquals('#^.*$#', Glob::pattern('**')->toRegex());
        $this->assertEquals('#^\#$#', Glob::pattern('#')->toRegex());
        $this->assertEquals('#^\\?$#', Glob::pattern('\\?')->toRegex());
    }

    public function test_it_can_escape_glob_patterns_when_converting_to_regular_expressions(): void
    {
        $this->assertEquals('#^\\\\$#', Glob::pattern('\\\\')->toRegex());
        $this->assertEquals('#^\\?$#', Glob::pattern('\?')->toRegex());
        $this->assertEquals('#^\\*$#', Glob::pattern('\*')->toRegex());
        $this->assertEquals('#^\\*\\*$#', Glob::pattern('\*\*')->toRegex());
        $this->assertEquals('#^\\#$#', Glob::pattern('\#')->toRegex());
    }

    public function test_it_can_convert_a_complex_glob_pattern_to_a_regular_expressions(): void
    {
        $this->assertEquals('#^foo\.txt$#', Glob::pattern('foo.txt')->toRegex());
        $this->assertEquals('#^foo/bar\.txt$#', Glob::pattern('foo/bar.txt')->toRegex());
        $this->assertEquals('#^foo\?bar\.txt$#', Glob::pattern('foo\?bar.txt')->toRegex());
        $this->assertEquals('#^[^/]*\.txt$#', Glob::pattern('*.txt')->toRegex());
        $this->assertEquals('#^.*/[^/]*\.txt$#', Glob::pattern('**/*.txt')->toRegex());
        $this->assertEquals('#^([^/]*|.*/[^/]*)\.txt$#', Glob::pattern('{*,**/*}.txt')->toRegex());
        $this->assertEquals('#^file\.(yml|yaml)$#', Glob::pattern('file.{yml,yaml}')->toRegex());
        $this->assertEquals('#^[fbw]oo\.txt$#', Glob::pattern('[fbw]oo.txt')->toRegex());
        $this->assertEquals('#^[^fbw]oo\.txt$#', Glob::pattern('[^fbw]oo.txt')->toRegex());
        $this->assertEquals('#^foo}bar\.txt$#', Glob::pattern('foo}bar.txt')->toRegex());
        $this->assertEquals('#^foo\^bar\.txt$#', Glob::pattern('foo^bar.txt')->toRegex());
        $this->assertEquals('#^foo,bar\.txt$#', Glob::pattern('foo,bar.txt')->toRegex());
        $this->assertEquals('#^foo/.*/[^/]*\.txt$#', Glob::pattern('foo/**/*.txt')->toRegex());
    }

    public function test_regular_expression_start_and_end_anchors_are_configurable(): void
    {
        $this->assertEquals('#foo#', Glob::pattern('foo')->toRegex(Glob::NO_ANCHORS));
        $this->assertEquals('#^foo#', Glob::pattern('foo')->toRegex(Glob::START_ANCHOR));
        $this->assertEquals('#foo$#', Glob::pattern('foo')->toRegex(Glob::END_ANCHOR));
        $this->assertEquals('#^foo$#', Glob::pattern('foo')->toRegex(Glob::BOTH_ANCHORS));
        $this->assertEquals('#^foo$#', Glob::pattern('foo')->toRegex(Glob::START_ANCHOR | Glob::END_ANCHOR));
    }

    public function test_it_matches_a_literal_value(): void
    {
        $this->assertTrue(Glob::pattern('foo')->match('foo'));
        $this->assertFalse(Glob::pattern('bar')->match('foo'));
    }

    public function test_it_matches_a_single_character(): void
    {
        $this->assertTrue(Glob::pattern('?')->match('f'));
        $this->assertTrue(Glob::pattern('??')->match('fo'));
        $this->assertTrue(Glob::pattern('???')->match('foo'));

        $this->assertFalse(Glob::pattern('?')->match('foo'));
        $this->assertFalse(Glob::pattern('???')->match('f'));
    }

    public function test_it_matches_zero_or_more_characters_excluding_slash(): void
    {
        $this->assertTrue(Glob::pattern('*')->match('foo'));
        $this->assertTrue(Glob::pattern('*')->match('foo\\bar'));
        $this->assertTrue(Glob::pattern('*.txt')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('*/*')->match('foo/bar'));
        $this->assertTrue(Glob::pattern('*/*.txt')->match('foo/bar.txt'));

        $this->assertFalse(Glob::pattern('*')->match('foo/bar'));
        $this->assertFalse(Glob::pattern('*.txt')->match('foo/bar.txt'));
        $this->assertFalse(Glob::pattern('*/*')->match('foo/bar/baz'));
        $this->assertFalse(Glob::pattern('*/*.txt')->match('foo/bar/baz.txt'));
    }

    public function test_it_matches_zero_or_more_characeters_including_slash(): void
    {
        $this->assertTrue(Glob::pattern('**')->match('foo'));
        $this->assertTrue(Glob::pattern('**')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('**')->match('foo/bar.txt'));
        $this->assertTrue(Glob::pattern('**')->match('foo/bar/baz.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo/bar.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo/bar/baz.txt'));

        $this->assertFalse(Glob::pattern('**.txt')->match('foo.bar'));
        $this->assertFalse(Glob::pattern('**/*.txt')->match('foo.txt'));
    }

    public function test_it_matches_a_single_character_from_a_set(): void
    {
        $this->assertTrue(Glob::pattern('[abc]')->match('a'));
        $this->assertTrue(Glob::pattern('[abc]')->match('b'));
        $this->assertTrue(Glob::pattern('[abc]')->match('c'));

        $this->assertTrue(Glob::pattern('[abc][abc]')->match('ab'));
        $this->assertTrue(Glob::pattern('[abc][abc]')->match('bc'));
        $this->assertTrue(Glob::pattern('[abc][abc]')->match('ca'));

        $this->assertTrue(Glob::pattern('[bfg]oo')->match('foo'));
        $this->assertTrue(Glob::pattern('[bfg]oo')->match('boo'));
        $this->assertTrue(Glob::pattern('[bfg]oo')->match('goo'));

        $this->assertFalse(Glob::pattern('[abc]')->match('abc'));
        $this->assertFalse(Glob::pattern('[bfg]oo')->match('zoo'));
        $this->assertFalse(Glob::pattern('[bfg]oo')->match('bar'));
        $this->assertFalse(Glob::pattern('[abc][abc]')->match('abc'));
    }

    public function test_it_matches_a_single_character_in_a_range(): void
    {
        $this->assertTrue(Glob::pattern('[a-c]')->match('a'));
        $this->assertTrue(Glob::pattern('[a-c]')->match('b'));
        $this->assertTrue(Glob::pattern('[a-c]')->match('c'));

        $this->assertTrue(Glob::pattern('[a-c][a-c]')->match('ab'));
        $this->assertTrue(Glob::pattern('[a-c][a-c]')->match('bc'));
        $this->assertTrue(Glob::pattern('[a-c][a-c]')->match('ca'));

        $this->assertTrue(Glob::pattern('[f-h]oo')->match('foo'));
        $this->assertTrue(Glob::pattern('[f-h]oo')->match('goo'));
        $this->assertTrue(Glob::pattern('[f-h]oo')->match('hoo'));

        $this->assertFalse(Glob::pattern('[a-c]')->match('abc'));
        $this->assertFalse(Glob::pattern('[f-h]oo')->match('zoo'));
        $this->assertFalse(Glob::pattern('[a-c]oo')->match('bar'));
        $this->assertFalse(Glob::pattern('[a-c][a-c]')->match('abc'));
    }

    public function test_it_mathes_any_character_not_in_a_set(): void
    {
        $this->assertTrue(Glob::pattern('[^abc]')->match('x'));
        $this->assertTrue(Glob::pattern('[^abc]')->match('z'));

        $this->assertTrue(Glob::pattern('[^abc][^xyz]')->match('za'));
        $this->assertTrue(Glob::pattern('[^abc][^xyz]')->match('ya'));

        $this->assertTrue(Glob::pattern('[^abc]oo')->match('foo'));
        $this->assertTrue(Glob::pattern('[^abc]oo')->match('zoo'));

        $this->assertFalse(Glob::pattern('[^abc]')->match('a'));
        $this->assertFalse(Glob::pattern('[^abc]')->match('b'));
        $this->assertFalse(Glob::pattern('[^abc]')->match('c'));
        $this->assertFalse(Glob::pattern('[^abc]oo')->match('boo'));
        $this->assertFalse(Glob::pattern('[^abc][^xyz]')->match('cz'));
        $this->assertFalse(Glob::pattern('[^abc][^xyz]')->match('foo'));
    }

    public function test_it_matches_any_character_not_in_a_range(): void
    {
        $this->assertTrue(Glob::pattern('[^a-c]')->match('x'));
        $this->assertTrue(Glob::pattern('[^a-c]')->match('z'));

        $this->assertTrue(Glob::pattern('[^a-c][^x-z]')->match('za'));
        $this->assertTrue(Glob::pattern('[^a-c][^x-z]')->match('ya'));

        $this->assertTrue(Glob::pattern('[^a-c]oo')->match('foo'));
        $this->assertTrue(Glob::pattern('[^a-c]oo')->match('zoo'));

        $this->assertFalse(Glob::pattern('[^a-c]')->match('a'));
        $this->assertFalse(Glob::pattern('[^a-c]')->match('b'));
        $this->assertFalse(Glob::pattern('[^a-c]')->match('c'));
        $this->assertFalse(Glob::pattern('[^a-c]oo')->match('boo'));
        $this->assertFalse(Glob::pattern('[^a-c][^x-z]')->match('cz'));
        $this->assertFalse(Glob::pattern('[^a-c][^x-z]')->match('foo'));
    }

    public function test_it_matches_a_pattern_from_a_set(): void
    {
        $this->assertTrue(Glob::pattern('{foo,bar,baz}')->match('foo'));
        $this->assertTrue(Glob::pattern('{foo,bar,baz}')->match('bar'));
        $this->assertTrue(Glob::pattern('{foo,bar,baz}')->match('baz'));

        $this->assertTrue(Glob::pattern('foo/{bar,baz}/qux')->match('foo/bar/qux'));
        $this->assertTrue(Glob::pattern('foo/{bar,baz}/qux')->match('foo/baz/qux'));

        $this->assertTrue(Glob::pattern('foo.{yml,yaml}')->match('foo.yml'));
        $this->assertTrue(Glob::pattern('foo.{yml,yaml}')->match('foo.yaml'));

        $this->assertFalse(Glob::pattern('{foo,bar,baz}')->match('qux'));
    }

    public function test_it_matches_the_start_of_a_string(): void
    {
        $this->assertTrue(Glob::pattern('foo/*')->matchStart('foo/bar.txt'));
        $this->assertFalse(Glob::pattern('foo/*')->matchStart('bar/foo.txt'));
    }

    public function test_it_matches_the_end_of_a_string(): void
    {
        $this->assertTrue(Glob::pattern('**.txt')->matchEnd('foo/bar.txt'));
        $this->assertFalse(Glob::pattern('**.txt')->matchEnd('foo/bar.log'));
    }

    public function test_it_matches_within_a_string(): void
    {
        $this->assertTrue(Glob::pattern('*/bar/*')->matchWithin('foo/bar/baz.txt'));
        $this->assertFalse(Glob::pattern('*/bar/*')->matchWithin('foo/baz/qux.txt'));
    }

    public function test_it_matches_zero_or_more_characters_excluding_back_slash(): void
    {
        Glob::directorySeparator('\\');

        $this->assertTrue(Glob::pattern('*')->match('foo'));
        $this->assertTrue(Glob::pattern('*')->match('foo/bar'));
        $this->assertTrue(Glob::pattern('*.txt')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('*\\\\*')->match('foo\\bar'));
        $this->assertTrue(Glob::pattern('*\\\\*.txt')->match('foo\\bar.txt'));
        $this->assertTrue(Glob::pattern('*\\\\*')->match('foo\\bar'));

        $this->assertFalse(Glob::pattern('*')->match('foo\\bar'));
        $this->assertFalse(Glob::pattern('*')->match('foo\\bar'));
        $this->assertFalse(Glob::pattern('*.txt')->match('foo\\bar.txt'));
        $this->assertFalse(Glob::pattern('*\\*')->match('foo\\bar\\baz'));
        $this->assertFalse(Glob::pattern('*\\*.txt')->match('foo\\bar\\baz.txt'));
    }

    public function test_it_matches_zero_or_more_characeters_including_back_slash(): void
    {
        Glob::directorySeparator('\\');

        $this->assertTrue(Glob::pattern('**')->match('foo'));
        $this->assertTrue(Glob::pattern('**')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('**')->match('foo\bar.txt'));
        $this->assertTrue(Glob::pattern('**')->match('foo\bar\baz.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo\bar.txt'));
        $this->assertTrue(Glob::pattern('**.txt')->match('foo\bar\baz.txt'));

        $this->assertFalse(Glob::pattern('**.txt')->match('foo.bar'));
        $this->assertFalse(Glob::pattern('**\\\\*.txt')->match('foo.txt'));
    }

    public function test_it_can_escape_a_glob_string(): void
    {
        $this->assertEquals('\\\\', Glob::escape('\\'));
        $this->assertEquals('\\?', Glob::escape('?'));
        $this->assertEquals('\\*', Glob::escape('*'));
        $this->assertEquals('\\[', Glob::escape('['));
        $this->assertEquals('\\]', Glob::escape(']'));
        $this->assertEquals('\\^', Glob::escape('^'));
        $this->assertEquals('\\{', Glob::escape('{'));
        $this->assertEquals('\\}', Glob::escape('}'));
        $this->assertEquals('\\,', Glob::escape(','));

        $this->assertEquals(
            '\\\\\\?\\*\\[\\]\\^\\{\\}\\,',
            Glob::escape('\\?*[]^{},')
        );
    }
}
