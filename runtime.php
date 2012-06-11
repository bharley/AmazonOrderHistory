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

// Register the autoloader
include 'lib/Blake/Autoloader.php';
Blake\Autoloader::register();

// Create the request object
$req = new Blake\Amazon\Request(require 'config.php');
// Create an order history object
$orders = new Blake\Amazon\OrderHistory($req);

// Get order history
$history = $orders->getHistory();

// Create an Excel doc
require_once 'lib/PHPExcel.php';
$excel = new PHPExcel();

// Set the document properties
$excel->getProperties()->setCreator('Blake Harley');
$excel->getProperties()->setLastModifiedBy('Blake Harley');
$excel->getProperties()->setTitle('Amazon Order History');
$excel->getProperties()->setSubject('Amazon Order History');
$excel->getProperties()->setDescription('Amazon Order History');

// Start writing
$excel->setActiveSheetIndex(0);
$excel->getActiveSheet()->setTitle('Amazon Order History');
$headerStyle = array(
	'font' => array(
		'bold' => true,
	),
	'borders' => array(
		'outline' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
		'inside' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array(
			'rgb' => 'cccc98',
		),
	),
);
$excel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($headerStyle);

// Set column widths
$excel->getActiveSheet()->getColumnDimension('A')->setWidth(19.50);
$excel->getActiveSheet()->getColumnDimension('B')->setWidth(11.25);
$excel->getActiveSheet()->getColumnDimension('C')->setWidth(60.00);
$excel->getActiveSheet()->getColumnDimension('D')->setWidth(12.00);

// Set column headers
$excel->getActiveSheet()->setCellValue('A1', 'Order Number');
$excel->getActiveSheet()->setCellValue('B1', 'Date');
$excel->getActiveSheet()->setCellValue('C1', 'Item');
$excel->getActiveSheet()->setCellValue('D1', 'Price');

// Populate things
$i = 2;
foreach ($history as $order)
{
	$first = true;
	foreach ($order['items'] as $item)
	{
		if ($first)
		{
			$excel->getActiveSheet()->setCellValue("A$i", $order['number']);
			$excel->getActiveSheet()->setCellValue("B$i", $order['date']->format('d M Y'));
			$excel->getActiveSheet()->setCellValue("D$i", $order['price']);
		}
		$excel->getActiveSheet()->setCellValue("C$i", $item);
		
		$first = false;
		
		$i++;
	}
}
$excel->getActiveSheet()->setCellValue("D$i", '=SUM(D2:D'. ($i - 1) .')');
$excel->getActiveSheet()->getStyle("D2:D$i")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

// Output the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="amazon-history.xlsx"');
header('Cache-Control: max-age=0');
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
