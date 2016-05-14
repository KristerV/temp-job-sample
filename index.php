<?php

$blacklistFilename = 'global.fsd.xml';

function importBlacklistXML($filename) {
	$xmlContents = file_get_contents('global.fsd.xml');;
	$xml = simplexml_load_string($xmlContents, "SimpleXMLElement", LIBXML_NOCDATA);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);
	$blacklist = array();

	foreach ($array['sanctionEntity'] as $item) {
		$nameAlias = $item['nameAlias'];
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
