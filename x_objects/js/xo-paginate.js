(function( $ ){

  $.fn.x_paginate = function() {
	// bind all buttons
	$('div.pagination button').live('click',function(e){
		e = e?e:window.event;
		e.stopPropagation();
		$.xo.pagination.go($(this));
		return false;
	});
  };
})( jQuery );

// pagination module 
$.xo.pagination = {
	// go!
	"go":function(button){
		var paginator = $('div.pagination');
		var matches = /key-([a-z|_|\-]+)/.exec($('div.pagination').attr('class'));
		var key = matches[1];
		var matches = /action-([a-z]+)/.exec(button.attr('class'));
		var action = matches[1];
		var callback = function(){};
		if ( button.hasClass('default-callback'))
			callback = $.xo.manage.paginate_cb;
		// set page
		var matches = /page-([0-9]+)/.exec( paginator.attr('class'));
		var page = parseInt( matches[1]);
		switch(action){
			case 'page':
				var matches = /page-([0-9]+)/.exec(button.attr('class'));
				page = parseInt( matches[1] );
			break;
			case 'next':
				page = page +1;
			break;
			case 'back':
				page = page -1;
			break;
			case 'ff':
				var matches = /lastpage-([0-9]+)/.exec( paginator.attr('class'));
				page = parseInt( matches[1] );
			break;
			case 'rewind':
				page = 1;
			break;
		}
		// use api to get more records
		$.xo.api.call(
			'recordset/'+key+'/10 from page '+page+'/bo-'+key+'-admin-list-view/bo-'+key+'-admin-list-none-view',
			callback
		);
	}
};