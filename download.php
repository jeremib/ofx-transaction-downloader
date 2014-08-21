<?php

if ( !isset($_POST['login']) || !isset($_POST['password']) ) {
	die('Invalid login information');
}

ini_set('auto_detect_line_endings', true);
require_once('OFX/OFX.php');


$finance = new Finance();
$finance->banks['usaa'] = new Bank($finance, '24591', 'https://service2.usaa.com/ofx/OFXServlet', 'USAA');
$finance->banks['usaa']->logins[] = new Login($finance->banks['usaa'], $_POST['login'], $_POST['password']);


$transactions = array();

$rows = 0;
foreach($finance->banks as $bank) {
    foreach($bank->logins as $login) {
        $login->setup();
        foreach($login->accounts as $account) {
            $account->setup();
            foreach($account->transactions as $transaction) {
            	$transactions[] = array(
            		'account_number' => $account->id,
            		'type' => $transaction->type,
            		'date' => $transaction->datePosted->format("Y-m-d"),
            		'amount' => $transaction->amount,
            		'name' => $transaction->name,
            		'id' => $transaction->identifier
            		);
            }
        }
    }
}

download_send_headers("data_export_" . date("Y-m-d_h_i_s") . ".csv");
echo array2csv($transactions);
die();

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}