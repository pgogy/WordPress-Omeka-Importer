<?PHP

	require_once("config.inc");
	require_once("data.inc");
	require_once("database.inc");
			
	class omeka_wp{

		public function database_connect(){		

			if(defined("DB_TYPE")){		

				if(trim(DB_TYPE)!=""){			

					$db_type = DB_TYPE . "_database_layer";

					$this->db_layer = new $db_type();					

				}				

			}		

			$this->database_link = $this->db_layer->database_connect();	
	
		}

		public	function get_users(){

			$users = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT * FROM `omeka_entities` as o_e, omeka_users as o_u where o_e.id = o_u.id", array(), $this->database_link)); 

			$users_output = array();
			$user_data = array();

			foreach($users as $user){
				$user_data['first_name'] = $user['first_name'];
				$user_data['id'] = $user['id'];
				$user_data['last_name'] = $user['last_name'];
				$user_data['email'] = $user['email'];
				$user_data['username'] = $user['username'];
				$user_data['role'] = $user['role'];

				$users_output[] = $user_data;
				$user_data = array();
			}

			print_r(serialize($users_output));

		}

		public	function get_categories(){

			$collections = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select id,name,description from omeka_collections", array(), $this->database_link)); 

			$categories_output = array();
			$categories = array();

			foreach($collections as $collection){
				$categories['id'] = $collection['id'];
				$categories['name'] = utf8_encode($collection['name']);
				$categories['description'] = utf8_encode($collection['description']);
				
				$categories_output[] = $categories;
				$categories = array();
			}

			print_r(serialize($categories_output));

		}

		public	function get_tags(){

			$tags = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select id,name from omeka_tags", array(), $this->database_link)); 

			$tags_output = array();
			$tags_data = array();

			foreach($tags as $tag){

				$tags_data['id'] = $tag['id'];
				$tags_data['name'] = utf8_encode($tag['name']);
				
				$tags_output[] = $tags_data;
				$tags_data = array();
			} 

			print_r(serialize($tags_output));

		}

		public	function get_page_list(){

			$pages = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select * from omeka_simple_pages_pages", array(), $this->database_link)); 

			$pages_output = array();
			$pages_data = array();

			foreach($pages as $page){
				$pages_data['id'] = $page['id'];
				$pages_data['title'] = utf8_encode($page['title']);

				$pages_output[]= $pages_data;
				$pages_data = array();
			}

			print_r(serialize($pages_output)); 

		}

		public	function get_page($id){

			$pages = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select * from omeka_simple_pages_pages", array(), $this->database_link)); 

			$pages_data = array();

			foreach($pages as $page){
		
				if($page['id']==$id){

					$pages_data['id'] = $page['id'];
					$pages_data['text'] = $page['text'];
					$pages_data['author'] = $page['created_by_user_id'];
					$pages_data['published'] = $page['is_published'];
					$pages_data['title'] = $page['title'];
					$pages_data['slug'] = $page['slug'];
					$pages_data['inserted'] = $page['inserted'];
					$pages_data['updated'] = $page['updated'];
					$pages_data['parent_id'] = $page['parent_id'];

					print_r(serialize($pages_data));

				}
			} 

		}

		public	function get_item_list(){

			$item_counter = 0;

			$item_output = array();
			$item_data = array();

			$items = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select * from omeka_items", array(), $this->database_link)); 

			foreach($items as $item){

				$item_data['id'] = $item['id'];
				
				$elements = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT name,text FROM `omeka_element_texts` as oet, omeka_elements as oe WHERE record_id = :id and oet.element_id = oe.id", array(":id" => $item['id']), $this->database_link)); 
				
				foreach($elements as $element){
					$counter = 0;
					foreach($element as $index => $data){
						if(!is_numeric($index)){
							if($counter == 0){
								$this->field = $data;
								$counter++;
							}else{
								if($this->field=="Coverage"){
									$temp = explode("\n", $data);
									if(count($temp)==2){
										$data = $temp[1];
									}
								}
								if($this->field=="Title"){
									$item_data[strtolower($this->field)] = trim(utf8_encode($data));
								}
							}

						}
					}
				}

				$item_output[] = $item_data;
				unset($item_data);
				$item_data = array();
					
			}

			print_r(serialize($item_output));
	
		}

		public	function get_entry_data($id){

			$item_counter = 0;

			$item_data = array();

			$items = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select * from omeka_items", array(), $this->database_link)); 

			foreach($items as $item){

				if($id==$item['id']){

					$item_data['id'] = $item['id'];
					$item_data['public'] = $item['public'];
					$item_data['type'] = $item['item_type_id'];
					$item_data['category'] = $item['collection_id'];
					$item_data['created'] = $item['added'];
					$item_data['modified'] = $item['modified'];
	
					//get elements

					$elements = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT name,text FROM `omeka_element_texts` as oet, omeka_elements as oe WHERE record_id = :id and oet.element_id = oe.id", array(":id" => $item['id']), $this->database_link)); 
				
					foreach($elements as $element){
						$counter = 0;
						foreach($element as $index => $data){
							if(!is_numeric($index)){
								if($counter == 0){
									$this->field = $data;
									$counter++;
								}else{
									if($this->field=="Coverage"){
										$temp = explode("\n", $data);
										if(count($temp)==2){
											$data = $temp[1];
										}
									}
									$item_data[$this->field] = trim(utf8_encode($data));
								}

							}
						}
					}

					//get item type

					$types = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT name FROM `omeka_item_types` WHERE id = :id", array(":id" => $item['item_type_id']), $this->database_link));

					$type_data = array();

					foreach($types as $type){
						array_push($type_data,$type['name']);
					}

					$item_data['omeka_types'] = $type_data;


					//get tags

					$tags = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT tag_id FROM `omeka_taggings` WHERE relation_id = :id", array(":id" => $item['id']), $this->database_link));

					$tags_data = array();

					foreach($tags as $tag){
						array_push($tags_data,$tag['tag_id']);
					}

					//get file

					$files = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT archive_filename FROM `omeka_files` WHERE item_id = :id", array(":id" => $item['id']), $this->database_link));

					$file_data = array();

					foreach($files as $file){
						array_push($file_data,$file['archive_filename']);
					}

					$item_data['file_data'] = $file_data;

					//get author

					$author = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("SELECT relationship_id FROM `omeka_entities_relations` WHERE relation_id = :id", array(":id" => $item['id']), $this->database_link));

					$item_data['author'] = $author[0]['relationship_id'];
	
					if(!isset($item_data['Description'])){
						$exif_data = explode('s:8:"FileName";s:36:"',$item_data['Exif Array']);
						$item_data['media'] = substr($exif_data[1],0,strpos($exif_data[1],'"'));
					}


					print_r(serialize($item_data));					

					unset($item_data);
					$item_data = array();
				}				
	
			}

			die();
	
		}

		public	function get_file_list(){

			$item_counter = 0;

			$file_output = array();
			$file_data = array();

			$files = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select id, original_filename from omeka_files", array(), $this->database_link)); 

			foreach($files as $file){

				$file['original_filename'] = utf8_encode($file['original_filename']);

				$file_output[] = $file;
				unset($item_data);
				$item_data = array();
					
			}

			print_r(serialize($file_output));
	
		}

		public	function get_file($id, $url){

			$item_counter = 0;

			$file_output = array();
			$file_data = array();

			$files = $this->db_layer->get_all_rows($this->db_layer->select_query_multiple("select id, item_id, archive_filename, original_filename from omeka_files", array(), $this->database_link)); 

			foreach($files as $filedata){

				if($filedata['id']==$id){

					$dir = opendir(dirname(__FILE__));
					while($file = readdir($dir)){
						if(is_dir(dirname(__FILE__) . "/" . $file . "/archive/files")){
							$file_data['name'] = $filedata['archive_filename'];
							$file_data['display_name'] = $filedata['original_filename'];
							$file_data['download'] = $url . $file . "/archive/files/" . $filedata['archive_filename'];
							$file_data['id'] = $filedata['id'];
							$file_data['item_id'] = $filedata['item_id'];	
							$file_output[] = $file_data;
							$file_data = array();
						}
					}

				}
					
			}

			print_r(serialize($file_output));
			
		}


	}



