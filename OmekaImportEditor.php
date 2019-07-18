<?PHP

	class OmekaImportEditor{

		public $metadata;

		function __construct(){	

			add_action("admin_head", array($this, "post_fields"));		
			add_action("save_post", array($this, "save_post_fields"));		

		}

		function post_fields(){
	
			global $post;
			if(isset($post->ID)){
				$this->metadata = get_metadata("post",$post->ID);
				if(count($this->metadata!=0)){
					add_meta_box("omeka_extra", "Additional Omeka fields", array($this, "extra"));
				}
			}
			
		}

		function extra(){

			$noShow = array("omeka_setting_id","omeka_setting_type","omeka_setting_modified","omeka_setting_omeka_types","omeka_setting_file_data","omeka_setting_media");

			foreach($this->metadata as $key => $value){
				if(strpos($key,"omeka_setting")===0){
					if(!in_array($key,$noShow)){
					echo "<label>" . str_replace("omeka_setting_","",$key) . "</label>";
					echo "<textarea style='width:100%' rows='10' name='" . $key . "'>";
					echo $value[0];
					echo "</textarea>";
					}
				}
			}
		}

		function save_post_fields($post_id){
			$noShow = array("omeka_setting_id","omeka_setting_type","omeka_setting_modified","omeka_setting_omeka_types","omeka_setting_file_data","omeka_setting_media");

			$this->metadata = get_metadata("post",$post_id);

			foreach($this->metadata as $key => $value){
				if(strpos($key,"omeka_setting")===0){
					if(!in_array($key,$noShow)){
						update_post_meta($post_id, $key, $_POST[$key]);
					}
				}
			}

		}	

	}

	$OmekaImportEditor = new OmekaImportEditor();