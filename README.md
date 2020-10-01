# Mako Simple Cache

[![Build Status](https://img.shields.io/travis/mako-framework/simple-cache/master.svg?style=flat)](https://travis-ci.org/mako-framework/simple-cache)

A [Simple Cache](https://www.php-fig.org/psr/psr-16/) adapter for the Mako Framework.

## Requirements

Mako 7.0 or greater.

## Installation

Install the package using the following composer command:

```
composer require mako/simple-cache
```

## Usage

Create a `SimpleCache` object by injecting a Mako cache store instance and you're good to go.

```
$simpleCache = new SimpleCache($this->cache->instance());
```