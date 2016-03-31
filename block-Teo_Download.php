<?php

/*!
 *    Teo Downloads
 *  -------------------------------------------------------
 *    
 *    A simple block that lists out by default 5 downloads by last uploaded
 *    and 5 downloads by the number of hits it has received.
 *    
 *    @author        Teo
 *    @copyright     (c) 2012 Teo. All Rights Reserved
 *    @date          10 Sep, 2012
 *    @license       GNU/GPL (General Public License)
 *    @notes         n/a
 */

if (!defined('NUKE_EVO')) {
	exit('You can\'t access this file directly');
}

global $db, $prefix;

/*
$content .= "<style>
#tabella {
border:1px solid #161616;
background:#161616;
border-radius:5px;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
-khtml-border-radius:5px;margin:2px;
padding:2px;}
</style>";
*/

//
// Builds a database query based on the parameters given and constructs the required
// HTML to output on the page
//
function buildToHtml($orderBy, $total) {
	global $db, $prefix;

	// Set some variables for use below
	$output  = '';
	$counter = 1;

	// Construct the MySQL query to run for the downloads collection
	$res = $db->sql_query('SELECT c.title AS categoria, d.lid, d.cid, d.title AS titolo, d.date, d.hits, d.submitter, d.filesize FROM ' . $prefix . '_downloads_categories c LEFT JOIN ' . $prefix . '_downloads_downloads d ON d.cid = c.cid WHERE d.active = 1 ORDER BY d.' . $orderBy . ' DESC LIMIT 0, ' . $total);

	if ($db->sql_numrows($res)) {
		while ($row = $db->sql_fetchrow($res)) {
			// Remove any underscores from the download title
			$title = str_replace('_', ' ', $row['titolo']);
			$category = str_replace('_', ' ', $row['categoria']);
			$date = $row['date'];
			$time = date('F j, Y', strtotime($date));
			
			// Build the table element for the download
			$output .= '<tr>';
			$output .= '    <td width="100%"><div id="tabella">';
			$output .=          $counter . ': <a href="modules.php?name=Downloads&amp;op=getit&amp;lid=' . $row['lid'] . '" title="' . $title . '"><strong>' . $title . '</strong></a> ';
			$output .= '        <span style="color: #32ff00;">[' . $row['hits'] . ' hits]</span></small><br />';
			$output .= '	    <small>in <a href="modules.php?name=Downloads&cid=' . $row['cid'] . '" title="' . $category. '"><i>' . $category . ' </i></a>Category<br />';
			$output .= '        Added on '. $time .' by ' . $row['submitter'] . '<br />';
			
			$output .= '    </td></div>';
			$output .= '</tr>';

			// Increment the counter
			$counter++;
		}
	}

	$db->sql_freeresult($res);
	return $output;
}


//
// Set some configuration options
//
$total = 5;


//
// Find out how much bandwidth has been used overall for every download in the
// database
//
$res          = $db->sql_query('SELECT sum(filesize * hits) AS served FROM ' . $prefix . '_downloads_downloads WHERE active = 1');
list($served) = $db->sql_fetchrow($res);
$db->sql_freeresult($res);

$gb = 1024 * 1024 * 1024;
$mb = 1024 * 1024;
$kb = 1024;

if ($served >= $gb) {
	$bandwidth = sprintf("%01.2f", $served / $gb) . ' Gb/s';
} else if ($served >= $mb) {
	$bandwidth = sprintf("%01.2f", $served / $mb) . ' Mb/s';
} else if ($served >= $kb) {
	$bandwidth = sprintf("%01.2f", $served / $kb) . ' Kb/s';
} else {
	$bandwidth = $served . ' B/s';
}


//
// Get the total number of files that are currently active in the database
//
$res   = $db->sql_query('SELECT lid FROM ' . $prefix . '_downloads_downloads WHERE active = 1');
$files = $db->sql_numrows($res);
$db->sql_freeresult($res);


//
// Get the total number of hits for every active file in the database
//
$res  = $db->sql_query('SELECT hits FROM ' . $prefix . '_downloads_downloads WHERE active = 1');
$hits = 0;

while (list($totalHits) = $db->sql_fetchrow($res)) {
	$hits = $hits + $totalHits;
}

$db->sql_freeresult($res);


//
// Get the latest uploaded files that are currently active in the database
//
$latest = buildToHtml('date', $total);


//
// Get the hottest uploaded files that are currently active in the database
//
$popular = buildToHtml('hits', $total);


//
// Finally construct the HTML to output
//

$content .= '<table width="100%" border="0">';
$content .= '    <tr>';
$content .= '        <td><a href="modules.php?name=Downloads&amp;d_op=NewDownloads"><h4>Newest ' . $total . ' Downloads</h4></a></td>';
$content .= '        <td><a href="modules.php?name=Downloads&amp;d_op=MostPopular"><h4>Popular ' . $total . ' Downloads</h4></a></td>';
$content .= '    </tr>';
$content .= '    <tr>';
$content .= '        <td width="50%">';
$content .= '            <table width="100%" border="0">';
$content .=                  $latest;
$content .= '            </table>';
$content .= '        </td>';
$content .= '        <td width="50%">';
$content .= '            <table width="100%" border="0">';
$content .=                  $popular;
$content .= '            </table>';
$content .= '        </td>';
$content .= '    </tr>';
$content .= '    <tr>';
$content .= '        <td width="50%">';
//$content .= '            <hr/>';
$content .= '            <strong>File Available:</strong>' . $files . ' - <strong>Total Downloads:</strong> ' . $hits;
$content .= '        </td>';
$content .= '        <td width="50%">';
//$content .= '            <hr/>';
$content .= '            <strong>Downloads Generated:</strong> ' . $bandwidth . ' of Traffic';
$content .= '        </td>';
$content .= '    </tr>';
$content .= "</tr>";
$content .= "</table>";
$content .= "<table width=\"100%\" border=\"0\">";
$content .= "<tr>\n";
$content .= "<td align=\"right\" Valign=\"bottom\" colspan=\"3\" height=\"18\">\n";
$content .= "                <span class=\"gensmall\">&copy; <a href=\"http://www.sof2.org\">Teo</a></span></a></td>\n";
$content .= "</tr>\n";

$content .= '</table>';