<?php
/**
 * Copyright (c) 2012 Blake Harley
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * This software is licensed under the MIT license.
 */

namespace Blake;

/**
 * This is a very simple autoloading class.
 * 
 * @package AmazonParser
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0
 */
class Autoloader
{
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected static $dir = '';
	
	/**
	 * Registers this class with PHP as an autoloader.
	 * 
	 * @since 1.0 
	 */
	public static function register()
	{
		self::$dir = dirname(__DIR__);
		define('LIBRARY_PATH', self::$dir);
		spl_autoload_register(__NAMESPACE__ .'\Autoloader::autoload');
	}
	
	/**
	 * Attempts to autoload the given classname by turning the namespace into
	 * the directory path.
	 * 
	 * @param string $classname The class to autoload
	 */
	public static function autoload($classname)
	{
		require self::$dir . DIRECTORY_SEPARATOR . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $classname) .'.php';
	}
}