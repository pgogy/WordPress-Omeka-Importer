function omeka_media_get(items, orig_length){
	if(items.length!=0){
		item = items.shift();
		
		var data = {
			'action': 'omeka_media_import',
			'id': jQuery(item).attr('id'),
			'url': jQuery(item).attr('url'),	
			'nonce': omeka_import_media.nonce
		};
		
		jQuery("#update" + jQuery(item).attr("id"))
			.html("Importing...")
			.css("color","#00F");
		
		jQuery.post(omeka_import_media.ajaxURL, data, function(response) {
			
			width = jQuery("#importProgress")
						.width();
						
			width = width - 10;
						
			progress = (orig_length - items.length) * (width / orig_length);

			jQuery("#importTotal")
				.html((orig_length - items.length) + " / " + orig_length);

			jQuery("#importProgressBar")
				.animate({width:progress+"px"}, 400);
				
			percentage = (100-((items.length/orig_length) * 100)).toString();
			percentage = percentage.split(".");

			jQuery("#importProgressBar")
				.html(percentage[0] + "%");
				
			jQuery("#update" + jQuery(item).attr("id"))
				.html("Media imported " + response)
				.css("color","#0F0");
			
			omeka_media_get(items, orig_length);
			
		});
	}else{	
		children = Array();
		jQuery("form#omeka_choose_media_form")
			.children()
			.each(
				function(index,value){
					children.push(value);
				}
			);
		omeka_media_fade_out(children);
	}
}

function omeka_media_fade_out(items){
	if(items.length!=0){
		item = items.shift();
		jQuery(item)
			.fadeOut(10, function(){
							omeka_media_fade_out(items);
						}
					);
	}else{
		console.log("here i am");
		jQuery("div#omeka_next_step")
			.fadeIn(500);
	}
}

jQuery(document).ready(
	function(){
	
		jQuery("form#omeka_choose_media_form #omeka_media_submit")
			.on("click", 
					function(){
					
						items = Array();
						
						jQuery("#importProgress")
							.slideDown(500);
							
						jQuery("#importProgressBar")
							.animate({width:"40px"}, 400);
							
						jQuery("#importProgressBar")
							.html("0%");	
					
						jQuery("form#omeka_choose_media_form input:checked")
							.each(							
								function(index,value){	
									items.push(value);									
								}
							);
							
						omeka_media_get(items, items.length);
					
					}
			);
	}
);