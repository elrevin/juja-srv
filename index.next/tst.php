<?php
$m = [];
print_r (preg_match("%^(?![\\w-_/]+_filter_)[\\w-_/]*%i", 'ttt/n/_flter_/sdfsdf', $m));
print_r($m);