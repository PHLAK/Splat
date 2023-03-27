<?php

namespace Tests;

use PHLAK\Splat\Glob;
use PHLAK\Splat\Pattern;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/** @covers \PHLAK\Splat\Glob */
class GlobTest extends TestCase
{
    public function test_it_matches_a_literal_value(): void
    {
        $this->assertTrue(Glob::match('foo', 'foo'));
        $this->assertFalse(Glob::match('bar', 'foo'));
    }

    public function test_it_matches_a_single_character(): void
    {
        $this->assertTrue(Glob::match('?', 'f'));
        $this->assertTrue(Glob::match('??', 'fo'));
        $this->assertTrue(Glob::match('???', 'foo'));

        $this->assertFalse(Glob::match('?', 'foo'));
        $this->assertFalse(Glob::match('???', 'f'));
    }

    public function test_it_matches_zero_or_more_characters_excluding_slash(): void
    {
        $this->assertTrue(Glob::match('*', 'foo'));
        $this->assertTrue(Glob::match('*', 'foo\\bar'));
        $this->assertTrue(Glob::match('*.txt', 'foo.txt'));
        $this->assertTrue(Glob::match('*/*', 'foo/bar'));
        $this->assertTrue(Glob::match('*/*.txt', 'foo/bar.txt'));

        $this->assertFalse(Glob::match('*', 'foo/bar'));
        $this->assertFalse(Glob::match('*.txt', 'foo/bar.txt'));
        $this->assertFalse(Glob::match('*/*', 'foo/bar/baz'));
        $this->assertFalse(Glob::match('*/*.txt', 'foo/bar/baz.txt'));
    }

    public function test_it_matches_zero_or_more_characeters_including_slash(): void
    {
        $this->assertTrue(Glob::match('**', 'foo'));
        $this->assertTrue(Glob::match('**', 'foo.txt'));
        $this->assertTrue(Glob::match('**', 'foo/bar.txt'));
        $this->assertTrue(Glob::match('**', 'foo/bar/baz.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo/bar.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo/bar/baz.txt'));

        $this->assertFalse(Glob::match('**.txt', 'foo.bar'));
        $this->assertFalse(Glob::match('**/*.txt', 'foo.txt'));
    }

    public function test_it_matches_a_single_character_from_a_set(): void
    {
        $this->assertTrue(Glob::match('[abc]', 'a'));
        $this->assertTrue(Glob::match('[abc]', 'b'));
        $this->assertTrue(Glob::match('[abc]', 'c'));

        $this->assertTrue(Glob::match('[abc][abc]', 'ab'));
        $this->assertTrue(Glob::match('[abc][abc]', 'bc'));
        $this->assertTrue(Glob::match('[abc][abc]', 'ca'));

        $this->assertTrue(Glob::match('[bfg]oo', 'foo'));
        $this->assertTrue(Glob::match('[bfg]oo', 'boo'));
        $this->assertTrue(Glob::match('[bfg]oo', 'goo'));

        $this->assertFalse(Glob::match('[abc]', 'abc'));
        $this->assertFalse(Glob::match('[bfg]oo', 'zoo'));
        $this->assertFalse(Glob::match('[bfg]oo', 'bar'));
        $this->assertFalse(Glob::match('[abc][abc]', 'abc'));
    }

    public function test_it_matches_a_single_character_in_a_range(): void
    {
        $this->assertTrue(Glob::match('[a-c]', 'a'));
        $this->assertTrue(Glob::match('[a-c]', 'b'));
        $this->assertTrue(Glob::match('[a-c]', 'c'));

        $this->assertTrue(Glob::match('[a-c][a-c]', 'ab'));
        $this->assertTrue(Glob::match('[a-c][a-c]', 'bc'));
        $this->assertTrue(Glob::match('[a-c][a-c]', 'ca'));

        $this->assertTrue(Glob::match('[f-h]oo', 'foo'));
        $this->assertTrue(Glob::match('[f-h]oo', 'goo'));
        $this->assertTrue(Glob::match('[f-h]oo', 'hoo'));

        $this->assertFalse(Glob::match('[a-c]', 'abc'));
        $this->assertFalse(Glob::match('[f-h]oo', 'zoo'));
        $this->assertFalse(Glob::match('[a-c]oo', 'bar'));
        $this->assertFalse(Glob::match('[a-c][a-c]', 'abc'));
    }

    public function test_it_matches_glob_wildcards_literally_in_character_classes(): void
    {
        $this->assertTrue(Glob::match('[[?*\]', '?'));
        $this->assertTrue(Glob::match('[[?*\]', '*'));
        $this->assertTrue(Glob::match('[[?*\]', '\\'));
        $this->assertFalse(Glob::match('[[?*\]', 'x'));
    }

    public function test_it_matches_any_character_not_in_a_set(): void
    {
        $this->assertTrue(Glob::match('[^abc]', 'x'));
        $this->assertTrue(Glob::match('[^abc]', 'z'));

        $this->assertTrue(Glob::match('[^abc][^xyz]', 'za'));
        $this->assertTrue(Glob::match('[^abc][^xyz]', 'ya'));

        $this->assertTrue(Glob::match('[^abc]oo', 'foo'));
        $this->assertTrue(Glob::match('[^abc]oo', 'zoo'));

        $this->assertFalse(Glob::match('[^abc]', 'a'));
        $this->assertFalse(Glob::match('[^abc]', 'b'));
        $this->assertFalse(Glob::match('[^abc]', 'c'));
        $this->assertFalse(Glob::match('[^abc]oo', 'boo'));
        $this->assertFalse(Glob::match('[^abc][^xyz]', 'cz'));
        $this->assertFalse(Glob::match('[^abc][^xyz]', 'foo'));
    }

    public function test_it_matches_any_character_not_in_a_range(): void
    {
        $this->assertTrue(Glob::match('[^a-c]', 'x'));
        $this->assertTrue(Glob::match('[^a-c]', 'z'));

        $this->assertTrue(Glob::match('[^a-c][^x-z]', 'za'));
        $this->assertTrue(Glob::match('[^a-c][^x-z]', 'ya'));

        $this->assertTrue(Glob::match('[^a-c]oo', 'foo'));
        $this->assertTrue(Glob::match('[^a-c]oo', 'zoo'));

        $this->assertFalse(Glob::match('[^a-c]', 'a'));
        $this->assertFalse(Glob::match('[^a-c]', 'b'));
        $this->assertFalse(Glob::match('[^a-c]', 'c'));
        $this->assertFalse(Glob::match('[^a-c]oo', 'boo'));
        $this->assertFalse(Glob::match('[^a-c][^x-z]', 'cz'));
        $this->assertFalse(Glob::match('[^a-c][^x-z]', 'foo'));
    }

    public function test_it_matches_a_pattern_from_a_set(): void
    {
        $this->assertTrue(Glob::match('{foo,bar,baz}', 'foo'));
        $this->assertTrue(Glob::match('{foo,bar,baz}', 'bar'));
        $this->assertTrue(Glob::match('{foo,bar,baz}', 'baz'));

        $this->assertTrue(Glob::match('foo/{bar,baz}/qux', 'foo/bar/qux'));
        $this->assertTrue(Glob::match('foo/{bar,baz}/qux', 'foo/baz/qux'));

        $this->assertTrue(Glob::match('foo.{yml,yaml}', 'foo.yml'));
        $this->assertTrue(Glob::match('foo.{yml,yaml}', 'foo.yaml'));

        $this->assertFalse(Glob::match('{foo,bar,baz}', 'qux'));
    }

    public function test_it_matches_the_start_of_a_string(): void
    {
        $this->assertTrue(Glob::matchStart('foo/*', 'foo/bar.txt'));
        $this->assertFalse(Glob::matchStart('foo/*', 'bar/foo.txt'));
    }

    public function test_it_matches_the_end_of_a_string(): void
    {
        $this->assertTrue(Glob::matchEnd('**.txt', 'foo/bar.txt'));
        $this->assertFalse(Glob::matchEnd('**.txt', 'foo/bar.log'));
    }

    public function test_it_matches_within_a_string(): void
    {
        $this->assertTrue(Glob::matchWithin('*/bar/*', 'foo/bar/baz.txt'));
        $this->assertFalse(Glob::matchWithin('*/bar/*', 'foo/baz/qux.txt'));
    }

    public function test_it_matches_zero_or_more_characters_excluding_back_slash(): void
    {
        Pattern::directorySeparator('\\');

        $this->assertTrue(Glob::match('*', 'foo'));
        $this->assertTrue(Glob::match('*', 'foo/bar'));
        $this->assertTrue(Glob::match('*.txt', 'foo.txt'));
        $this->assertTrue(Glob::match('*\\\\*', 'foo\\bar'));
        $this->assertTrue(Glob::match('*\\\\*.txt', 'foo\\bar.txt'));
        $this->assertTrue(Glob::match('*\\\\*', 'foo\\bar'));

        $this->assertFalse(Glob::match('*', 'foo\\bar'));
        $this->assertFalse(Glob::match('*', 'foo\\bar'));
        $this->assertFalse(Glob::match('*.txt', 'foo\\bar.txt'));
        $this->assertFalse(Glob::match('*\\*', 'foo\\bar\\baz'));
        $this->assertFalse(Glob::match('*\\*.txt', 'foo\\bar\\baz.txt'));
    }

    public function test_it_matches_zero_or_more_characeters_including_back_slash(): void
    {
        Pattern::directorySeparator('\\');

        $this->assertTrue(Glob::match('**', 'foo'));
        $this->assertTrue(Glob::match('**', 'foo.txt'));
        $this->assertTrue(Glob::match('**', 'foo\bar.txt'));
        $this->assertTrue(Glob::match('**', 'foo\bar\baz.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo\bar.txt'));
        $this->assertTrue(Glob::match('**.txt', 'foo\bar\baz.txt'));

        $this->assertFalse(Glob::match('**.txt', 'foo.bar'));
        $this->assertFalse(Glob::match('**\\\\*.txt', 'foo.txt'));
    }

    public function test_it_can_match_a_string_with_a_lookahead(): void
    {
        $this->assertTrue(Glob::matchWithin('*.tar(=.gz)', 'foo.tar.gz'));
        $this->assertFalse(Glob::matchWithin('*.tar(=.gz)', 'foo.tar.xz'));
        $this->assertFalse(Glob::matchWithin('*.tar(=.gz)', 'foo.tar'));

        $this->assertTrue(Glob::matchWithin('*.tar(=.{gz,xz})', 'foo.tar.gz'));
        $this->assertTrue(Glob::matchWithin('*.tar(=.{gz,xz})', 'foo.tar.xz'));
        $this->assertFalse(Glob::matchWithin('*.tar(=.{gz,xz})', 'foo.tar.bz'));
        $this->assertFalse(Glob::matchWithin('*.tar(=.{gz,xz})', 'foo.tar'));
    }

    public function test_it_can_match_a_string_with_a_negative_lookahead(): void
    {
        $this->assertTrue(Glob::matchWithin('*.tar(!.gz)', 'foo.tar'));
        $this->assertTrue(Glob::matchWithin('*.tar(!.gz)', 'foo.tar.xz'));
        $this->assertFalse(Glob::matchWithin('*.tar(!.gz)', 'foo.tar.gz'));

        $this->assertTrue(Glob::matchWithin('*.tar(!.{gz,xz})', 'foo.tar'));
        $this->assertTrue(Glob::matchWithin('*.tar(!.{gz,xz})', 'foo.tar.bz'));
        $this->assertFalse(Glob::matchWithin('*.tar(!.{gz,xz})', 'foo.tar.gz'));
        $this->assertFalse(Glob::matchWithin('*.tar(!.{gz,xz})', 'foo.tar.xz'));
    }

    public function test_it_can_filter_an_array(): void
    {
        $filtered = Glob::filter('**.txt', [
            'foo', 'foo.txt', 'bar.zip', 'foo/bar.png', 'foo/bar.txt',
        ]);

        $this->assertEquals(['foo.txt', 'foo/bar.txt'], array_values($filtered));
    }

    public function test_it_can_reject_an_array(): void
    {
        $rejected = Glob::reject('**.txt', [
            'foo', 'foo.txt', 'bar.zip', 'foo/bar.png', 'foo/bar.txt',
        ]);

        $this->assertEquals(['foo', 'bar.zip', 'foo/bar.png'], array_values($rejected));
    }

    public function test_it_can_return_a_list_of_files_matching_the_pattern(): void
    {
        $files = Glob::in('**.txt', __DIR__ . '/_files');

        $this->assertInstanceOf(Finder::class, $files);

        $this->assertEquals([
            $path = sprintf('%s/%s', __DIR__, '_files/foo.txt') => new SplFileInfo($path, '', 'foo.txt'),
            $path = sprintf('%s/%s', __DIR__, '_files/foo/bar.txt') => new SplFileInfo($path, 'foo', 'foo/bar.txt'),
            $path = sprintf('%s/%s', __DIR__, '_files/foo/bar/baz.txt') => new SplFileInfo($path, 'foo/bar', 'foo/bar/baz.txt'),
        ], iterator_to_array($files));
    }
}
