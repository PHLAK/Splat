<p align="center">
  <img src="glob.svg" alt="Glob" width="50%">
</p>

<p align="center">
  <a href="https://github.com/PHLAK/Glob/blob/master/LICENSE"><img src="https://img.shields.io/github/license/PHLAK/Glob?style=flat-square" alt="License"></a>
  <a href="https://spectrum.chat/phlaknet"><img src="https://img.shields.io/badge/Join_the-Community-7b16ff.svg?style=flat-square" alt="Join our Community"></a>
  <a href="https://github.com/users/PHLAK/sponsorship"><img src="https://img.shields.io/badge/Become_a-Sponsor-cc4195.svg?style=flat-square" alt="Become a Sponsor"></a>
  <a href="https://paypal.me/ChrisKankiewicz"><img src="https://img.shields.io/badge/Make_a-Donation-006bb6.svg?style=flat-square" alt="One-time Donation"></a>
  <a href="https://travis-ci.com/PHLAK/Glob"><img src="https://img.shields.io/travis/com/PHLAK/Glob/master?style=flat-square" alt="Build Status"></a>
  <a href="https://styleci.io/repos/1375774"><img src="https://styleci.io/repos/1375774/shield?branch=master" alt="StyleCI"></a>
</p>

---

Glob-like pattern matching and utilities.

Requirements
------------

  - [PHP](https://www.php.net/) >= 7.0

Installation
------------

    composer require phlak/glob

Usage
-------------

### Initialization

  ```php
  use PHLAK\Utilities\Glob;

  new Glob($pattern);
  // or
  Glob::pattern($pattern);
  ```

When instantiating a `Glob` object you must supply a `$pattern` string that may
contain one or more of the following special matching expressions.

#### Matching Expressions

  - `?` matches any single character
  - `*` matches zero or more characters excluding `/` (`\` on Windows)
  - `**` matches zero or more characters including `/` (`\` on Windows)
  - `[abc]` matches a single character from the set (i.e. `a`, `b` or `c`)
  - `[a-c]` matches a single character in the range (i.e. `a`, `b` or `c`)
  - `[^abc]` matches any character not in the set (i.e. not `a`, `b` or `c`)
  - `[^a-c]` matches any character not in the range (i.e. not `a`, `b` or `c`)
  - `{foo,bar,baz}` matches any pattern in the set (i.e. `foo`, `bar` or `baz`)

---

### Exact Match

Test if a string matches the glob pattern.

  ```php
  Glob::pattern('*.txt')->match('foo.txt'); // true
  Glob::pattern('*.txt')->match('foo.log'); // false
  ```

---

### Match Start

Test if a string starts with the glob pattern.

  ```php
  Glob::pattern('foo/*')->matchStart('foo/bar.txt'); // true
  Glob::pattern('foo/*')->matchStart('bar/foo.txt'); // false
  ```

---

### Match End

Test if a string ends with the glob pattern.

  ```php
  Glob::pattern('**.txt')->matchEnd('foo/bar.txt'); // true
  Glob::pattern('**.txt')->matchEnd('foo/bar.log'); // false
  ```

---

### Match Within

Test if a string contains the glob pattern.

  ```php
  Glob::pattern('bar')->matchWithin('foo/bar/baz.txt'); // true
  Glob::pattern('bar')->matchWithin('foo/baz/qux.txt'); // false
  ```

---

### To Regular Expression

Convet the glob-like pattern to a regular expression pattern.

  ```php
  Glob::pattern('foo')->toRegex(); // Returns '#^foo$#'
  Glob::pattern('foo/bar.txt')->toRegex(); // Returns '#^foo/bar\.txt$#'
  Glob::pattern('file.{yml,yaml}')->toRegex(); // Returns '#^file\.(yml|yaml)$#'
  ```

  You can also control line anchors via the `$options` parameter.

  ```php
  Glob::pattern('foo')->toRegex(Glob::NO_ANCHORS); // Returns '#foo#'
  Glob::pattern('foo')->toRegex(Glob::START_ANCHOR); // Returns '#^foo#'
  Glob::pattern('foo')->toRegex(Glob::END_ANCHOR); // Returns '#foo$#'
  Glob::pattern('foo')->toRegex(Glob::BOTH_ANCHORS); // Returns '#^foo$#'
  Glob::pattern('foo')->toRegex(Glob::START_ANCHOR | Glob::END_ANCHOR); // Returns '#^foo$#'
  ```

Changelog
---------

A list of changes can be found on the [GitHub Releases](https://github.com/PHLAK/Glob/releases) page.

Troubleshooting
---------------

See the [Common Issues](https://github.com/PHLAK/Glob/wiki/Common-Issues) page for a list of common issues and help in solving them.

For general help and support join our [Spectrum Community](https://spectrum.chat/phlaknet) or reach out on [Twitter](https://twitter.com/PHLAK).

Please report bugs to the [GitHub Issue Tracker](https://github.com/PHLAK/Glob/issues).

Copyright
---------

This project is licensed under the [MIT License](https://github.com/PHLAK/Glob/blob/master/LICENSE).
