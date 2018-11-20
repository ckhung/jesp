<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link type="text/css" rel="stylesheet" href="jest.css" />
<title>Join, Eval, and Sort Tables</title>
</head>
<body>

<h1 style="text-align: center">Join, Eval, and Sort Tables</h1>

<?php
require_once 'Expression.php';

# https://stackoverflow.com/questions/14752470/creating-a-config-file-in-php
# https://stackoverflow.com/questions/10148328/php-is-include-function-secure
# https://stackoverflow.com/questions/14614866/nested-arrays-in-ini-file/14614942
# ==> use json as config file

$config = array_key_exists('config', $_GET) ?  $_GET['config'] : 'config.json';
$config = json_decode(file_get_contents($config), TRUE);

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
	$config['col'][$i]['var'] = $config['col'][$i]['disp'];
    if (! array_key_exists('expr', $config['col'][$i]))
	$config['col'][$i]['expr'] = $config['col'][$i]['var'];
    if (! array_key_exists('format', $config['col'][$i]))
	$config['col'][$i]['format'] = '%s';
}

# https://forums.windowssecrets.com/showthread.php/132566-PHP-weird-behavior-of-foreach
# https://stackoverflow.com/questions/8220399/php-foreach-pass-by-reference-last-element-duplicating-bug
#
# https://stackoverflow.com/questions/10687306/why-do-twitter-bootstrap-tables-always-have-100-width
echo "<table id='jest_table' width=200px style='width: auto;' class='table table-striped table-bordered'>\n";
echo "<thead>\n<tr><th>排序 ";
foreach ($config['col'] as $col) echo "<th>$col[disp] ";
echo "\n</thead>\n\n<tbody>";
foreach ($MD as $pkey => $row) {
    # https://stackoverflow.com/questions/13036160/phps-array-map-including-keys
    # ok, I give up using array_merge and array_map

    # pass 1: init variables
    foreach ($config['col'] as $col) {
	if (array_key_exists('var', $col) and array_key_exists($col['disp'], $row)) {
	    $row[$col['var']] = $row[$col['disp']];
	}
    }
    # pass 2: eval expressions
    foreach ($config['col'] as $col) {
	if (array_key_exists('expr', $col)) {
	    $row[$col['var']] = myeval($col['expr'], $row);
	}
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
    $colnames = fgetcsv($F, 999, ",");
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

?>

  <link type="text/css" rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
  <link type="text/css" rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css" />
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"> </script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"> </script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"> </script>
  <script type="text/javascript" src="jest.js"> </script>
</body>
</html>
