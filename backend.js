jQuery(document).ready(function ($) {

	//toggle advanced 
	$( "#dli_css" ).hide();
	$( "#dli_save" ).add( "#dli_advanced" ).add( "#dli_restore" ).click(function(){
		$( "#dli_css" ).toggle();
	})

	function restore( data ){
		$("#dli_status").empty()
		jQuery.post(ajax_object.ajax_url, data , function(response) {
				try{
					var $txt = JSON.parse( response )
					$('#dli_css_text').val( $txt );
				}catch(e )
				{
					return;
				}
		})
	}

	//css
	restore( { action: 'dli_backend_css_action', read: 1 } );
	$( "#dli_restore" ).click(function(){
		restore( { action: 'dli_backend_css_action', restore: 1 } )
	})

	$( "#dli_save" ).click(function(){
		restore( { action: 'dli_backend_css_action', css: $('#dli_css_text').val() } )
	})
	
	//upload
	$('#dli_file_upload').ajaxForm({
    beforeSend: function() {
		$("#dli_status").empty()
		$("#dli_file_upload .spinner").addClass('is-active')
				
    },
    uploadProgress: function(event, position, total, percentComplete) {
    },
    success: function() {
    },
	complete: function(xhr) {
		$("#dli_file_upload .spinner").removeClass('is-active')
		try {
			var response = JSON.parse( xhr.responseText )

			$("#dli_status").text( response.status )
			if( response.error) return;
			
			var ii=0;
			$('#table_settings tr').each( function(i,e){
				if ( $(e).find('.file_name').text() ==  response.fileName ||
					 $(e).find('td').length == 1 )
				{
					$(e).remove();
				}
				else 
				{
					ii++;
				}
			})
			var row = decodeURIComponent(response.row)
			row = row.replace(/XXX/g,  ii )
			//premium
			row = row.replace(/YYY/g,  (ii>1 ? 'Yes' : '') )
			$('#table_settings table').append( row )
		}catch(e )
		{
			return;
		}
	}
	});
})
