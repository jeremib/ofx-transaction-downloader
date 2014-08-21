<?
	require_once(__DIR__.'/OFX.php');
	
	$finance = new Finance();
	$finance->banks['amex'] = new Bank($finance, '24591', 'https://service2.usaa.com/ofx/OFXServlet', 'USAA');
	$finance->banks['amex']->logins[] = new Login($finance->banks['amex'], '18106150', '2918');
	
	foreach($finance->banks as $bank) {
		foreach($bank->logins as $login) {
			$login->setup();
			foreach($login->accounts as $account) {
				$account->setup();
			}
		}
	}

	print "<pre>".print_r($finance, 1)."</pre>";
?>
