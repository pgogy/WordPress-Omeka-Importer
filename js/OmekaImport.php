<?PHP

	/*
		Plugin Name: Omeka Import
		Description: Importing from Omeka into WordPress
		Author: pgogy
		Version: 0.1
	*/

	class OmekaImport{

		function __construct(){			
			if(is_admin()){
				add_action("admin_menu", array($this, "menu_create"));
				add_action('network_admin_menu', array($this, 'menu_create'));
				add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
			}
		}

		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="omeka_pages"){
					wp_register_style( 'omekaimport_file_css', plugins_url() . '/OmekaImport/css/omekaimport-file.css', false, '1.0.0' );
					wp_enqueue_style( 'omekaimport_file_css' );
					wp_enqueue_script( 'omeka-import-page', plugins_url() . '/OmekaImport/js/omekaimport-file.js', array( 'jquery' ) );
					wp_localize_script( 'omeka-import-page', 'omeka_import_page', 
														array( 
															'ajaxURL' => admin_url("admin-ajax.php"),
															'nonce' => wp_create_nonce("omeka_import_page")
														) 
					);
				}
				if($_GET['page']=="omeka_items"){
					wp_register_style( 'omekaimport_item_css', plugins_url() . '/OmekaImport/css/omekaimport-item.css', false, '1.0.0' );
					wp_enqueue_style( 'omekaimport_item_css' );
					wp_enqueue_script( 'omeka-import-items', plugins_url() . '/OmekaImport/js/omekaimport-item.js', array( 'jquery' ) );
					wp_localize_script( 'omeka-import-items', 'omeka_import_item', 
														array( 
															'ajaxURL' => admin_url("admin-ajax.php"),
															'nonce' => wp_create_nonce("omeka_import_item")
														) 
					);
				}
				if($_GET['page']=="omeka_files"){
					wp_register_style( 'omekaimport_media_css', plugins_url() . '/OmekaImport/css/omekaimport-media.css', false, '1.0.0' );
					wp_enqueue_style( 'omekaimport_media_css' );
					wp_enqueue_script( 'omeka-import-media', plugins_url() . '/OmekaImport/js/omekaimport-media.js', array( 'jquery' ) );
					wp_localize_script( 'omeka-import-media', 'omeka_import_media', 
														array( 
															'ajaxURL' => admin_url("admin-ajax.php"),
															'nonce' => wp_create_nonce("omeka_import_media")
														) 
					);
				}
			
			}
		}

		function menu_create(){
			add_menu_page( __("Omeka Import"), __("Omeka Import"), "manage_options", "omeka_import", array($this,"import_main"));
			add_submenu_page( "omeka_import", __("Get users"), __("Get users"), "manage_options", "omeka_users", array($this, "import_users") );
			add_submenu_page( "omeka_import", __("Get categories"), __("Get Categories"), "manage_options", "omeka_categories", array($this, "import_categories") );
			add_submenu_page( "omeka_import", __("Get tags"), __("Get tags"), "manage_options", "omeka_tags", array($this, "import_tags") );
			add_submenu_page( "omeka_import", __("Get pages"), __("Get pages"), "manage_options", "omeka_pages", array($this, "import_pages") );
			add_submenu_page( "omeka_import", __("Get items"), __("Get items"), "manage_options", "omeka_items", array($this, "import_items") );
			add_submenu_page( "omeka_import", __("Get files"), __("Get files"), "manage_options", "omeka_files", array($this, "import_files") );
		}

		function import_main(){
			?>
				<h2><?PHP echo __("Using the Importer"); ?></h2>
				<p><?PHP echo __("Using the Importer in the following order"); ?></p>
				<ol>
					<li><a href="<?PHP echo admin_url("admin.php?page=omeka_users"); ?>"><?PHP echo __("Users"); ?></a></li>
					<li><a href="<?PHP echo admin_url("admin.php?page=omeka_categories"); ?>"><?PHP echo __("Categories"); ?></a></li>
					<li><a href="<?PHP echo admin_url("admin.php?page=omeka_tags"); ?>"><?PHP echo __("Tags"); ?></a></li>
					<li><a href="<?PHP echo admin_url("admin.php?page=omeka_pages"); ?>"><?PHP echo __("Pages"); ?></a></li>
					<li><a href="<?PHP echo admin_url("admin.php?page=omeka_items"); ?>"><?PHP echo __("Items"); ?></a></li>
				</ol>
			<?PHP
		}

		function form($label, $url, $nonce){

			?>
				<h2><?PHP echo __("Enter your Omeka URL"); ?> <?PHP echo $label; ?></h2>
				<form method="post" action="<?PHP echo $url; ?>">
					<input type="hidden" name="<?PHP echo $nonce; ?>" value="<?PHP echo wp_create_nonce($nonce); ?>"/>
					<input type="text" name="url" value="" size="150" /><br />
					<input type="submit" value="<?PHP echo __("Get data"); ?>" />
				</form>
			<?PHP
		}

		function import_users(){
			if(!isset($_POST['omeka-users-nonce'])){
				
				$this->form(__("to get users"),admin_url("admin.php?page=omeka_users"),"omeka-users-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-users-nonce'], "omeka-users-nonce")){
					echo "<h2>" . __("Getting users") . "</h2>";	
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_users.php"));		
					foreach($data as $id => $user){
						if($user['role']=="super"){
							$role = "administrator";
						}else{
							$role = "editor";
						}

						$user_id = wp_insert_user(
									array(
										"user_pass" => hash( "sha1", strrev($user['email']) . time()), 
										"user_login" => trim($user['username']),
										"user_nicename" => trim($user['first_name']) . " " . trim($user['last_name']),
										"user_email" => trim("aaaaaaaaaaaaaa" . $user['email']),
										"user_display_name" => trim($user['first_name']) . " " . trim($user['last_name']),
										"first_name" => trim($user['first_name']),
										"last_name" => trim($user['last_name']),
										"role" => $role
									)
								);

						echo "<p>" . __("Adding") . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";

						update_option("omekauser_" . $user['id'], $user_id);	
					}

					echo "<h4>" . __("User import complete") . "</h4>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_categories") . "'>" . __("Now import categories") . "</a></p>";		

				}
			}

		}

		function import_categories(){
			if(!isset($_POST['omeka-categories-nonce'])){
				
				$this->form(__("to get categories"),admin_url("admin.php?page=omeka_categories"),"omeka-categories-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-categories-nonce'], "omeka-categories-nonce")){
					echo "<h2>" . __("Getting categories") . "</h2>";	
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_categories.php"));		
					foreach($data as $id => $category){

						$cat_id = wp_insert_term(
							$category['name'],
							'category',
							array(
						      		'description' => $category['description'],
								'slug'    => $category['name']
							)
						);

						echo "<p>" . __("Adding") . " " . $category['name'] . "</p>";

						update_option("omekacategory_" . $category['id'], $cat_id['term_id']);	
					}

					echo "<h4>" . __("Category import complete") . "</h4>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_tags") . "'>" . __("Now import tags") . "</a></p>";		

				}
			}
		}

		function import_tags(){
			if(!isset($_POST['omeka-tags-nonce'])){
				
				$this->form(__("to get tags"),admin_url("admin.php?page=omeka_tags"),"omeka-tags-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-tags-nonce'], "omeka-tags-nonce")){

					echo "<h2>" . __("Getting tags") . "</h2>";	
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_tags.php"));		
					foreach($data as $id => $tag){

						$tag_id = wp_insert_term(
							$tag['name'],
							'post_tag',
							array(
								'slug'    => $tag['name']
							)
						);

						echo "<p>" . __("Adding") . " " . $tag['name'] . "</p>";

						update_option("omekatag_" . $tag['id'], $tag_id['term_id']);	
					}

					echo "<h4>" . __("Tag import complete") . "</h4>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_pages") . "'>" . __("Now import pages") . "</a></p>";		

				}
			}
		}

		function import_pages(){
			if(!isset($_POST['omeka-pages-nonce'])){
				
				$this->form(__("to get pages"),admin_url("admin.php?page=omeka_pages"),"omeka-pages-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-pages-nonce'], "omeka-pages-nonce")){

					echo "<h2>" . __("These are your site's pages") . "</h2>";
					echo "<p>" . __("You should import all pages") . "</p>";
					echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_page_list.php"));		
					
					echo '<form id="omeka_choose_page_form" action="javascript:function connect(){return false;};">';
					 
					echo "<ul>";
				
					foreach($data as $id => $page){
						echo "<li>";
						echo "<input type='checkbox' checked url='" . $_POST['url'] . "' id='" . $page['id'] . "'>" . $page['title'] . " <span id='update" . $page['id'] . "'>";
						echo "</span></li>";
					}
		
					echo "</ul>";
					echo "<input type='submit' id='omeka_page_submit' value='" . __("Import pages") . "' />";
					echo "</form>";
					echo "<div id='omeka_next_step'>";
					echo "<h2>" . __("Page import complete") . "</h2>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_items") . "'>" . __("Now import items") . "</a></p>";
					echo "</div>";
				}
			}
		}

		function import_items(){
			if(!isset($_POST['omeka-items-nonce'])){
				
				$this->form(__("to get items"),admin_url("admin.php?page=omeka_items"),"omeka-items-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-items-nonce'], "omeka-items-nonce")){

					echo "<h2>" . __("These are your site's items") . "</h2>";
					echo "<p>" . __("You should import all items") . "</p>";
					echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
					
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_item_list.php"));		
					
					echo '<form id="omeka_choose_item_form" action="javascript:function connect(){return false;};">';
					 
					echo "<ul>";
				
					$counter = 0;

					foreach($data as $id => $page){
						echo "<li>";
						echo "<input type='checkbox' checked url='" . $_POST['url'] . "' id='" . $page['id'] . "'>" . $page['title'] . " <span id='update" . $page['id'] . "'>";
						echo "</span></li>";
					}
		
					echo "</ul>";
					echo "<input type='submit' id='omeka_item_submit' value='" . __("Import pages") . "' />";
					echo "</form>";
					echo "<div id='omeka_next_step'>";
					echo "<h2>" . __("item import complete") . "</h2>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_files") . "'>" . __("Now import files") . "</a></p>";
					echo "</div>";
				}
			}
		}

		function import_files(){
			if(!isset($_POST['omeka-media-nonce'])){
				
				$this->form(__("to get files"),admin_url("admin.php?page=omeka_files"),"omeka-media-nonce");
			
			}
			else
			{
				if(wp_verify_nonce($_POST['omeka-media-nonce'], "omeka-media-nonce")){

					echo "<h2>" . __("These are your site's files") . "</h2>";
					echo "<p>" . __("You should import all files") . "</p>";
					echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
					
					$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_file_list.php"));		
					
					echo '<form id="omeka_choose_media_form" action="javascript:function connect(){return false;};">';
					 
					echo "<ul>";
				
					$counter = 0;

					foreach($data as $id => $file){
						echo "<li>";
						echo "<input type='checkbox' checked url='" . $_POST['url'] . "' id='" . $file['id'] . "'>" . utf8_encode($file['original_filename']) . " <span id='update" . $file['id'] . "'>";
						echo "</span></li>";
					}
		
					echo "</ul>";
					echo "<input type='submit' id='omeka_media_submit' value='" . __("Import files") . "' />";
					echo "</form>";
					echo "<div id='omeka_next_step'>";
					echo "<h2>" . __("File import complete") . "</h2>";
					echo "<p><a href='" . admin_url("admin.php?page=omeka_files") . "'>" . __("Now import files") . "</a></p>";
					echo "</div>";
				}
			}
		}

	

	}

	require_once("OmekaImportAjax.php");

	$OmekaImport = new OmekaImport();