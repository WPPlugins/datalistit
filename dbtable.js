jQuery(document).ready(function ($) {

function run(table, table_name, next){
	var data = {
		action: 'dli_fronted_action',
		table: ajax_object.tables[table_name]
	};

	if(next !== undefined) 
	{
	var t=  data.table
	if(next) {
		if( t.start_index + t.page_size > ajax_object.tables[table_name].size) 
			return;
		t.start_index += t.page_size;
	} else {
		if( t.start_index - t.page_size < 0)
			return;
		t.start_index -= t.page_size;
	}
	}

	$.post(ajax_object.ajax_url, data, function(response) {
			var dd={};
			try{
				dd = JSON.parse( response )
			}catch(e )
			{
				return;
			}
			if( !dd.result ) { 
				table.append( dd.error);
				return;
			}
			if( !dd.records.length ) return;
			
			table.empty()
			
			var r = $('<tr>')
			table.append(r)
			for (var i = 0; i < dd.header.length; i++) {
				var d = $('<th>').append(dd.header[i])
				r.append( d )
			}
			
			for (var i = 0; i < dd.records.length; i++) {
				var entry = dd.records[i];
				var r = $('<tr>')

				if( i %2 ) r.attr('class','even')
				else r.attr('class','odd')

				table.append(r)
				
				for (var j = 0; j < entry.length; j++) {
					var d = $('<td>').append(entry[j])
					r.append( d )
				}
			}
	});
}

$('.dli').each( function(i, ee) {
	var table_name = $(this).attr("id") 
	var table = $(this).find('.dli_table')
	run( table, table_name );
	
	$(this).find('.dli_paginate_previous').click( function(){
		run( table, table_name, false);
	})
	
	$(this).find('.dli_paginate_next').click( function(){
		run( table, table_name, true);
	})
})

});
