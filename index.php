<?php

// Program input
$blacklistFilename = 'global.fsd.xml'; // Blacklist XML file from http://www.basistech.com/text-analytics/rosette/name-indexer/#
$noisewordsFilename = 'noise.txt';

// Program run order
$noisewords = importNoiseWords($noisewordsFilename);
$blacklist = importBlacklistXML($blacklistFilename);

function importBlacklistXML($filename) {

	// Load XML file and format into array
	$xmlContents = file_get_contents('global.fsd.xml');
	$xmlString = simplexml_load_string($xmlContents, "SimpleXMLElement", LIBXML_NOCDATA);
	$dataJSON = json_encode($xmlString);
	$dataArray = json_decode($dataJSON,TRUE);

	// Fetch blacklisted names from complex multidimensional array
	$blacklist = array();
	foreach ($dataArray['sanctionEntity'] as $personData) {
		$nameAlias = $personData['nameAlias'];
		// Does person have multiple nameAliases?
		if (array_key_exists('@attributes', $nameAlias)) {
			array_push($blacklist, sanitizeName($nameAlias['@attributes']['wholeName']));
		} else {
			foreach ($nameAlias as $aliasArrayItem) {
				array_push($blacklist, sanitizeName($aliasArrayItem['@attributes']['wholeName']));
			}
		}
	}

	return $blacklist;
}

function importNoiseWords($filename) {
	$contents = file_get_contents($filename);
	$contents = strtolower($contents); // unify with matching
	$wordsArray = explode("\n", $contents);
	return $wordsArray;
}

function sanitizeName($name) {
	global $noisewords;

	// Force lowercase
	$name = strtolower($name);

	////// Array manipulation ////////
	$nameArray = explode(" ", $name);

	// Remove noise words
	$nameArray = array_diff($nameArray, $noisewords);

	// Sort names a-z
	sort($nameArray);

	$name = implode(" ", $nameArray);
	////// Array manipulation END ////////

	return $name;
}

print '<pre>';
print_r($blacklist);
print '</pre>';

?>
