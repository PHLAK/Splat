<p align="center">
  <img src="splat.svg" alt="Splat" width="50%">
</p>

<p align="center">
    <a href="https://github.com/PHLAK/Splat/discussions"><img src="https://img.shields.io/badge/Join_the-Community-7b16ff.svg?style=for-the-badge" alt="Join our Community"></a>
    <a href="https://github.com/users/PHLAK/sponsorship"><img src="https://img.shields.io/badge/Become_a-Sponsor-cc4195.svg?style=for-the-badge" alt="Become a Sponsor"></a>
    <a href="https://paypal.me/ChrisKankiewicz"><img src="https://img.shields.io/badge/Make_a-Donation-006bb6.svg?style=for-the-badge" alt="One-time Donation"></a>
    <br>
    <a href="https://packagist.org/packages/PHLAK/Splat"><img src="https://img.shields.io/packagist/v/PHLAK/Splat.svg?style=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/PHLAK/Splat"><img src="https://img.shields.io/packagist/dt/PHLAK/Splat.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="https://github.com/PHLAK/Splat/blob/master/LICENSE"><img src="https://img.shields.io/github/license/PHLAK/Splat?style=flat-square" alt="License"></a>
    <a href="https://github.com/PHLAK/Splat/actions"><img alt="GitHub branch checks state" src="https://img.shields.io/github/checks-status/phlak/splat/master?style=flat-square"></a>
</p>

---

Glob-like file and pattern matching utility.

Requirements
------------

  - [PHP](https://www.php.net/) >= 7.2

Installation
------------

Install Splat with Composer.

    composer require phlak/splat

Then import the `Glob` or `Pattern` classes as needed.

```php
use PHLAK\Splat\Glob;
use PHLAK\Splat\Pattern;
```

Patterns
--------

`Glob` methods accept a `$pattern` as the first parameter. This can be a string
or an instance of `\PHLAK\Splat\Pattern`. A pattern string may contain one or
more of the following special matching expressions.

### Matching Expressions

  - `?` matches any single character
  - `*` matches zero or more characters excluding `/` (`\` on Windows)
  - `**` matches zero or more characters including `/` (`\` on Windows)
  - `[abc]` matches a single character from the set (i.e. `a`, `b` or `c`)
  - `[a-c]` matches a single character in the range (i.e. `a`, `b` or `c`)
  - `[^abc]` matches any character not in the set (i.e. not `a`, `b` or `c`)
  - `[^a-c]` matches any character not in the range (i.e. not `a`, `b` or `c`)
  - `{foo,bar,baz}` matches any pattern in the set (i.e. `foo`, `bar` or `baz`)

### Assertions

The following assertions can be use to assert that a string is followed by or
not followed by another pattern.

  - `(=foo)` matches any string that also contains `foo`
  - `(!foo)` matches any string that does not also contain `foo`

For example, a pattern of `*.tar(!.{gz|xz})` will match a string ending with
`.tar` or `.tar.bz` but not `tar.gz` or `tar.xz`.

### Converting Patterns To Regular Expressions

Convet a glob pattern to a regular expression pattern.

```php
Pattern::make('foo')->toRegex(); // Returns '#^foo$#'
Pattern::make('foo/bar.txt')->toRegex(); // Returns '#^foo/bar\.txt$#'
Pattern::make('file.{yml,yaml}')->toRegex(); // Returns '#^file\.(yml|yaml)$#'
```

  You can also control line anchors via the `$options` parameter.

```php
Pattern::make('foo')->toRegex(Glob::NO_ANCHORS); // Returns '#foo#'
Pattern::make('foo')->toRegex(Glob::START_ANCHOR); // Returns '#^foo#'
Pattern::make('foo')->toRegex(Glob::END_ANCHOR); // Returns '#foo$#'
Pattern::make('foo')->toRegex(Glob::BOTH_ANCHORS); // Returns '#^foo$#'
Pattern::make('foo')->toRegex(Glob::START_ANCHOR | Glob::END_ANCHOR); // Returns '#^foo$#'
```

---

### Escape

Escape glob pattern characters from a string.

```php
Pattern::escape('What?'); // Returns 'What\?'
Pattern::escape('*.{yml,yaml}'); // Returns '\*.\{yml\,yaml\}'
Pattern::escape('[Gg]l*b.txt'); // Returns '\[Gg\]l\*b.txt'
```

Methods
-------

### Files In

Get a list of files in a directory matching a glob pattern.

```php
Glob::in('**.txt', 'some/file/path');
```

Returns a [Symfony Finder Component](https://symfony.com/doc/current/components/finder.html)
containing the files matching the glob pattern within the specified directory 
(e.g. `foo.txt`, `foo/bar.txt`, `foo/bar/baz.txt`, etc.).

---

### Exact Match

Test if a string matches a glob pattern.

```php
Glob::match('*.txt', 'foo.txt'); // true
Glob::match('*.txt', 'foo.log'); // false
```

---

### Match Start

Test if a string starts with a glob pattern.

```php
Glob::matchStart('foo/*', 'foo/bar.txt'); // true
Glob::matchStart('foo/*', 'bar/foo.txt'); // false
```

---

### Match End

Test if a string ends with a glob pattern.

```php
Glob::matchEnd('**.txt', 'foo/bar.txt'); // true
Glob::matchEnd('**.txt', 'foo/bar.log'); // false
```

---

### Match Within

Test if a string contains a glob pattern.

```php
Glob::matchWithin('bar', 'foo/bar/baz.txt'); // true
Glob::matchWithin('bar', 'foo/baz/qux.txt'); // false
```

---

### Filter an Array (of Strings)

Filter an array of strings to values matching a glob pattern.

```php
Glob::filter('**.txt', [
    'foo', 'foo.txt', 'bar.zip', 'foo/bar.png', 'foo/bar.txt',
]);

// Returns ['foo.txt', 'foo/bar.txt']
```

---

### Reject an Array (of Strings)

Filter an array of strings to values *not* matching a glob pattern.

```php
Glob::reject('**.txt', [
    'foo', 'foo.txt', 'bar.zip', 'foo/bar.png', 'foo/bar.txt',
]);

// Returns ['foo', 'bar.zip', 'foo/bar.png']
```


Changelog
---------

A list of changes can be found on the [GitHub Releases](https://github.com/PHLAK/Splat/releases) page.

Troubleshooting
---------------

For general help and support join our [GitHub Discussion](https://github.com/PHLAK/Splat/discussions) or reach out on [Twitter](https://twitter.com/PHLAK).

Please report bugs to the [GitHub Issue Tracker](https://github.com/PHLAK/Splat/issues).

Copyright
---------

This project is licensed under the [MIT License](https://github.com/PHLAK/Splat/blob/master/LICENSE).
