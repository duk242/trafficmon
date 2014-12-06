<?php
	// Traffic Mon Script

	// Open usage.db file and users.txt file
	// usage.db: One Line per MAC, CSV.  MAC -> Onpeak Down -> Onpeak Up -> Offpeak Down -> Offpeak Up -> Last Seen
	// users.txt: One Line per MAC, CSV. MAC -> ComputerName
	$usageDB = file_get_contents('/tmp/usage.db') or die("Unable to open usage database file");
	$users = file_get_contents('/config/scripts/users.txt');
	#$users = file_get_contents('users.txt');
	#$usageDB = file_get_contents('usage.db') or die("Unable to open usage database file");
	$usageLine = explode("\n", $usageDB);


	date_default_timezone_set('Australia/Sydney');
	$totalOnPeakUp = 0;
	$totalOnPeakDown = 0;
	$totalOffPeakDown = 0;
	$totalOffPeakUp = 0;
	$totalUsage = 0;

	// Parse each line
	for ($i=0; $i < count($usageLine); $i++) {
		if($usageLine) {	// Just incase there's a blank line
			// Explode into each part
			$usageLineExplode = explode(",", $usageLine[$i]);

			$usage[$i]['MAC'] = $usageLineExplode[0];
			# Use MAC to work out ComputerName
			if(preg_match_all("/".$usage[$i]['MAC'].",.*/", $users, $computerName)) {
				$nameSplit = explode(",", $computerName[0][0]);
				$usage[$i]['name'] = $nameSplit[1];
			} else {
				$usage[$i]['name'] = "Unknown";
			}


			// Total up the following as we go
			$usage[$i]['onpeakdown'] = $usageLineExplode[1];
			$totalOnPeakDown = $totalOnPeakDown + $usageLineExplode[1];
			$usage[$i]['onpeakup'] = $usageLineExplode[2];
			$totalOnPeakUp = $totalOnPeakUp + $usageLineExplode[2];
			$usage[$i]['offpeakdown'] = $usageLineExplode[3];
			$totalOffPeakDown = $totalOffPeakDown + $usageLineExplode[3];
			$usage[$i]['offpeakup'] = $usageLineExplode[4];
			$totalOffPeakUp = $totalOffPeakUp + $usageLineExplode[4];
			$usage[$i]['lastSeen'] = $usageLineExplode[5];

			$usage[$i]['total'] = $usage[$i]['onpeakdown'] + $usage[$i]['onpeakup'] + $usage[$i]['offpeakdown'] + $usage[$i]['offpeakup'];
			$totalUsage = $totalUsage + $usage[$i]['total'];
		}

	}
	// Sort Array from highest to lowest
	usort($usage, "comparer");
	function comparer($a, $b) {
		return strnatcasecmp($b['onpeakdown'], $a['onpeakdown']);
	}

	/// border: 1px white solid;

	// Draw pretty table
	print '<html><head><title>Traffic Monitor</title>
	<style type="text/css">
		.mainTable { width: 85%;  }
		.mainTable td { padding: 3px; }
		.mainTable tr:nth-child(odd) { background-color: #1e2225; }
		body { text-align: center; }
	</style>
	</head><body style="color: #3B96D8; background-color: #14171A;">
		<h1>Traffic Monitor</h1>
			<table class="mainTable"><tr style="background-color: #090b0c"><th>Name</th><th>OnPeak Down</th><th>OnPeak Up</th><th>OffPeak Down</th><th>OffPeak Up</th><th>Total</th><th>Last Seen</th></tr>'."\n";
	for ($i=0; $i < count($usage); $i++) {
		print '<tr><td><span style="font-size: large">'. $usage[$i]['name'] .'</span><br /><span style="font-size: small">'.$usage[$i]['MAC'].'</span></td><td>'.human_filesize($usage[$i]['onpeakdown']).'</td><td>'.human_filesize($usage[$i]['onpeakup']).'</td><td>'.human_filesize($usage[$i]['offpeakdown']).'</td><td>'.human_filesize($usage[$i]['offpeakup']).'</td><th>'. human_filesize($usage[$i]['total']) .'</th><td>';
		print  '<span style="font-size: large">'. time_elapsed(time() - strtotime($usage[$i]['lastSeen'])) .' ago</span><br /><span style="font-size: small;">'. $usage[$i]['lastSeen'] .'</span></td></tr>'."\n";
	}
	print '<tr style="text-align: left"><th>Totals</th><th>'.human_filesize($totalOnPeakDown).'</th><th>'.human_filesize($totalOnPeakUp).'</th><th>'.human_filesize($totalOffPeakDown).'</th><th>'.human_filesize($totalOffPeakUp).'</th><th>'.human_filesize($totalUsage).'</th><th>&nbsp;</th></tr>'."\n";
	print '</table></body></html>';

	function human_filesize($kilobytes, $decimals = 2) {
	    $size = array('KB','MB','GB','TB','PB','EB','ZB','YB');
	    $factor = floor((strlen($kilobytes) - 1) / 3);
	    return sprintf("%.{$decimals}f", $kilobytes / pow(1024, $factor)) . @$size[$factor];
	}

	function time_elapsed($secs){
	    $bit = array(
	        'y' => $secs / 31556926 % 12,
	        'w' => $secs / 604800 % 52,
	        'd' => $secs / 86400 % 7,
	        'h' => $secs / 3600 % 24,
	        'm' => $secs / 60 % 60,
	        's' => $secs % 60
	        );

	    foreach($bit as $k => $v)
	        if($v > 0)$ret[] = $v . $k;

	    return join(' ', $ret);
    }


?>