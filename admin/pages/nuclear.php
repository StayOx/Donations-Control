<div class='content'>

        <form action='show_donations.php?server_query' method='post' name='CUSTOMCOMMAND'>
            <font class='info'><?php echo $lang->nuclear[0]->query; ?></font><br>  
            <input type='text' size='40' name='COMMAND' class='searchBox' /><input type='submit' value='<?php echo $lang->nuclear[0]->submit; ?>'>
        </form>          


<?php
if (isset($_POST['COMMAND'])) {
	$sb = new SourceBans;
	$query = $_POST['COMMAND'];
	
	if(!$sb->queryServersResponse($query)){
		echo "<h1 class='error>".$lang->nuclear[0]->error."</h1>";
	}
	if (STATS) {
		@$log->stats("SQ");
	}
	$log->logAction(sprintf($lang->logmsg[0]->nuclear, $_SESSION['username'], $query));

	unset($sb);
}else{
	echo "<h3><u>".$lang->nuclear[0]->about."</u></h3>";
	echo "<p>".$lang->nuclear[0]->description."<p>";
	echo "<p>".$lang->nuclear[0]->warning."</p>";
}
?>

</div>
