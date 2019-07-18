<?PHP

	class OmekaImportAjax{

		function __construct(){			
			add_action("wp_ajax_omeka_page_import", array($this, "omeka_page_import"));
			add_action("wp_ajax_no_priv_omeka_page_import", array($this, "omeka_page_import"));
			add_action("wp_ajax_omeka_item_import", array($this, "omeka_item_import"));
			add_action("wp_ajax_no_priv_omeka_item_import", array($this, "omeka_item_import"));
			add_action("wp_ajax_omeka_media_import", array($this, "omeka_media_import"));
			add_action("wp_ajax_no_priv_omeka_media_import", array($this, "omeka_media_import"));
		}	

		function omeka_page_import(){
			
			if(wp_verify_nonce($_POST['nonce'], "omeka_import_page"))
			{
			
				$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_page.php?id=" . $_POST['id']));
								
				$publish = "draft";

				if($data['published']==1){
					$publish = "publish";
				}

				$args = array(	
					'post_author' => get_option("omekauser_" . $data['author']),
					'post_date' => $data['inserted'],
					'post_content' => utf8_encode($data['text']),
					'post_title' => $data['title'],
					'post_status' => $publish,
					'post_type' => "page"
				);

				$page_id = wp_insert_post($args);

			}

			die();

		}

		function omeka_item_import(){

			if(wp_verify_nonce($_POST['nonce'], "omeka_import_item"))
			{

				$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_item.php?id=" . $_POST['id']));
				
				$publish = "draft";

				if($data['public']==1){
					$publish = "publish";
				}

				$args = array(	
					'post_author' => get_option("omekauser_" . $data['author']),
					'post_date' => $data['created'],
					'post_content' => utf8_encode($data['description']),
					'post_title' => $data['Title'],
					'post_status' => $publish,
					'post_type' => "post"
				);

				$post_id = wp_insert_post($args);

				unset($data['Title']);
				unset($data['description']);
				unset($data['author']);
				unset($data['created']);
				unset($data['public']);

				$category = get_category(get_option("omekacategory_" . $data['category']));

				unset($data['category']);

				$tags = array();

				foreach($data['tags'] as $tag){
					$tag_info = get_tag(get_option("omekatag_" . $tag[0]));
					array_push($tags, $tag_info->term_id);
				}

				unset($data['tags']);

				wp_set_post_categories($post_id, array($category->term_id));
				wp_set_object_terms($post_id, $tags, "post_tag");

				foreach($data as $name => $value){
					add_post_meta($post_id, "omeka_setting_" . $name, $value);
				}
 

			}

			die();



		}

		function omeka_media_import(){

			if(wp_verify_nonce($_POST['nonce'], "omeka_import_media"))
			{

				$data = unserialize(file_get_contents($_POST['url'] . "get_omeka_file.php?id=" . $_POST['id'] . "&url=" . $_POST['url']));

				$remote_file = file_get_contents($data['download']);

				$file = wp_upload_bits(utf8_encode($data['display_name']), null, $remote_file);

		       	$attachment = array(
			       	'guid'=> $file['url'], 
        				'post_mime_type' => $file['type'],
        				'post_status' => 'inherit'
         			);

    				$image_id = wp_insert_attachment($attachment, $file['file']);

				add_post_meta($image_id, $file['url'], $data['name']);

				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$args = array(
					   	'meta_query' => array(
					       	array(
					           	'key' => 'omeka_setting_id',
					           	'value' => $data['item_id'],
					       	    'compare' => '=',
						       )
					   )
				);
				$query = new WP_Query($args);

				$post = get_post($image_id);

				add_post_meta($query->posts[0]->ID, "attached_file", $post->guid);
 
				$attach_data = wp_generate_attachment_metadata( $image_id, $file['file'] );

				wp_update_attachment_metadata( $image_id, $attach_data );

				echo $post->guid;
				
			}

			die();


		}



	}

	$OmekaImportAjax = new OmekaImportAjax();