<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - numbers.php
 */

if (!function_exists("formatNumber")) {
	/**
	 * Format a number following the app's locale
	 *
	 * @param $number
	 * @return string
	 */
	function formatNumber($number): string {
		$locale  = \Illuminate\Support\Facades\App::currentLocale();
		$nbrfmtr = new NumberFormatter($locale, NumberFormatter::DECIMAL);

		if ($locale === 'fr') {
			$nbrfmtr->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, ' ');
		}

		$output = $nbrfmtr->format($number);
		return str_replace(" ", " ", $output);
	}
}

if (!function_exists("formatCurrency")) {
	/**
	 * Format a number as currency following the app's locale
	 *
	 * @param $number
	 * @return array|false|string|string[]
	 */
	function formatCurrency($number) {
		$locale  = \Illuminate\Support\Facades\App::currentLocale();
		$nbrfmtr = new NumberFormatter($locale, NumberFormatter::CURRENCY);

		$nbrfmtr->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '$');

		$output = $nbrfmtr->format($number);

		// Remove decimals if zero.
		return str_replace([
			                   $nbrfmtr->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL) . "00",
			                   " ",
		                   ], ["", " "], $output);
	}
}
