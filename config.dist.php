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

/**
 * This is an example configuration file. Copy it into 'config.php' and change
 * the values below as desired.
 * 
 * @package AmazonParser
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0 
 */
return array(
	// REQUIRED SETTINGS
	// The email address you use to log into Amazon.com.
	'emailAddress' => '',
	
	// The password you use to log into Amazon.com.
	'password' => '',
	
	
	// OPTIONAL SETTINGS
	// The file cookies will be stored in. Make sure PHP has write access here!
	'cookieFile' => 'cookies/cookie.txt',
	
	// The user agent (browser) that will be reported to Amazon. The provided one is Google Chrome version 19.
	'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.52 Safari/536.5',
	
	// The URL used to log into Amazon.com. You probably shouldn't change this.
	'loginRoute' => 'https://www.amazon.com/ap/signin?_encoding=UTF8&openid.assoc_handle=usflex&openid.return_to=https%3A%2F%2Fwww.amazon.com%2Fgp%2Fyourstore%2Fhome%3Fie%3DUTF8%26ref_%3Dpd_ys_home_signin%26signIn%3D1&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.pape.max_auth_age=900&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select',
);