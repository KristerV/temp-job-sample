<?php

// Program input
$blacklistFilename = 'global.fsd.xml';          // Blacklist XML file from
$noisewordsFilename = 'noise.txt';              // Noisewords text
$nameSimilarityTolerance = 75;                  // Names match similarity tolerance in percent
$namesToCheck = array(
	"Osama Bin Laden",                          // Where is he?
	"saddam AL-tikrIti huSsein",                // Exact match (regardless of word order)
	"Peeter Pendel",                            // No match (just double checking)
	"Master of Command Gabriel robert Mugabe",  // Noiseword test
	"abid bid hamid ha",                        // Names are closely similar
	"abid !,,..#.,,,bid h'a'm'i'd ha",          // Heavy punctuation
	"f. islamice salvării");                    // Abbreviated names

// Program run order
$noisewords = importNoiseWords($noisewordsFilename);
$blacklist = importBlacklistXML($blacklistFilename);
printMatches($namesToCheck);

// Print blacklist for debugging purposes
print '<h3>Imported blacklist</h3>';
print '<pre>Source: http://www.basistech.com/text-analytics/rosette/name-indexer/#<br/>';
print_r($blacklist);
print '</pre>';


function importNoiseWords($filename) {
	$contents = file_get_contents($filename);
	$contents = strtolower($contents);      // Matching based on lowercase
	$wordsArray = explode("\n", $contents); // Each word is on newline
	return $wordsArray;
}

function importBlacklistXML($filename) {

	// Load XML file and format into array
	$xmlContents = file_get_contents('global.fsd.xml');
	$xmlString = simplexml_load_string($xmlContents, "SimpleXMLElement", LIBXML_NOCDATA);
	$dataJSON = json_encode($xmlString);
	$dataArray = json_decode($dataJSON,TRUE);

	// Extract names from complex multidimensional array
	$blacklist = array();
	foreach ($dataArray['sanctionEntity'] as $personData) {
		$nameAlias = $personData['nameAlias'];
		// Does person have multiple nameAliases?
		if (array_key_exists('@attributes', $nameAlias)) {
			// NO; Can access wholeName value
			array_push($blacklist, sanitizeName($nameAlias['@attributes']['wholeName']));
		} else {
			// YES; Loop through aliases to access wholeName value
			foreach ($nameAlias as $aliasArrayItem) {
				array_push($blacklist, sanitizeName($aliasArrayItem['@attributes']['wholeName']));
			}
		}
	}

	return $blacklist;
}


function sanitizeName($name) {
	global $noisewords;

	// Force lowercase
	$name = strtolower($name);

	// Remove punctuation
	$name = preg_replace("/[!?\.,'#@]/", '', $name);

	// Prepare for array manipulation
	$nameArray = preg_split('/\s+/', $name);

	// Remove noise words
	$nameArray = array_diff($nameArray, $noisewords);

	// Sort names a-z
	sort($nameArray);

	$name = implode(" ", $nameArray);

	return $name;
}

function printMatches($list) {
	global $blacklist;
	global $nameSimilarityTolerance;
	print("<h3>Checking names</h3>");

	foreach ($list as $name) {
		print("<p>$name");
		$sanitized = sanitizeName($name);

		// Check similarity for each blacklist item
		foreach ($blacklist as $blackName) {
			similar_text($sanitized, $blackName, $matchPercent);
			if ($matchPercent > $nameSimilarityTolerance) {
				print_r(" <span style='color: red'>MATCH</span>");
				break;
			}
		}
		print("</p>");
	}
}

?>
