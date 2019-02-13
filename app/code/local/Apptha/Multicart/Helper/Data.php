<?php
/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * 
 * Apptha does not guarantee correct work of this extension
 * 
 * on any other Magento edition except Magento COMMUNITY edition.
 * 
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Multicart
 * @version      1.0
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2015 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 * 
 */
class Apptha_Multicart_Helper_Data extends Mage_Core_Helper_Abstract{
/**
 * Determine whether the extension is enabled
 *
 * @return bool
 */
	/**
	 * License key
	 *
	 * Return license key given or not
	 *
	 * @return int
	 */
	public function checkMulticartKey() {
		 $apikey = Mage::getStoreConfig ( 'multicart/catalog/license_key' );
		 $multicartApiKey = $this->multicartApiKey ();
		
		 if ($apikey != $multicartApiKey) {
		 	return  false;
		 }
		 return true;
	}
	
	/**
	 * Get License Text
	 */
	public function getLicenseText(){
	return base64_decode('PGEgc3R5bGU9ImNvbG9yOnJlZDsiIGhyZWY9Imh0dHA6Ly93d3cuYXBwdGhhLmNvbS9jaGVja291dC9jYXJ0L2FkZC9wcm9kdWN0LzE4NCIgdGFyZ2V0PSJfYmxhbmsiPiAtIEJ1eSBub3c8L2E+');
	}
	/**
	 * Function to get the license key
	 *
	 * Return generated license key
	 *
	 * @return string
	 */
	
	public function multicartApiKey() {
		$code = $this->genenrateOscdomain ();
		return substr ( $code, 0, 25 ) . "CONTUS";
	}
	/**
	 * Function to get the domain key
	 *
	 * Return domain key
	 *
	 * @return string
	 */
	
	public function domainKey($tkey) {
		$message = "EM-SCARTMP0EFIL9XEV8YZAL7KCIUQ6NI5OREH4TSEB3TSRIF2SI1ROTAIDALG-JW";
		$stringLength = strlen ( $tkey );
		for($i = 0; $i < $stringLength; $i ++) {
			$keyArray [] = $tkey [$i];
		}
		$encMessage = "";
		$kPos = 0;
		$charsStr = "WJ-GLADIATOR1IS2FIRST3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0";
		$strLen = strlen ( $charsStr );
		for($i = 0; $i < $strLen; $i ++) {
			$charsArray [] = $charsStr [$i];
		}
		$lenMessage = strlen ( $message );
		$count = count ( $keyArray );
		for($i = 0; $i < $lenMessage; $i ++) {
			$char = substr ( $message, $i, 1 );
			$offset = $this->getOffset ( $keyArray [$kPos], $char );
			$encMessage .= $charsArray [$offset];
			$kPos ++;
	
			if ($kPos >= $count) {
				$kPos = 0;
			}
		}
		return $encMessage;
	}
	/**
	 * Function to get the offset for license key
	 *
	 * Return offset key
	 *
	 * @return string
	 */
	
	public function getOffset($start, $end) {
		$charsStr = "WJ-GLADIATOR1IS2FIRST3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0";
		$strLen = strlen ( $charsStr );
		for($i = 0; $i < $strLen; $i ++) {
			$charsArray [] = $charsStr [$i];
		}
		for($i = count ( $charsArray ) - 1; $i >= 0; $i --) {
			$lookupObj [ord ( $charsArray [$i] )] = $i;
		}
		$sNum = $lookupObj [ord ( $start )];
		$eNum = $lookupObj [ord ( $end )];
		$offset = $eNum - $sNum;
		if ($offset < 0) {
			$offset = count ( $charsArray ) + ($offset);
		}
		return $offset;
	}
	/**
	 * Function to generate license key
	 *
	 * Return license key
	 *
	 * @return string
	 */
	
	public function genenrateOscdomain() {
		$subfolder = $matches = '';
		$strDomainName = Mage::app ()->getFrontController ()->getRequest ()->getHttpHost ();
		preg_match ( "/^(http:\/\/)?([^\/]+)/i", $strDomainName, $subfolder );
		preg_match ( "/^(https:\/\/)?([^\/]+)/i", $strDomainName, $subfolder );
		preg_match ( "/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $subfolder [2], $matches );
		if (isset ( $matches ['domain'] )) {
			$clientUrl = $matches ['domain'];
		} else {
			$clientUrl = "";
		}
		$clientUrl = str_replace ( "www.", "", $clientUrl );
		$clientUrl = str_replace ( ".", "D", $clientUrl );
		$clientUrl = strtoupper ( $clientUrl );
		if (isset ( $matches ['domain'] )) {
			$response = $this->domainKey ( $clientUrl );
		} else {
			$response = "";
		}
		return $response;
	}
}
