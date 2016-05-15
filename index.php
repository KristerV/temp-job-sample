<?php

$blacklistFilename = 'global.fsd.xml'; // Blacklist XML file from http://www.basistech.com/text-analytics/rosette/name-indexer/#

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
			array_push($blacklist, $nameAlias['@attributes']['wholeName']);
		} else {
			foreach ($nameAlias as $aliasArrayItem) {
				array_push($blacklist, $aliasArrayItem['@attributes']['wholeName']);
			}
		}
	}

	return $blacklist;
}

$blacklist = importBlacklistXML($blacklistFilename);
print '<pre>';
print_r($blacklist);
print '</pre>';

?>
