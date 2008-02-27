<?php

function showcalendar() {

global $imasroot,$cid;

$now= time();
$today = $now;
if (isset($_GET['calpageshift'])) {
	$pageshift = $_GET['calpageshift'];
} else {
	$pageshift = 0;
}
$today = $today + $pageshift*28*24*60*60;

$dayofweek = date('w',$today);
$dayofmo = date('j',$today);
$curmo = date('M',$today);
$curyr = date('Y',$today);
$curmonum = date('n',$today);
$daysinmo = date('t',$today);
$lastmo = date('M',$today - ($dayofmo+2)*24*60*60);
$lastmonum = date('n',$today - ($dayofmo+2)*24*60*60);
$daysinlastmo = date('t',$today - ($dayofmo+2)*24*60*60);
$nextmo = date('M',$today + ($daysinmo-$dayofmo+2)*24*60*60);
$nextmonum = date('n',$today + ($daysinmo-$dayofmo+2)*24*60*60);

$hdrs = array();
$ids = array();


for ($i=0;$i<$dayofweek;$i++) {
	$curday = $dayofmo - $dayofweek + $i;
	if ($curday<1) {
		if ($i==0) {
			$hdrs[0][$i] = $lastmo . " " . $daysinlastmo;
			$ids[0][$i] = $lastmonum.'-'.$daysinlastmo;
		} else {
			$hdrs[0][$i] = ($daysinlastmo + $curday);
			$ids[0][$i] = $lastmonum.'-'.($daysinlastmo + $curday);
		}
	} else {
		if ($i==0) {
			$hdrs[0][$i] = $curmo . " " . $curday;
			$ids[0][$i] = $curmonum.'-'.$curday;
		} else {
			$hdrs[0][$i] = $curday;
			$ids[0][$i] = $curmonum.'-'.$curday;
		}
	}
	$dates[$ids[0][$i]] = date('l F j, Y',$today - ($dayofweek - $i)*24*60*60);
}

for ($i=$dayofweek;$i<28;$i++) {
	$row = floor($i/7);
	$col = $i%7;
	$curday = $dayofmo -$dayofweek+ $i;
	if ($curday > $daysinmo) {
		if ($curday == $daysinmo+1) {
			$hdrs[$row][$col] = $nextmo." 1";
		} else {
			$hdrs[$row][$col] = $curday - $daysinmo;
		}
		$ids[$row][$col] = $nextmonum.'-'.($curday - $daysinmo);
	} else {
		$hdrs[$row][$col] = $curday;
		$ids[$row][$col] = $curmonum.'-'.$curday;
	}
	$dates[$ids[$row][$col]] = date('l F j, Y',$today + ($i-$dayofweek)*24*60*60);
}

?>
<script type="text/javascript"> 
function showcontents(el) {
	html = '';
	if (caleventsarr[el.id]!=null) {
		html += '<div style="background-color:#ddf;">'+caleventsarr[el.id].date + '</div>';
		if (caleventsarr[el.id].data!=null) {
			html += '<ul class=qview style="margin-top: 2px;">';
			for (var i=0; i<caleventsarr[el.id].data.length; i++) {
				if (caleventsarr[el.id].data[i].type=='A') {
					html += '<li><span style="background-color: #f66; padding: 0px 5px 0px 5px;">?</span> <a href="../assessment/showtest.php?cid='+cid+'&id='+caleventsarr[el.id].data[i].id+'">';
					html += caleventsarr[el.id].data[i].name + '</a>';
					html += ' Due '+caleventsarr[el.id].data[i].time;
					html += '</li>';
				} else if (caleventsarr[el.id].data[i].type=='I') {
					html += '<li><span style="background-color: #f66; padding: 0px 5px 0px 5px;">!</span> ';
					html += caleventsarr[el.id].data[i].name;
					html += '</li>';
				} else if (caleventsarr[el.id].data[i].type=='L') {
					html += '<li><span style="background-color: #f66; padding: 0px 5px 0px 5px;">!</span> ';
					if (caleventsarr[el.id].data[i].link=='') {
						html += '<a href="../course/showlinkedtext.php?cid='+cid+'&id='+caleventsarr[el.id].data[i].id+'">';
					} else {
						html += '<a href="'+caleventsarr[el.id].data[i].link+'">';
					}
					html += caleventsarr[el.id].data[i].name + '</a>';
					html += '</li>';
				}
			}
		}
	}
	html += '</ul>';
	document.getElementById('step').innerHTML = html;	
	var alltd = document.getElementsByTagName("td");
	for (var i=0;i<alltd.length;i++) {
		alltd[i].style.backgroundColor = '#fff';
	}
	el.style.backgroundColor = '#fdd';
}
</script>

<?php
echo '<div class=center><a href="course.php?calpageshift='.($pageshift-1).'&cid='.$cid.'">&lt; &lt;</a> ';
if ($pageshift==0) {
	echo "Now ";
} else {
	echo '<a href="course.php?calpageshift=0&cid='.$cid.'">Now</a> ';
}
echo '<a href="course.php?calpageshift='.($pageshift+1).'&cid='.$cid.'">&gt; &gt;</a></div> ';
echo "<table class=\"cal\" >";  //onmouseout=\"makenorm()\"

$lowertime = max($now,mktime(0,0,0,$curmonum,$dayofmo - $dayofweek,$curyr));
$uppertime = mktime(0,0,0,$curmonum,$dayofmo - $dayofweek + 28,$curyr);

$assess = array();
$query = "SELECT id,name,enddate FROM imas_assessments WHERE enddate>$lowertime AND enddate<$uppertime AND startdate<$now AND avail=1 AND courseid='$cid'";
$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
while ($row = mysql_fetch_row($result)) {
	list($moday,$time) = explode('~',date('n-j~g:i a',$row[2]));
	$row[1] = str_replace('"','\"',$row[1]);
	$assess[$moday][] = "{type:\"A\", time:\"$time\", id:\"$row[0]\", name:\"$row[1]\"}";//"<span class=icon style=\"background-color:#f66\">?</span> <a href=\"../assessment/showtest.php?id={$row[0]}&cid=$cid\">{$row[1]}</a> Due $time<br/>";
}
$query = "SELECT id,title,enddate FROM imas_inlinetext WHERE enddate>$lowertime AND enddate<$uppertime AND startdate<$now AND avail=1 AND courseid='$cid'";
$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
while ($row = mysql_fetch_row($result)) {
	list($moday,$time) = explode('~',date('n-j~g:i a',$row[2]));
	$row[1] = str_replace('"','\"',$row[1]);
	$assess[$moday][] = "{type:\"I\", time:\"$time\", id:\"$row[0]\", name:\"$row[1]\"}";//"<span class=icon style=\"background-color:#f66\">?</span> <a href=\"../assessment/showtest.php?id={$row[0]}&cid=$cid\">{$row[1]}</a> Due $time<br/>";
}
$query = "SELECT id,title,enddate,text FROM imas_linkedtext WHERE enddate>$lowertime AND enddate<$uppertime AND startdate<$now AND avail=1 AND courseid='$cid'";
$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
while ($row = mysql_fetch_row($result)) {
	list($moday,$time) = explode('~',date('n-j~g:i a',$row[2]));
	$row[1] = str_replace('"','\"',$row[1]);
	 if ((substr($row[3],0,4)=="http") && (strpos($row[3]," ")===false)) { //is a web link
		   $alink = trim($row[3]);
	   } else if (substr($row[3],0,5)=="file:") {
		   $filename = substr($row[3],5);
		   $alink = $imasroot . "/course/files/".$filename;
	   } else {
		   $alink = '';
	   }
	$assess[$moday][] = "{type:\"L\", time:\"$time\", id:\"$row[0]\", name:\"$row[1]\", link:\"$alink\"}";//"<span class=icon style=\"background-color:#f66\">?</span> <a href=\"../assessment/showtest.php?id={$row[0]}&cid=$cid\">{$row[1]}</a> Due $time<br/>";
}

$jsarr = '{';
foreach ($dates as $moday=>$val) {
	if ($jsarr!='{') {
		$jsarr .= ',';
	}
	if (isset($assess[$moday])) {
		$jsarr .= '"'.$moday.'":{date:"'.$dates[$moday].'",data:['.implode(',',$assess[$moday]).']}';
	} else {
		$jsarr .= '"'.$moday.'":{date:"'.$dates[$moday].'"}';
	}
}
$jsarr .= '}';
		
echo '<script>';
echo "var cid = $cid;";
echo "var caleventsarr = $jsarr;";
echo '</script>';
echo "<thead><tr><th>Sunday</th> <th>Monday</th> <th>Tuesday</th> <th>Wednesday</th> <th>Thursday</th> <th>Friday</th> <th>Saturday</th></tr></thead>";
echo "<tbody>";
for ($i=0;$i<count($hdrs);$i++) {
	echo "<tr>";
	for ($j=0; $j<count($hdrs[$i]);$j++) {
		if ($i==0 && $j==$dayofweek && $pageshift==0) { //onmouseover="makebig(this)"
			echo '<td id="'.$ids[$i][$j].'" onclick="showcontents(this)" class="today"><div class="td"><span class=day>'.$hdrs[$i][$j]."</span><div class=center>";
		
		} else {
			echo '<td id="'.$ids[$i][$j].'" onclick="showcontents(this)" ><div class="td"><span class=day>'.$hdrs[$i][$j]."</span><div class=center>";
		}
		if (isset($assess[$ids[$i][$j]])) {
			for ($k=0;$k<count($assess[$ids[$i][$j]]);$k++) {
				//echo $assess[$ids[$i][$j]][$k];
				if (strpos($assess[$ids[$i][$j]][$k],'type:"A"')!==false) {
					echo "<span style=\"background-color:#f66;padding: 0px 3px 0px 3px;\">?</span> ";
				} else {
					echo "<span style=\"background-color:#f66;padding: 0px 3px 0px 3px;\">!</span> ";
				}
			}
		}
		echo "</div></div></td>";
	}
	echo "</tr>";
}
echo "</tbody></table>";

echo "<div id=step style=\"margin-top: 10px; padding:10px; border:1px solid #000;\"></div>";
if ($pageshift==0) {
	echo "<script>showcontents(document.getElementById('{$ids[0][$dayofweek]}'));</script>";
}

}
?>
