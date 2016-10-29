<?php 
require_once 'app/Mage.php';
Mage::init();
$currencyModel = Mage::getModel('directory/currency');
$currencies = $currencyModel->getConfigAllowCurrencies();
$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
$defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
$rates=$currencyModel->getCurrencyRates($defaultCurrencies, $currencies);
$res_cur = '';
foreach($currencies as $toCode) {
	$url = 'http://www.google.com/finance/converter?a=1&from=_CODE_FROM_&to=_CODE_TO_';
	$url = str_replace('_CODE_FROM_', $baseCurrencyCode, $url);
    $url = str_replace('_CODE_TO_', $toCode, $url);	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	if(preg_match("'<span class=bld>([0-9\.]+)\s\w+</span>'", $res, $m)) {
		$res_cur[$baseCurrencyCode][$toCode] = $m[1];	
	}
	curl_close($ch);
	sleep(1);
}
$res_cur[$baseCurrencyCode][$baseCurrencyCode] = '1.0000';
foreach($rates[$baseCurrencyCode] as $CurrencyCode => $value  ) {
    $currencies = array($baseCurrencyCode => array($CurrencyCode => $res_cur[$baseCurrencyCode][$CurrencyCode]) );
    Mage::getModel('directory/currency')->saveRates($currencies); 
}
