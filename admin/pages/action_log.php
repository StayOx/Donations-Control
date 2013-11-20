<?php
if(file_exists("logs/action.log")){

$log = new log;
$error=array_reverse($log->getLog('action.log'));

echo "<table border='2'>";
echo "<tr><th>".$lang->action[0]->datetime."</th><th>".$lang->action[0]->actions."</th></tr>";
foreach ($error as $err) {
	$data = explode("|", $err);
	echo "<tr>";
	echo"<td>".$data[0]."</td>";
	echo"<td>".$data[1]."</td>";
	//echo"<td>".$data[2]."</td>";
	echo "</tr>";
}
echo "</table>";
unset($log);
}else{
	$_SESSION['message'] = "<h3 class='success'>".$lang->action[0]->noactions."</h3>";
	header("location: show_donations.php");
}
?>