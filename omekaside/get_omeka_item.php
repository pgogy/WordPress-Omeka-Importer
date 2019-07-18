<?PHP
	require_once("class.omeka.wp.php");

	$omeka_wp = new omeka_wp();
	$omeka_wp->database_connect();

	$omeka_wp->get_entry_data($_GET['id']);	

