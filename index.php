<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link type="text/css" rel="stylesheet" href="jesp.css" />
<title>Join, Evaluate, and Sort Tables</title>
</head>
<body>

<?php
require_once 'Expression.php';

# https://stackoverflow.com/questions/14752470/creating-a-config-file-in-php
# https://stackoverflow.com/questions/10148328/php-is-include-function-secure
# https://stackoverflow.com/questions/14614866/nested-arrays-in-ini-file/14614942
# ==> use json as config file

$err_log = '';

$config = array_key_exists('c', $_GET) ?  $_GET['c'] : "config.json";
# https://stackoverflow.com/questions/6224330/understanding-nested-php-ternary-operator

$config = json_decode(file_get_contents($config), TRUE);
if (! array_key_exists('title', $config))
    $config['title'] = 'Join, Evaluate, Sort, and Print Tables';
echo "<h1 style='text-align: center'>$config[title]</h1>\n\n";
?>

<div class="center-block" id="canvas" style="width: 800px; height: 0px;"></div>

<?php
if (! array_key_exists('keyprefix', $config))
    $config['keyprefix'] = '';

# main dictionary $MD
$MD = array();
foreach ($config['csvfiles'] as $csvfn) {
    $MD = join_csv($MD, $csvfn);
}

# echo "<pre>\n";
# echo "MD:\n";
# print_r($MD);
# echo "</pre>\n";

$N = count($config['col']);
for ($i=0; $i<$N; ++$i) {
    if (! array_key_exists('var', $config['col'][$i]))
	$config['col'][$i]['var'] = $config['col'][$i]['name'];
    if (! array_key_exists('format', $config['col'][$i]))
	$config['col'][$i]['format'] = '%s';
}

# https://forums.windowssecrets.com/showthread.php/132566-PHP-weird-behavior-of-foreach
# https://stackoverflow.com/questions/8220399/php-foreach-pass-by-reference-last-element-duplicating-bug
#
# https://stackoverflow.com/questions/10687306/why-do-twitter-bootstrap-tables-always-have-100-width
echo "<table id='jesp_table' width=200px style='width: auto;' class='table table-striped table-bordered'>\n";
echo "<thead>\n<tr><th>排序 ";
foreach ($config['col'] as $col) echo "<th>$col[name] ";
echo "\n</thead>\n<tbody>\n";
foreach ($MD as $pkey => $row) {
    # https://stackoverflow.com/questions/13036160/phps-array-map-including-keys
    # ok, I give up using array_merge and array_map

    # pass 1: init variables
    foreach ($config['col'] as $col) {
	if (array_key_exists('var', $col) && array_key_exists($col['name'], $row)) {
	    $row[$col['var']] = $row[$col['name']];
	}
    }
    # pass 2: eval expressions
    foreach ($config['col'] as $col) {
	if (in_array($col, $config['textcols'])) continue;
	if (array_key_exists('expr', $col)) {
	    $row[$col['var']] = myeval($col['expr'], $row);
	}

	if (! array_key_exists($col['var'], $row))
	    continue 2;
	    # data is missing from certain files e.g. pkey=="s1469" is missing from 181123.csv

	if (preg_match('/\bnan\b/i', $row[$col['var']]))
	    continue 2;
    }
    if (! myeval($config['keep'], $row)) continue;
    echo("<tr><td> ");
    foreach ($config['col'] as $col) {
	printf("<td>$col[format] ", $row[$col['var']]);
    }
    echo("\n");
}

echo "</tbody>\n</table>";

function join_csv($table, $csvfn) {
    global $config;
    $F = fopen($csvfn, 'r');
    $colnames = array_map("trim", fgetcsv($F, 999, ","));
    $NC = count($colnames);
    while ($cols = fgetcsv($F, 999, ",")) {
	if (preg_match('/^#/', $cols[0])) continue;
	$row = array();
	for ($i=0; $i<$NC; ++$i) {
	    $v = $cols[$i];
	    if ($colnames[$i] === $config['pkey']) {
		$row[$colnames[$i]] = "$config[keyprefix]$v";
	    } elseif (in_array($colnames[$i], $config['textcols'])) {
		$row[$colnames[$i]] = $v;
	    } else {
		$row[$colnames[$i]] = is_numeric($v) ? $v : NAN;
	    }
	}
	$pkey = $row[$config['pkey']];
	$table[$pkey] = array_key_exists($pkey, $table) ?  array_merge($table[$pkey], $row) : $row;

# for debugging...
#	if (preg_match('/^s11/', $pkey))
#	    print_r($table[$pkey]);
    }
    fclose($F);
    return $table;
}

function myeval($expr, $dict) {
    $allowed = array('abs', 'sqr');
    if (array_key_exists($expr, $dict))
	return $dict[$expr];
    preg_match_all('/\b[a-z]\w+\b/', $expr, $m);
    foreach ($m[0] as $v) {
	if (array_key_exists($v, $dict)) {
	    if (preg_match('/\bnan\b/i', $dict[$v]))
		return NAN;
	    $expr = preg_replace("/$v/", '('.$dict[$v].')', $expr);
	} elseif (in_array($v, $allowed)) {
	} else {
	    return NAN;
	}
    }
    $safeeval = new Expression();
    return $safeeval->evaluate($expr);
}

echo $err_log;

?>

<!--
https://datatables.net/download/
bootstrap3, jQuery3, Bootstrap3, DataTables, FixedHeaders
Minify+Concatenate
-->

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jq-3.3.1/dt-1.10.18/fh-3.1.4/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jq-3.3.1/dt-1.10.18/fh-3.1.4/datatables.min.js"></script>
  <script type="text/javascript" src="https://cdn.plot.ly/plotly-basic-latest.min.js" ></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/URI.js/1.19.1/URI.js" ></script>

  <script type="text/javascript" src="jesp.js"> </script>
</body>
</html>
