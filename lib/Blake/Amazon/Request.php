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

namespace Blake\Amazon;

/**
 * This class handles connecting with and processing requests with Amazon.com
 * using cURL.
 * 
 * @package AmazonParser
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0
 */
class Request
{
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected $_emailAddress = null;
	
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected $_password = null;
	
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected $_cookieFile = null;
	
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected $_userAgent = null;
	
	/**
	 * @since 1.0
	 * @var   string
	 */
	protected $_loginRoute = null;
	
	/**
	 * @since 1.0
	 * @var   boolean
	 */
	protected $_hasAuthenticated = false;
	
	/**
	 * Creates a new Request object with the given options.
	 * 
	 * @since 1.0
	 * @param array $options Initial options
	 */
	public function __construct($options)
	{
		$this->setOptions($options);
	}
	
	/**
	 * Gets the given URI from Amazon.com. Proper authentication will be
	 * retrived if it hasn't already been.
	 * 
	 * @since 1.0
	 * @param string $uri The URI to fetch
	 */
	public function getUri($uri)
	{
		$this->_checkAuthentication();
		
		return $this->_doCurl("https://www.amazon.com/$uri");
	}
	
	/**
	 * Checks to see if proper authentication has already been established.
	 * Otherwise authentication will be retreived.
	 * 
	 * @since  1.0
	 */
	protected function _checkAuthentication()
	{
		// If this hasn't been authenticated yet, do so
		if (!$this->_hasAuthenticated)
		{
			// Get valid form data and parse it
			$html = $this->_doCurl($this->_loginRoute);
			preg_match_all('/<input type="hidden" name="(.*?)" value="(.*?)"/', $html, $matches, PREG_SET_ORDER);

			// Now we piece together valid POST data based on the form we fetched
			$postData = array();
			foreach ($matches as $group)
			{
				$postData[$group[1]] = $group[2];
			}
			$postData['email'] = $this->_emailAddress;
			$postData['password'] = $this->_password;
			$postData['create'] = '0';
			$postData = http_build_query($postData, '', '&');
			
			// Go through the actual login process now
			$this->_doCurl($this->_loginRoute, array(
				CURLOPT_POST       => true,
				CURLOPT_POSTFIELDS => $postData,
			));
			
			// Now we have a valid login(?)
			// TODO: Actually verify that we have successfully logged in.
			$this->_hasAuthenticated = true;
		}
	}
	
	/**
	 * Processes a cURL request at the given location using the provided cURL
	 * options.
	 * 
	 * @since  1.0
	 * @param  string $location The location to execute
	 * @param  array $opts Additional cURL options (optional)
	 * @return string The resulting cURL data
	 */
	protected function _doCurl($location, $opts = array())
	{
		$this->_checkOptions();
		
		// Default cURL options
		$options = array(
			CURLOPT_HEADER => array(
				'User-Agent: '. $this->_userAgent,
				'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3',
				'Accept-Language:en-US,en;q=0.8',
				'Origin: https://www.amazon.com',
			),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEJAR      => $this->_cookieFile,
			CURLOPT_COOKIEFILE     => $this->_cookieFile,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER         => false,
			CURLOPT_ENCODING       => 'gzip',
//			CURLOPT_VERBOSE        => true,
//			CURLOPT_STDERR         => fopen('php://output', 'w'),
		);
		// Merge with new options
		$options = $this->_mergeOptions($options, $opts);
		
		$curl = curl_init($location);
		curl_setopt_array($curl, $options);
		$data = curl_exec($curl);
		curl_close($curl);
		
		return $data;
	}
	
	/**
	 * Verifies whether or not every option has been set.
	 * 
	 * @since  1.0
	 * @throws Exception If any of the required options have no been set 
	 */
	protected function _checkOptions()
	{
		$props = get_object_vars($this);
		
		foreach ($props as $key => $value)
		{
			switch (gettype($value))
			{
				case 'string':
					if (strlen($value) < 1)
					{
						throw new \Exception('Property "'. $key .'" must be set');
					}
					break;
				default:
					if ($value === null)
					{
						throw new \Exception('Property "'. $key .'" must be set');
					}
					break;
			}
		}
	}
	
	/**
	 * Recursively merges the given options. The second parameter takes priority
	 * over the first array.
	 * 
	 * @since  1.0
	 * @param  array $arr1
	 * @param  array $arr2
	 * @return array 
	 */
	protected function _mergeOptions($arr1, $arr2)
	{
		foreach ($arr2 as $key => $value)
		{
			// If the original array shares the same key, take care of collisions
			if (array_key_exists($key, $arr1))
			{
				// If they're both arrays, merge them
				if (is_array($value) && is_array($arr1[$key]))
				{
					$arr1[$key] = $this->_mergeOptions($arr1[$key], $value);
				}
				// Otherwise arr2 has higher priority
				else
				{
					$arr1[$key] = $value;
				}
			}
			else
			{
				$arr1[$key] = $value;
			}
		}
		
		return $arr1;
	}

	/**
	 * Sets the given options.
	 * 
	 * @since  1.0
	 * @param  array $options The options to set
	 * @throws Exception If options are not of type array
	 */
	public function setOptions($options)
	{
		if (!is_array($options))
		{
			throw new \Exception('Options must be of type array');
		}
		
		// Set all of these options
		foreach ($options as $key => $value)
		{
			$this->setOption($key, $value);
		}
	}
	
	/**
	 * Sets the given named option to the given value.
	 * 
	 * @since  1.0
	 * @param  string $key The option to set
	 * @param  mixed $value The value to set the option to
	 * @throws Exception If the key is not a valid option
	 */
	public function setOption($key, $value)
	{
		$method = 'set'. ucfirst($key);
		$this->$method($value);
	}
	
	/**
	 * This method allows you to set private properties by calling
	 * Request::set{prop}($value) where prop is the property's name without the
	 * underscore.
	 * 
	 * @since  1.0
	 * @param  string $name The method name trying to be called
	 * @param  array $args The arguments
	 * @throws Exception
	 */
	public function __call($name, $args)
	{
		// This is a set request
		if (strpos($name, 'set') === 0)
		{
			$property = '_'. lcfirst(substr($name, 3));
			if (!property_exists($this, $property))
			{
				throw new \Exception(__CLASS__ ." does not have property '". lcfirst(substr($name, 3)) ."'");
			}
			
			$this->$property = $args[0];
			return;
		}
	}
}