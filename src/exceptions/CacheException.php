<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\simple\exceptions;

use Psr\SimpleCache\CacheException as SimpleCacheCacheException;
use RuntimeException;

/**
 * Invalid argument exception.
 */
class CacheException extends RuntimeException implements SimpleCacheCacheException
{

}
