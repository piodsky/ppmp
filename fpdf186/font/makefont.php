<?php
require_once(__DIR__ . '/makefont/makefont.php');

$ttfFile = __DIR__ . '/font/FREE3OF9.TTF';
$encoding = 'cp1252'; // Western European
$embed = true;
$subset = false;

MakeFont($ttfFile, $encoding, $embed, $subset);