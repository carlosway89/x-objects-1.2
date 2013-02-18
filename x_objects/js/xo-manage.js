// management console
$.xo.manage = {
		// settings
		"settings" :{
			"service_key":false
		},
		// callback after pagination
		"paginate_cb":function(xhtml){
			//console.log(xhtml);
			var list = $('div.service-manage-list');
			var records = $('div.service-manage-list div.managed-record,div.service-manage-list div.pagination');
			records.remove();
			list.append($(xhtml).children());
			$('div.ajax-editor').ajax_editor();
		},
		// callback after logout
		"logout_cb":function(result){
			if ( /success/.test(result))
				window.location.reload(true);
		},
		// callback after emptying a table
		"empty_cb":function(result){
			if ( /success/.test(result))
				$('div.truncateable').remove();
		},
		// configure
		"configure":function(){
			// get service key
			var matches = /key-([a-z|\_]+)/.exec($('div.managed-services').attr('class'));
			if ( matches )
				$.xo.manage.settings.service_key = matches[1];
			// bind manage logout
			$('a.manage-logout').bind('click',function(e){
				e = e ? e : window.event;
				e.preventDefault();
				$.xo.api.call(
					'auth/logout',
					$.xo.manage.logout_cb
				);
				return false;
			});
			// bind pagination
			$('div.pagination').x_paginate();
			// bind service controls
			$('button.service-control').live('click',function(e){
				e = e ? e: window.event;
				e.preventDefault();
				// determine what to do
				var matches = /sc-([a-z]+)/.exec( $(this).attr('class'));
				var action = matches[1];
				//console.log(action);
				switch (action){
					case 'cancel':
						// clear warnings
						$('div.sc-warning').html('');
						// remove confirmations
						$('button.service-control').removeClass('confirmed');
						// hide this button
						$(this).hide();
					break;
					// empty a table, delete all records
					case 'empty':
						// if not confirmed
						if ( ! $(this).hasClass('confirmed')) {
							// show a warning
							$('div.sc-warning').html("WARNING!  This action will delete all records of this type.  Are you sure?");
							// add confirmed class
							$(this).addClass("confirmed");
							// show cancel button
							$('button.sc-cancel').show();
						} else {
							// remove confirmation
							$(this).removeClass('confirmed');
							// remove warning
							$('div.sc-warning').html('');
							// hide cancel
							$('button.sc-cancel').hide();
							// use api
							$.xo.api.call(
								'business/'+$.xo.manage.settings.service_key+'/empty',
								$.xo.manage.empty_cb
							);
						}
					break;
				}
				return false;
			});
		}
};