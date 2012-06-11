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

use \DateTime,
	\Zend\Dom\Query;

/**
 * Retrieves all order entries from Amazon using Blake\Amazon\Request and then
 * parses various information out of it.
 * 
 * @package AmazonParser
 * @author  Blake Harley <contact@blakeharley.com>
 * @since   1.0
 */
class OrderHistory
{
	/**
	 * @since 1.0
	 * @var   Blake\Amazon\Request
	 */
	protected $_request = null;
	
	/**
	 * Initiates this class with the given request object.
	 * 
	 * @since 1.0
	 * @param \Blake\Amazon\Request $request 
	 */
	public function __construct($request)
	{
		$this->setRequest($request);
	}
	
	public function getHistory()
	{
		// First get the history for the most recent year
		$result =  $this->getHistoryForYear(date('Y'));
		$orders = $result['orders'];
		
		foreach ($result['years'] as $year)
		{
			// Skip the year we already covered
			if ($year == date('Y'))
			{
				continue;
			}
			
			$results = $this->getHistoryForYear($year);
			$orders = array_merge($orders, $results['orders']);
		}
		
		return $orders;
	}
	
	public function getHistoryForYear($year)
	{
		// URI: gp/css/order-history?opt=ab&orderFilter=year-{year}
		$raw = $this->_request->getUri("gp/css/order-history?opt=ab&orderFilter=year-{$year}");
		
		// Now we parse the page and build a list of orders
		$orders = array();
		$dom = new Query($raw);
		
		// Parse order boxes so we can parse information per order
		$orderBoxes = $dom->query('.action-box');
		$i = 0;
		foreach ($orderBoxes as $orderBox)
		{
			$innerHtml = $orderBox->ownerDocument->saveXML($orderBox);
			$innerDom = new Query($innerHtml);
			
			// Parse order dates
			$dates = $innerDom->query('.action-box .order-level h2');
			foreach ($dates as $date)
			{
				$orders[$i]['date'] = new DateTime($date->nodeValue);
			}
			
			// Parse order number
			$numbers = $innerDom->query('.action-box .order-level .order-details li .info-data a');
			foreach ($numbers as $number)
			{
				$orders[$i]['number'] = $number->nodeValue;
			}
			
			// Parse order price
			$prices = $innerDom->query('.action-box .order-level .order-details li .price');
			foreach ($prices as $price)
			{
				$orders[$i]['price'] = substr($price->nodeValue, 1);
			}
			
			// Parse order items
			$items = $innerDom->query('.order-bar .ship-contain .shipment li > a span');
			$orders[$i]['items'] = array();
			foreach ($items as $item)
			{
				$item = trim($item->nodeValue);
				if ($item)
				{
					$orders[$i]['items'][] = $item;
				}
			}
			
			$i++;
		}
		
		// While we have this document open, parse the years
		$yearDom = $dom->query('select#orderFilter option');
		$years = array();
		foreach ($yearDom as $option)
		{
			$year = trim($option->nodeValue);
			if (preg_match('/Orders placed in (\d{4})/', $year, $matches))
			{
				$years[] = $matches[1];
			}
		}
		
		return array('orders' => $orders, 'years' => $years);
	}
	
	/**
	 * Sets the request object.
	 * 
	 * @since  1.0
	 * @param  \Blake\Amazon\Request $request
	 * @throws \Exception 
	 */
	public function setRequest($request)
	{
		if (!$request instanceof Request)
		{
			throw new \Exception('Request must be of type \Blake\Amazon\Request');
		}
		
		$this->_request = $request;
	}
}