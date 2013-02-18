(function( $ ){

  $.fn.xevents = function( options ) {
	$.xevents.show( $("div.event-log"), options );
  };
})( jQuery );

	// ajax editor module
$.xevents = {
	// levels
	"level" : {
		"success" : 1,
		"notice" : 0,
		"failure" : 2,
	"warning" : 3,
	"exception": 4,
	"debug" : 5,
	"error" : 6
	},
	// settings
	"settings":{
		// div for display
		"div": false,
		// last record id
		"last_id": 0
	},
	// show event log in a specific element
	"show":function(div,options){
		// save div for reuse
		$.xevents.settings.div=div;
		if ( $('button.empty-log').length)
			$('button.empty-log').bind('click',function(e){
				$.xevents.clear(e,this);
			});
		$.xevents.settings.div.empty();
		options = options? options : {};
		var heartbeat_func = (options.use_rest)? function(){				// heartbeat function
				// retrieve new events as a recordset
				$.xo.api.call('recordset/xevent/20 most recent ascending,id above '+$.xevents.settings.last_id+'/xevent-view/xevent-none-view',
					$.xevents.place);
			} :
			function(){				// heartbeat function
				// retrieve new events as a recordset
				$.ajax( {
					url: 'x_objects/api/api.php',
					data : 'module=recordset&key=xevent&query=20 most recent ascending,id above '+$.xevents.settings.last_id+'&view=xevent-view&none_view=xevent-none-view',
					success: function(result) { $.xevents.place(result); }
				});
			};


		// set up heartbeat
		$.jheartbeat.set(
			'xevents',				// the key
			{ "delay": 8000 }, 		// options
			heartbeat_func
			/*function(){				// heartbeat function
				// retrieve new events as a recordset
				$.xo.api.call('recordset/xevent/id above '+$.xevents.settings.last_id+'/xevent-view/xevent-none-view',
					$.xevents.place);
			}
			*/
		);
	},
	
	// place retrieved events
	"place":function(xhtml){
		// place only if we have events
		if ( $(xhtml).find('div.xevent').length) {
			// update id
			$.xevents.settings.last_id = parseInt( $(xhtml).find('div.xevent').last().attr('id'))+1;
			//console.log("got new events");
			$(xhtml).find('div.xevent').each( function(){
				//console.log( this);
				$.xevents.settings.div.append( this);
			});
		}
	},
	// callback after clearing
	"clear_cb":function(result){
		if ( /success/.test(result)) {
			$('div.event-log').empty();
			$.xevents.settings.last_id = 0;
		}
	},
	// clear event log
	"clear":function(e,elem){
		// use api to empty the table
		$.xo.api.call(
			'business/xevent/empty',
			$.xevents.clear_cb
		);
	},
	"log_cb":function(result){},
	// log an event (useful from jquery)
	"log":function(level,tag,msg){
		$.xo.api.call(
			'business/xevent/create',
			$.xevents.log_cb,
			"json="+JSON.stringify( { "tag" : JSON.stringify( tag ) , "event_type_id": level, "message": msg })
		);
	}	};
	
