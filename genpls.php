#!/usr/bin/php
<?php
define('URIS_PER_STREAM', 2);
define('INPUTFILE', __DIR__.'/streams.json');

function streamuris_di($id) {
	$uris=array();
	for($i=1; $i<=URIS_PER_STREAM; $i++) {
		$uris[]="http://pub$i.di.fm/$id";
	}
	return $uris;
}
function streamuris_aol($id) {
	$uris=array();
	for($i=1; $i<=URIS_PER_STREAM; $i++) {
		$uris[]="http://scfire-dtc-aa0$i.stream.aol.com/stream/$id";
	}
	return $uris;
}

$streams=array(null);
unset($streams[0]); // Let the index start at position 1

$input=json_decode(file_get_contents(INPUTFILE));
if(!$input) {
	die('Failed to read streams from input file');
}
usort($input, function($a, $b) {
	return strcmp($a->name, $b->name);
});
foreach($input as $item) {
	if(!isset($item->id)) {
		$item->id='di_'.str_replace(' ', '', strtolower($item->name));
	}
	if(is_int($item->id)) {
		$uris=streamuris_aol($item->id);
	}
	else {
		$uris=streamuris_di($item->id);
	}
	
	foreach($uris as $uri) {
		$streams[]=array(
			$uri,
			$item->name
		);
	}
}

$output=new SplFileObject('digitally-imported.pls', 'w');
$output->fwrite("[playlist]\n");
$output->fwrite("NumberOfEntries=".count($streams)."\n");

foreach($streams as $index=>$stream) {
	$output->fwrite("File$index={$stream[0]}\n");
	$output->fwrite("Title$index={$stream[1]}\n");
	$output->fwrite("Length$index=-1\n");
}

