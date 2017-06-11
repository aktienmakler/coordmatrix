<?php
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="de-de" dir="ltr">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta charset="utf-8" />
	<title>Koordinaten-Matrix</title>
	<style>
		tr:nth-child(even) {background-color: #ddd;}
		// td:nth-child(even) {background-color: #f00;}
	</style>
</head>

<body>	

<form action="" method="POST">
<?php
// print_r($_REQUEST);
?>
<p>Fügen Sie hier eine formatierte Liste der GPS-Koordinaten und eines Bezeichners ein.</p>
<p>Beispiel</p>
<pre>Simsonbrunnen;50.876556806;12.083297968</pre>
<p>Als Zeichentrenner werden Tabs, "," und ";" erkannt.</p>
<ol>
	<li>Bezeichner</li>
	<li>Breitengrad (lat)</li>
	<li>Längengrad (lng)</li>
</ol>
<div><textarea name="rawdata" style="width:50%;"><?php echo $_REQUEST['rawdata'];?></textarea></div>
<div>
	<select name="delimiter">
		<option value="auto"<?php echo getHTMLSelected("delimiter", "auto");?>>Automatisch ermitteln</option>
		<option value="tab"<?php echo getHTMLSelected("delimiter", "tab");?>>Tab</option>
		<option value="comma"<?php echo getHTMLSelected("delimiter", "comma");?>>Komma</option>
		<option value="semicolon"<?php echo getHTMLSelected("delimiter", "semicolon");?>>Semikolon</option>
	</select>
</div>
<div>
	<select name="output">
		<option value="list"<?php echo getHTMLSelected("output", "list");?>>Liste</option>
		<option value="matrix"<?php echo getHTMLSelected("output", "matrix");?>>Matrix</option>
	</select>
</div>
<div>
	<button type="submit" name="absenden">Absenden</button>
	<button type="button" name="reset" onclick="this.form.elements['rawdata'].value='';">Reset</button>
</div>

<?php
if(!empty($_REQUEST['rawdata'])) {
	$liste = array();
	$c = explode("\n", $_REQUEST['rawdata']);
	switch($_REQUEST['delimiter']) {
		default:
		case "auto":
			$d = getDelimiter($c[0]);
			arsort($d);
			$d = array_keys($d);
			$d = $d[0];
		break;
		case "tab":
			$d = "\t";
		break;
		case "comma":
			$d = ",";
		break;
		case "semicolon":
			$d = ";";
		break;
	}

	foreach($c as $l) {
		$l = trim($l);
		if(!empty($l)) $liste[] = explode($d, $l);
	}
}

switch($_REQUEST['output']) {
	default:
	break;
	case 'list': 
		echo "<pre>";
		foreach($liste as $p1) {
			foreach($liste as $p2) {
				echo $p1[0]." -> ".$p2[0].":\t(".$p1[1].",".$p1[2].") -> (".$p2[1].",".$p2[2].")\t".distance($p1[1], $p1[2], $p2[1], $p2[2])."\t".bearing($p1[1], $p1[2], $p2[1], $p2[2])."\n";
			}
		}
		echo "</pre>";
	break;
	case "matrix":
?>
	<table style="">
		<thead>
			<tr>
				<th></th>
				<th colspan="<?php echo count($liste);?>">Ziel</th>
			</tr>
			<tr>
				<th>Distanz<br/>Winkel</th>
<?php
foreach($liste as $p1) {
	echo "<th>".$p1[0]."</th>\n";
}
?>
			</tr>
		</thead>
		<tbody style="overflow: scroll; width: 100%; height:200px;">
<?php

for($i=0; $i<count($liste); $i++) {
	$p1 = $liste[$i];
	echo "<tr>\n";
	echo "<th>".$p1[0]."</th>\n";
	for($j=0; $j<count($liste); $j++) {
		$p2 = $liste[$j];
		echo "<td class=\"matrix_".$i."_".$j."\">".round(distance($p1[1], $p1[2], $p2[1], $p2[2]),2)."<br/>".round(bearing($p1[1], $p1[2], $p2[1], $p2[2],2))."</td>\n";
	}
	echo "</tr>\n";
}
?>
		</tbody>
	</table>
<?php
	break;
}

function distance($dx1, $dy1, $dx2, $dy2) {
	return 6378388 * acos(sin(deg2rad($dx1)) * sin(deg2rad($dx2)) + cos(deg2rad($dx1)) * cos(deg2rad($dx2)) * cos(deg2rad($dy2) - deg2rad($dy1)));
}

function bearing($dx1, $dy1, $dx2, $dy2) {
	$rx1 = deg2rad($dx1);
	$ry1 = deg2rad($dy1);
	$rx2 = deg2rad($dx2);
	$ry2 = deg2rad($dy2);
	$dlng = deg2rad($dy2-$dy1);

	$y		= sin($dlng) * cos($rx2);
	$x		= cos($rx1)*sin($rx2) - sin($rx1)*cos($rx2)*cos($dlng);
	$brng	= atan2($y, $x);
	$brng	= rad2deg($brng);
	$brng	= fmod($brng + 360, 360);
	return $brng;
};

function getHTMLSelected($varname, $value) {
	if($_REQUEST[$varname]==$value) {
		return " selected=\"selected\"";
	}
}

function getDelimiter($line) {
	$cdelims = array();
	foreach(array("\t", ";", ",") as $d) {
		preg_match_all("|".$d."|", $line, $ausgabe);
		$cdelims[$d] = count($ausgabe[0]);
	}
	return $cdelims;
}
?>
</body>
</html>