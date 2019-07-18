<?PHP
	require_once("class.omeka.wp.php");

	$omeka_wp = new omeka_wp();
	$omeka_wp->database_connect();

	$omeka_wp->get_users();

/*

	echo "<br /><br />CATEGORIES <br /><br />";

	$omeka_wp->get_categories();

	echo "<br /><br />TAGS <br /><br />";

	$omeka_wp->get_tags();

	echo "<br /><br />PAGES <br /><br />";	

	$omeka_wp->get_pages();	

	echo "<br /><br />POSTS <br /><br />";	

	$omeka_wp->get_entry_data();

*/