jQuery(document).ready(function($) {
			
	function wpfw_init() {
		$('#WPFWManageForm').ajaxForm();
   	
   	$("#SortableElements").sortable({
   		handle: '.move',
   		update: function( event, ui ) {
   			var pos = 0;
   			$("#SortableElements").children("li").each(function() {
   				$("#"+$(this).attr("id")+"P").val(pos);
   				pos = pos+1;
   			});
   			$('#WPFWManageForm').submit();
   		}
		});
		
		$(".pg-title, .pg-desc, .pg-video").change(function() {
			$('#WPFWManageForm').submit();
		});
		
		$(".del-button").click(function() {
			var id = $(this).attr("id").substr(1,9999);
			$("#del-"+id).val("on");
			$('#WPFWManageForm').submit();
			$("#SO"+id).remove();
			return false;
		});
		
		$(".del-button-f").click(function() {
			var id = $(this).attr("id").substr(2,9999);
			$("#delf-"+id).val("on");
			$('#WPFWManageForm').submit();
			$("#SO"+id).remove();
			return false;
		});

	}
		
	wpfw_init();
	
	var options = { 
		success : function() {
			var formUrl = $('#WPFWAddForm').attr("action");
	 		$("#SortableElementsContainer").load(formUrl+" #SortableElements", function() {
	 			wpfw_init();
	 			$("#WPFWAddButton").removeClass("loading");
	 		});
	 		
	  }
	} 
	
	$('#WPFWAddForm').ajaxForm(options);			
	$("#WPFWAddButton").not(".na").click(function() {
		$(this).addClass("loading");
		$("#WPFWAddForm").submit();
		return false;
	});
	
	function wpfw_import_init() {
		var options = { 
			success : function() {
				var formUrl = $('#WPFWImportForm').attr("action");
		 		$("#SortableElementsContainer").load(formUrl+" #SortableElements", function() {
		 			wpfw_init();
		 			$(".add-button").removeClass("loading");
		 			$("#ImportList").children("li").each(function() {
		 				if ($(this).children("div").children("input.addinput").val() == 'on' || $(this).children("div").children("input.delfiinput").val() == 'on') { 
		 					$(this).remove();
		 				}
		 			});
		 		});
		  }
		} 
		
		$('#WPFWImportForm').ajaxForm(options);	
				
		$(".add-button").click(function() {
			$(this).parent().children("input.addinput").val("on");
			$(this).addClass("loading");
			$("#WPFWImportForm").submit();
			return false;
		});
		
		$(".del-button-fi").click(function() {
			var id = $(this).attr("id").substr(3,9999);
			$("#delfi-"+id).val("on");
			$(this).addClass("loading");
			$('#WPFWImportForm').submit();
			return false;
		});
	}
	
	$("#WPFWImportButton").click(function() {
		var formUrl = $('#WPFWImportForm').attr("action");
		var l = (parseInt($(window).width())-(parseInt($("#ImportWindowContainer").width())+parseInt($("#ImportWindowContainer").css("paddingLeft"))))/2;
		$("#ImportWindowContainer").css({marginLeft: l});
		$("#ImportGalleries").css({display: 'none'});
		$("#ImportGalleriesContainer").html('<div class="loading_bar"></div>');
		$("#ImportWindow").fadeIn(200);
		$("#ImportGalleriesContainer").load(formUrl+" #ImportGalleries", function() {
			wpfw_import_init();
			$("#ImportGalleries").fadeIn(200);
		});
			
			
	});
	
	$(".close").click(function() {
		$("#ImportWindow").fadeOut(200);
	});
	
});