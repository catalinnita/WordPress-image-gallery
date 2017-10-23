(function() {
		var bn = '<?php echo $_GET["bn"]; ?>';
		
    tinymce.create('tinymce.plugins.'+bn, {
        init : function(ed, url) {
            ed.addButton(bn, {
                title : '<?php echo $_GET["butt_title"]; ?>',
                image : '<?php echo $_GET["butt_img"]; ?>',
                onclick : function() {
										wl = (parseInt(jQuery(window).width())-parseInt(jQuery("#"+bn+"_window").children(".wpfw_window").width()))/2;
										wt = (parseInt(jQuery(window).height())-parseInt(jQuery("#"+bn+"_window").children(".wpfw_window").height()))/2;
									 
									 // vars settings 
									 function set_parents_relationships(bn) {
										 var inputs = jQuery("select, input, textarea");
										 jQuery("#"+bn+"_window .wpfw_window .wpfw_window_container fieldset").each(function() {
										 		var obj = jQuery(this).find(inputs);
	                	 		if(obj.length > 0 && obj.attr("data-parent")) {
	                	 			var obj_parent = obj.attr("data-parent").split(":");
	                	 			if(obj.parent().parent().children("fieldset").children("[name="+obj_parent[0]+"]").val() == obj_parent[1]) {
	                	 				obj.removeAttr('disabled');
	                	 				obj.parent('fieldset').css({display:'block'});
	                	 			}
	                	 			else {
	                	 				obj.attr('disabled','disabled');
	                	 				obj.parent('fieldset').css({display:'none'});
	                	 			}
	                	 		}
										 });
									 }
									 
									 set_parents_relationships(bn);
									 jQuery("#"+bn+"_window").find("select").change(function() { set_parents_relationships(bn); });
									 jQuery("#"+bn+"_window").find("input, textarea").keypress(function() { set_parents_relationships(bn); });
									 
									 // window settings
                	 jQuery("#"+bn+"_window").children(".wpfw_window").css({left: wl, top: wt});
                	 jQuery("#"+bn+"_window").fadeIn(200);
                	 
                	 
                	 // insert shortcode
                	 jQuery(".wpfw_window_buttons button").click(function() {
                	 	 	var s_container = jQuery(this).parent().parent().children(".wpfw_window_container");
                	 	 	var s_name = s_container.attr("id");
                	 	 	var s_vars = '';
                	 	 	var inputs = jQuery("select, input, textarea");
                	 	 	s_container.children("fieldset").each(function() {
                	 	 		var obj = jQuery(this).find(inputs);
                	 	 		if(obj.length > 0 && obj.attr("disabled") != 'disabled') {
                	 	 			s_vars = s_vars+obj.attr("name")+'="'+obj.val()+'" ';
                	 	 		}
                	 	 	});
                	 	 	
                	 	 	if( jQuery("#"+bn+"_window").css("display") != 'none') {
                  	 		ed.execCommand('mceInsertContent', true, '['+s_name+' '+s_vars+']</p><p>');
                  	 		jQuery("#"+bn+"_window").css({display: 'none'});
                  	 	}
                  	 	
                 	});
                 	
                 	// close button
                  jQuery("#"+bn+"_window .close").click(function() {
                   	jQuery("#"+bn+"_window").fadeOut(200);
                  });                 	
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname 	: "WPFW galleries",
                author 		: 'Catalin Nita',
                authorurl : 'http://www.wpfw.net/',
                infourl 	: 'http://www.wpfw.net/',
                version 	: "1.0"
            };
        }
    });
    tinymce.PluginManager.add(bn, eval('tinymce.plugins.'+bn));
})();
