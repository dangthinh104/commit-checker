# Laravel GIT commit checker by PHPCS, PHPMD

[![Latest Version](https://img.shields.io/github/release/thinh/commit-checker.svg?style=flat-square)](https://github.com/dangthinh104/commit-checker/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/botble/git-commit-checker/master.svg?style=flat-square)](https://travis-ci.org/dangthinh104/commit-checker)

## Installation

```bash
composer require dangthinh104/commit-checker
```

Publish the configuration:

### Install GIT hooks
```bash
php artisan git:install-hooks
```

- Create default PSR config (It will create phpcs.xml in your root project).

```bash
php artisan git:create-phpcs
```

- Create default PHPMD config (It will create phpmd.xml in your root project).

```bash
php artisan git:create-phpmd
```

- Run test manually (made sure you've added all changed files to git stage)

```bash
php artisan git:pre-commit
```
- Run test manually PHPMD(default just check PSR2)
```bash
php artisan git:pre-commit --phpmd
```
