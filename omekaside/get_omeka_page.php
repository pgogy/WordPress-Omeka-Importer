<?PHP
	require_once("class.omeka.wp.php");

	$omeka_wp = new omeka_wp();
	$omeka_wp->database_connect();

	$omeka_wp->get_page($_GET['id']);	
/*
	echo "<br /><br />POSTS <br /><br />";	

	$omeka_wp->get_entry_data();

*/