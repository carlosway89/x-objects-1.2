		// jquery function to set cursor position
$.fn.setCursorPosition = function(pos) {
      this.each(function(index, elem) {
        if (elem.setSelectionRange) {
          elem.setSelectionRange(pos, pos);
        } else if (elem.createTextRange) {
          var range = elem.createTextRange();
          range.collapse(true);
          range.moveEnd('character', pos);
          range.moveStart('character', pos);
          range.select();
        }
      });
      return this;
    };
		


// realobjects jquery extension lib
$.realobjects = {
	"debug" : false,
	"debugie" : false,
	// xml config
	"xml" : false,
	// context
	"context" : "",
	// dcss module
	"dcss":{
		// center an element in the window
		"center_in_window":function(what,how){
			switch(how){
				case 'vertically':
					var wheight = $(window).height();
					var dhraw = $(what).css('height');
					var matches = /([0-9]+)px/.exec( dhraw);
					var dheight = parseInt(matches[1]);
					var tmargin = 0.5*wheight - 0.5*dheight;
					$(what).css('margin-top',tmargin+'px');
				break;
			}
		}
	},
			// the api
	"api" : {
	
		// pending request, cancellable
		"xhr" : false,
		
		// cancel a request
		"cancel" : function() {
		
			if ( $.xo.api.xhr )
				$.xo.api.xhr.abort();
		
		
		},
	
		// call the api with a specific REST url
		"call" : function( url, callback, args ) {
		
			if ( $.xo.debug )
				console.log( "xo:api:call: args = " + args);
		
			$.xo.api.xhr = $.ajax({
			
			    
				url : 'api/' + url,
				type : 'POST',
				data : typeof( args ) != 'undefined' ? args : '',
				success: function( data ) {
					if ( typeof( callback) == 'undefined')
						console.log("x-objects:api: callback is not a defined function.");
					else
						callback( data );
				
				}
			
			});
		
		}
	
	},
	
	"init" : function( key, callback, context ) {
	
		// set the context
		$.xo.context = context;
		
		if ( $.xo.debug )
			console.log( "xo:init: context = " + context );

		// load xml
		$.ajax({
			url : 'xml/' + key + '.xml',
			dataType: 'xml',
			success: function( xml ) {
				// live validation
				$('.xo-livevalidate').each(function(){
					$(this).livevalidate();
				});
				// look for hover tips
				if ( $( xml ).find('hover-tip').text() != '' ) {
					if ( $.xo.debug )
						console.log( "initializing hover tips");
					$.xo.hover_tip.init( $( xml ).find( 'hover-tip'));
				}
				// look for validation
				if ( $( xml ).find( 'x-validation').text() != '' ) {
					
					$.xo.validation.init( $( xml ).find('x-validation') );
				}
				
				// look for auto-suggest
				if ( $( xml ).find( 'auto-suggest').text() != '' )
					$.xo.auto_suggest.configure( $( xml ).find('auto-suggest') );
					
				// look for document ready jquery
				if ( $( xml ).find( 'document-ready').text() != '' ) {
				    
					
				
					// bind jquery ready
					$.xo.bind_jquery( '' , $( xml ).find('document-ready') );
					
				} else {
		//		    alert( "no document ready!"+$(xml).find('document-ready').text());
				}
				
				// if at least one component has ajax editor class, configure it
				if ( $( 'div.ajax-editor').length )
					$('div.ajax-editor').ajax_editor();
				// enable simple login
				if ( $('div.simple-login').length && $('div.simple-login').is(':visible'))
					$('div.simple-login').simple_login();
				// management console
				if ( $('div.management-console').length && $('div.management-console').is(':visible')){
					$.xo.manage.configure();
				}
				
			
				callback( xml );
			
			},
			error: function( result ) {
			
				//alert( 'error');
			}
		});
		
	},
	
	// ui module
	"ui" : {
	
		// clear input field contents
		"clear_input_contents" : function( e , elem ) {
		
			$( elem ).val('');
			
		}
		

	},
	
	
	// auto-suggest
	"auto_suggest" : {
	
		"debug" : false,
	
		"config" : false,
		
		"suggestions_cache" : [],
		
		// save suggestions
		"save_suggestions" : function( data ) {
		
			$.xo.auto_suggest.suggestions_cache = data;
			
		},
		
		"configure" : function ( xml ) {
		
			if ( $.xo.debug || $.xo.auto_suggest.debug )
				console.log( 'configuring auto-suggest');
			
			if ( $( xml ).find( 'cache').text() == 'yes' ) {
			
				if ( $.xo.debug )
					console.log( 'auto-suggest caching enabled');
					
				var source = $( xml ).find( 'source').text().split('.');
				var key = source[0];
				var field = source[1];
				
				// only run if needed on this page
				$.xo.api.call( 'mysql_service/get_as_array/key:' + key + ',field:' + field , $.xo.auto_suggest.save_suggestions );
				
			}
		
			$.xo.auto_suggest.config = xml;
			
			// the element to place suggestions
			var suggest_elem = $( $.xo.auto_suggest.config ).find('suggest-element').text();

			
			// configure fields
			$( xml ).find( 'fields field').each( function() {
			
				var id = '#' + $(this).attr('id');
				
				if ( $.xo.debug )
					console.log( 'binding auto-suggest to field id =  ' + id );
				
				$( id ).live('keyup',$.xo.auto_suggest.suggest );
			
			});
			
			// add a handler when clicking on a suggestion
			var suggest_class = $( xml ).find( 'suggest-class').text();
			if ( suggest_class != '')
				$( 'div.' + suggest_class ).live( 'click' , $.xo.auto_suggest.select );
			
			
			
		},
		
		// select a suggestin
		"select" : function( e){
		
			var tmp = $( this ).attr('id').split('-');
			var id = tmp[1];
			var text = $( this).html();
			if ( $.xo.debug )
				console.log( "xo:suggest:select text = " + text);
			// the element to place suggestions
			var suggest_elem = $( $.xo.auto_suggest.config ).find('suggest-element').text();

			$( suggest_elem).hide();
			
			$.xo.call_by_name( 
				$( $.xo.auto_suggest.config ).find( 'selected-callback').text(), 
				window, 
				id,
				text
			);
		
		},
	
		"suggest" : function( e ) {
		
			if ( $.xo.debug )
				console.log( 'suggestion module activated by an event');

			// the element to place suggestions
			var suggest_elem = $( $.xo.auto_suggest.config ).find('suggest-element').text();
		
			// determine if keydown was pressed
			e = e ? e : window.event;
			if ( e.keyCode == 40 ) {
			
				//console.log( 'xo: suggest: downkey pressed');
			
				// give suggest element focus if visible
				if ( $( suggest_elem ).is(':visible') ) {
					//console.log( 'xo:suggest: element is visible!');
					$( suggest_elem + ' div:nth-child(0)' ).focus();
				
				}	
				// that's it!
				return true;
			
			}
			
		
			// the user's current query
			var query = $( this ).val().toLowerCase();
			
			// the tolerance to begin suggesting
			var tolerance = parseInt( $( $.xo.auto_suggest.config ).find('tolerance').text() );
			
			// get the token delimiter
			var token_delimiter = $( $.xo.auto_suggest.config ).find('token-delimiter').text();
			
			if ( $.xo.debug )
				console.log( 'suggestion module tolerance is ' + tolerance );
		
			
			if ( query.length >= tolerance ) {
			
				if ( $.xo.debug )
					console.log( 'auto suggest tolerance exceeded'); 
			
				// go through each suggestion and try to find a match
				var cache = $.xo.auto_suggest.suggestions_cache;
				
				if ( $.xo.debug )
					console.log( 'auto suggest cache = ' + cache); 
				
				var keys = cache.split(",");
				
				// empty suggestions
				$( suggest_elem).empty().hide();
				
				for ( var i in keys  ) {
				
					var matched = false;
					
					var candidate = keys[i].toLowerCase();
					
					if ( token_delimiter != '' ) {
					
						var candidates = candidate.split( token_delimiter );
					
						for ( var j in candidates )
							if ( query == $.trim( candidates[j] ).substring( 0, query.length ) ) {
								matched = true;
								break;
							}
					
					} else if ( query == candidate.substring( 0, query.length ) )
						matched = true;
						
					if ( matched ) {
					
						// split candidate between label and id
						var split = candidate.split("=");
						var label = split[0];
						var id = split[1];
					
						// place suggestion in suggestion area
						var suggest_class = $( $.xo.auto_suggest.config ).find('suggest-class').text();
							
						if ( ! $( suggest_elem + ' div#suggestion-' + id ).length ) {
							$( suggest_elem ).append( '<div id="suggestion-' + id + 
								'" class="' + suggest_class + '">' + label + '</div>' );
							if ( ! $( suggest_elem).is(':visible') )
								$( suggest_elem).fadeIn("med");
						}
					}
				}
				
			} else {
			
				// we're below the tolerance, so be sure to remove any suggestions
				$( suggest_elem ).hide().empty();
			
			}
			
		}
	
	},

	// ui controls
	"ui_controls" : {
	
		// configure 'em
		"configure" : function( xml ) {
		
			// go through each one
			$( xml ).find( 'ui-controls control').each ( function() {
			
				var query = $( this ).attr('query');
				var type = $( this).attr('type');

				switch ( type ) {
				
					case 'datepicker':
					
						$( query ).datepicker({ changeMonth: true });
						
					break;
				
				}
	
				
			});
		
		}
	},
	
	// validation module
	"validation" : {
	
		// enable debugging for this module
		"debug" : false,
	
		// configuration
		"config" : false,
		
		// indicates if an error exists
		"error" : false,
		
		// pending submit
		"pending_submit" : false,
		
		// set an error condition
		"set_error" : function( id, msg, val_id) {
		
			if ( $.xo.debug || $.xo.validation.debug )
				console.log( "xo:validation:set_error: error in field " + id);
	
			var msg_id = $( $.xo.validation.config ).find( 'validation#' + val_id + ' msg-prefix').text()  + id;
			
			if ( $.xo.debug || $.xo.validation.debug )
				console.log( "xo:validation:set_error: msg id = " + msg_id);
	
			
			
			var result_id =   $( $.xo.validation.config ).find( 'validation#' + val_id + ' result-prefix').text() +   id;
			var btn_id =   $( $.xo.validation.config ).find( 'validation#' + val_id + ' submit-id').text();
			
			// check whether to use notice
			var use_notice = $( $.xo.validation.config ).find( 'validation#' + val_id + ' use-notice').text() == 'yes' ? true : false;
			
			// check whether to show msg in field
			var msg_in_field = $( $.xo.validation.config ).find( 'validation#' + val_id + ' msg-in-field').text() == 'true' ? true : false;
			
			
			// show an error message
			if ( use_notice )
				$( '.notice').addClass('error').html( msg );
			else if ( msg_in_field )
				$( '#' + id ).addClass('error').val(msg);
			else
				$( msg_id ).removeClass('success').addClass('error').html( msg);
			
			// show error result visual
			var highlight_field = $( $.xo.validation.config).find( 'validation#' + val_id + ' highlight-field').text();
			if ( highlight_field == 'yes')
				$( '#' + id ).addClass('error');
			else
			$( result_id ).removeClass('success').addClass('error');
			
			
			// flag error condition
			$.xo.validation.error = true;
		
		},
		
		// clear an error condition
		"clear_error" : function( id ) {
	
			var msg_id = $( $.xo.validation.config ).find( 'msg-prefix').text()  + id;
			var btn_id =   $( $.xo.validation.config ).find( 'submit-id').text();
			
			// show an error message
			$( msg_id ).removeClass('success').addClass('success').html( '');
			
		
			// flag error condition
			$.xo.validation.error = false;
		
			
			
		},
		
		// process unique check
		"process_unique_check" : function( data, id, val_id ) {
			var msg_id = $( $.xo.validation.config ).find( 'validation#' + val_id + ' msg-prefix').text()  + id;

			// check whether to use notice
			var use_notice = $( $.xo.validation.config ).find( 'validation#' + val_id + ' use-notice').text() == 'yes' ? true : false;
			

			//console.log('processing unique check with data = ' + data + ' and val id = ' + val_id );
		
			if ( data == 'yes' )
				$.xo.validation.set_error( id, 'already registered', val_id);
			else {
				var field = use_notice ? '.notice' : msg_id;
				
				$( field ).removeClass('error').removeClass('success').html('');
			}
		},
		
		// check uniqueness
		"check_unique" : function ( id, rule, val_id ) {
		
			//console.log( 'checking unique');
			
			var msg_id = $( $.xo.validation.config ).find( 'validation#' + val_id + ' msg-prefix').text()  + id;
		
			var data = rule.substring( 7 ).split( '.');
			var key = data[0];
			var field = data[1];


			// indicator
			$( msg_id ).html('checking...');

		
			// use the object factory exists method to check if exists
			//var query = field + "='" + $( '#' + id ).val() + "'";
			var query = field + ':' + $( '#' + id ).val();
			$.xo.object_factory.exists ( key, query  , $.xo.validation.process_unique_check, id , val_id );
		},
		
	
		// check a field
		"check_field" : function( e, elem, val_id ){
		
		
			// by default, an empty value is not allowed
			var emptyok = false;
		
			// get the id of the field to check
			var id = $( elem).attr('id');

			if ( $.xo.debug || $.xo.validation.debug )
				console.log( "validation: checking field id=" + id);


			// get the id of the result field to show the result
			var result_id =   $( $.xo.validation.config ).find( ' validation#' + val_id + ' result-prefix').text() +   id;
			
			// show error result visual
			// skip if field hidden
			if ( $( elem ).is(':hidden')) {
				$( result_id ).removeClass('error').addClass('success');
				return;
			}
			
			// for now don't skip unique check
			var skip_unique = false;
			
			// for now, no field error
			var field_error = false;
			
			// get the message field to show messages
			var msg_id = $( $.xo.validation.config ).find( 'validation#' + val_id + ' msg-prefix').text()  + id;

			
			// clear result id
			$( result_id ).removeClass('error').removeClass('success');
			// clear msg id
			$( msg_id ).removeClass('error').removeClass('success').html('');
			// clear field id
			$( '#' + id ).removeClass('error');
			
			// get the rules
			var id_regex = $( $.xo.validation.config ).find( 'validation#' + val_id + ' id-regex').text();
			
			var rules = '';
			if ( id_regex ){
				var matches = /[0-9]+_([a-z|_]+)/.exec( id );
				//console.log( matches );
				var fieldid = matches[1];
			} else
				fieldid = id;
				
			var rules = $( $.xo.validation.config ).find( 'validation#' + val_id + ' fields field#' + fieldid ).attr('rules').split(',');

			// candidate value
			var candidate = $( elem ).val();
			if ( $( elem ).nodeName == 'select' )
				candidate = $( elem + ' option:selected').val();
			
			// loop through rules
			for ( var i in rules ) {
				
				if ( $.xo.debug || $.xo.validation.debug )
					console.log( 'checking rule = ' + rules[i] );
				
				// case: neq:'value' field must not equal a specific value
				if ( /neq\:\'(.*)\'/.test( rules[i] ) ) {
					
					var matches = /neq\:\'(.*)\'/.exec( rules[i] );
					
					if ( matches[1] == candidate ) {
						$.xo.validation.set_error( id, 'please enter a value' , val_id);
						skip_unique = true;
						field_error = true;
					
					
					}
					
				}
				
				// if using a callback
				if ( /callback/.test( rules[i] ) ) {
				
					var callback = rules[i].substring( 9 );

					// invoke callback
					$.xo.call_by_name( callback, window, candidate);

				}
				
				// check matches another field
				if ( /matches/.test( rules[i] ) ) {
				
					var raw = rules[i].split(":");
					var other_field = raw[1];
					
					if ( candidate != $( '#' + other_field).val() ) {
						$.xo.validation.set_error( id, 'must match ' + other_field , val_id);
						skip_unique = true;
						field_error = true;
					
					}
					
				
				}
				
				// password check
				if ( ! emptyok || ( emptyok && candidate != '' ) && /password/.test( rules[i] ) ) {
				
					var raw = rules[i].split(":");
					var level = raw[1];
					
					switch ( level  ) {
					
						case 'moderate':
						
							if ( candidate.length < 6 ) {
								$.xo.validation.set_error( id, 'password too short' , val_id);
								skip_unique = true;
								field_error = true;
							}
							
						break;
					
					}
				
				
				}
				
				// check a date
				if ( /date:([a-z|_]+)/.test( rules[i] ) ) {
				
					var matches = /date:([a-z|_]+)/.test( rules[i] );
					var format = matches[1];
					
					switch ( format ) {
					
						case 'us_slashes':
						
							if ( ! /([0-9]+)\/([0-9]+)\/([0-9]+)/.test( candidate ) ){
							
								$.xo.validation.set_error( id, 'must be date format m/d/y' );
								skip_unique = true;
								field_error = true;
							}
						
						break;
					
					}
				
				}
			
				// check rules based on some regex
				if ( /date_after/.test( rules[i] ) ) {
				
					var dependent_field = rules[i].substring( 11 );
					
					var dependent_date = new Date( $( '#' + dependent_field ).val() );
					
					var depending_date = new Date ( candidate );
				
					if ( dependent_date > depending_date ) {
						
						$.xo.validation.set_error( id, 'must end after start date' , val_id);
							skip_unique = true;
							field_error = true;
					

					}
				}

				// check rules based on some regex
				if ( /time_after/.test( rules[i] ) ) {
				
					var dependent_field = rules[i].substring( 11 );
					
					if ( id_regex ){
						var prefix = $( $.xo.validation.config).find( 'validation#' + val_id + ' id-prefix').text();
						var matches = /([0-9]+)_([a-z|_]+)/.exec( id );
						dependent_field = prefix + matches[1] + '_' + dependent_field;
						if ( $.xo.validation.debug )
							console.log( "$.xo.validation.check_field: dpendent field = " + dependent_field);
					}
					
					var dependent_time = parseInt( $( '#' + dependent_field + ' option:selected').val() );
					
					if ( $.xo.validation.debug )
						console.log( 'dependent_time = ' + dependent_time );
					
					var depending_time = parseInt( candidate );

					if ( $.xo.validation.debug )
						console.log( 'depending_time = ' + depending_time );
					
					if ( dependent_time > depending_time ) {
						
						$.xo.validation.set_error( id, 'must end after start time' , val_id );
							skip_unique = true;
							field_error = true;
					

					}
				}

				
				switch( rules[i] ) {
				
					// must be embedded youtube video code
					case 'youtube':
					
						if ( ! emptyok || ( emptyok && candidate != '' ) )
							if ( ! /iframe/.test( candidate ) || ! /youtube/.test( candidate)) {
								$.xo.validation.set_error( id, 'must be youtube embedded code', val_id);
								skip_unique = true;
								field_error = true;
						
							}
					
					break;
				
				
					// must be a simple us phone
					case 'usphone':
					
						if ( ! /^[0-9]{3}\-[0-9]{3}\-[0-9]{4}$/.test( candidate ) ) {
							$.xo.validation.set_error( id, 'invalid phone format', val_id);
							skip_unique = true;
							field_error = true;
						
						}
					
					break;
				
					// must be a valid us zip code or +4
					case 'zipcode':
					
						if ( ! /^[0-9]{5}(\-[0-9]{4})?$/.test( candidate ) ) {
							$.xo.validation.set_error( id, 'must be a zip code', val_id);
							skip_unique = true;
							field_error = true;
						}
					
					break;
				
					// must be a decimal, or degrade to an integer
					case 'decimal':
					
						if ( ! /^[0-9]+(\.[0-9]{1,2})?$/.test ( candidate ) ) {
							$.xo.validation.set_error( id, 'must be a number', val_id);
							skip_unique = true;
							field_error = true;
						}
					
					break;
				
					// if empty is ok, skip other rules
					case 'emptyok':
						emptyok = true;
						
					break;

					// check the presence of a value
					case 'presence':
					
						if ( candidate == '' ) {
							//console.log( 'failed validation presence');
							$.xo.validation.set_error( id, 'requires a value' , val_id );
							skip_unique = true;
							field_error = true;
						}
					break;
				
					case 'email':
					
						if ( emptyok && candidate == '')
							continue;
					
						if ( ! /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/.test( candidate ) ) {
							$.xo.validation.set_error( id, 'invalid email' , val_id );
							skip_unique = true;
							field_error = true;
						}
						
						
					break;
					
					case 'url':

						if ( ! /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( candidate) ) {
							$.xo.validation.set_error( id, 'invalid url' );
							field_error = true;
						}
						
					break;
					
					default:
					
						// unique
						if ( emptyok && candidate == '' )
							continue;
						if (  ! skip_unique && /unique/.test( rules[i] ) ) {
							//console.log( 'need to check uniqueness...');						
							$.xo.validation.check_unique( id, rules[i] , val_id );
						}
							
					break;
				
				}
			
			
			}
			
			// if no error, unlock button
			if ( ! field_error ) {
				// show error result visual
				$( result_id ).removeClass('error').addClass('success');
				
				 
			}
			
			//console.log('done checking field ' + id );
			
		},
		
		// all succes
		"all_success" : function( val_id ) {
		
			if ( $.xo.debug || $.xo.validation.debug )
				console.log( "validation::all_success() val_id = " + val_id );
		
			var result = true;
			
			$( $.xo.validation.config ).find('validation#' + val_id + ' fields field').each( function() {
			
				var result_id = $( $.xo.validation.config ).find( 'validation#' + val_id + ' result-prefix').text()  +  $(this).attr('id');
				if ( ! $( result_id).hasClass('success') ) {
					//console.log( 'failed on all success');
					result = false;
				}

			});
			
			return result;
		},
		
		// check all fields
		"check_all" : function( val_id ) {
		
			if ( $.xo.debug || $.xo.validation.debug )
				console.log('validation: checking all for validation id = ' + val_id);
		
			// step 1: bind field events for checking
			$( $.xo.validation.config ).find('validation#' + val_id + ' fields field').each( function() {
			
				var id = $( this ).attr('id');
				
				if ( $.xo.debug || $.xo.validation.debug )
					console.log('validation: checking field id = ' + id);
		
			
				$.xo.validation.check_field( false, $( '#' + id ), val_id );
			
			});
			
			// if pending submit and no errors, submit
			if ( $.xo.validation.pending_submit && ! $.xo.validation.field_error ) {
			
				// intercept submit
				var form = $( $.xo.validation.config ).find('validation#' + val_id + ' form-name').text();

				// trigger submit
				//$( form).submit();
			
			}
			
		
		},
	
		// initialize
		"init" : function( xml ) {
		    
			// save config
			var config = $.xo.validation.config = $.xml2json( xml );
			
			// reset error condition
			$.xo.validation.error = false;
			
			var context = '';
			if ( $.xo.context != undefined && $.xo.context != '' )
				context =  $.xo.context;
			else context = '';
			
			console.log( config );
			if ( $.xo.debug || $.xo.validation.debug )
				console.log("xo:validation:init context =  " + context );

			
			// set up each one
			for ( var i in config )
			    console.log( config[i]);
			$( $.xo.validation.config ).find('validation').each( function() {

				// set the validation id
				var val_id = $( this ).attr('id');

				// get the context
				var this_context = $( this).attr('context');
				
				// if not matching, skip it
				if ( this_context != '' && this_context != context ) {
				
					// skip
					//console.log( "xo:validation:init: skipping validation not in context id = " + val_id);
					
				} else {
				
			
				
					if ( $.xo.debug || $.xo.validation.debug )
						console.log( "validation: found id = " + val_id );
						
					// check for formless validation
					var type = $( this).attr('type');
					if ( type == 'formless' && $.xo.validation.debug ) {
						console.log( "$.xo.validation.init(): found FORMLESS validation " + val_id );
						$.xo.validation.formless_init( val_id );
					} else {
		
						// intercept submit
						var form = $( this ).find('form-name').text();
						
						if ( $.xo.debugie )
							alert( "xo:validation form = " + form);
				
						// custom submit handler
						var submit_handler = $(this ).find('submit-handler').text();

						$( form ).live( "submit" , function(e) {
						
							e = e ? e : window.event;
						
							if ( $.xo.debugie )
								alert( "xo:validation:form submitted");
		
							// validate all 
							if ( $.xo.validation.all_success( val_id ) == true ) {
						
								if ( submit_handler != '' ) {
									$.xo.call_by_name( submit_handler, window, e, this);
									return false;
								} else {
									if ( $.xo.debug )
										console.log( 'xo:validation:submit: form is not using custom handler');
									return true;
								}
							} else {
						
								e.preventDefault();
								$.xo.validation.pending_submit = true;
								$.xo.validation.check_all( val_id );
								return false;
							}
							
							
						});
		
						// step 1: bind field events for checking
						// but only if defined on current page
						if ( $( form).length )
							$( this ).find('fields field').each( function() {
				
							// bind field to event handler
							var id = '#' + $( this).attr('id');
							var event = $( this).attr('event');
				
							$( id ).live( event, function(e){ 
				
								// check field when event fires
								$.xo.validation.check_field(e, this, val_id); } ); 
					
							});
						else {
				
							if ( $.xo.debug || $.xo.validation.debug )
								console.log("xo:validation:init: skipping form " + form + " which is not on this page");
				
						}
			
						// if any fields are not empty, perform a check on them
						// step 1: bind field events for checking
				
						// only do if form is defined on page
				
						if ( $( form ).length && $( this).find( 'skip-preval').text() != 'yes' ) 
							$( this ).find('fields field').each( function() {
			
							if ( $.xo.debug || $.xo.validation.debug )
								console.log('checking fields');
					
							var id = '#' + $( this).attr('id');

							if ( $( id ).val() != '' || /emptyok/.test( $( this).attr('rules') ) )					
								$.xo.validation.check_field( false, $( id ), val_id ); 
					
						});
			
					}
					
				}
				
			});
		
		},
		
		// init for formless validation
		"formless_init" : function( id ) {
		
			// step 1 find fields and bind events
			$( $.xo.validation.config ).find( 'validation#' + id + ' fields field').each( function() {
			
				// get class event and rules
				var classname = $( this).attr('class');
				var event = $(this).attr('event');
				var rules = $( this).attr('rules');
				
				// bind
				$( '.' + classname ).live( event,function(e){ 
				
					// check field when event fires
					$.xo.validation.check_field(e, this, id); 
				}); 
					
				
			});
		
		}
		
		
	
	},
	
	// recordset module
	"recordset" : {
	
		"create" : function( key, query, view, none_view , callback ){
		
			// load the object using the api
			$.ajax({
			
				url : 'realobjects/ajax/realobjects.ajax.php',
				
				data : 'action=recordset_create&key=' + key + '&query=' + query + '&view=' + view + '&none_view=' + none_view,
				
				success : function( data ) {
				
					callback( data );
				
				}
			});
			
		}
	

		
		
	
	},
	
	// object factory module
	"object_factory" : {
	
		// check if an object exists given a query
		"exists" : function( key, query, callback , arg1, arg2 ) {
		
			// check using api
			$.ajax({
			
				//url : '/realobjects/ajax/realobjects.ajax.php',
				url : 'api/business/' + key + '/' + query + '/exists' ,
//				url : '/qwidget/server/realobjects/ajax/realobjects.ajax.php',
				data : 'action=objectfactory_exists&key=' + key + '&query=' + encodeURIComponent( query ),
				type : 'POST',
				success : function( data ) {
				
					callback( data , arg1, arg2);
				
				}
			});
			
		},
	
		// update an object 
		"update" : function( key, query, update, callback, target ) {
		// load the object using the api
			$.ajax({
			
				url : 'realobjects/ajax/realobjects.ajax.php',
				
				data : 'action=objectfactory_update&key=' + key + '&query=' + query + '&update=' + update,
				
				success : function( data ) {
				
					callback( data , target );
				
				}
			});
			
		},
	
		// get a specific object
		"get" : function( 
			key, 			// the key of the type of object to search for
			query, 			// the query for the specific object
			view , 			// the view to display the object
			target, 		// target for placing (optional)
			callback 		// callback to send html (optional)
		) {
		
			// load the object using the api
			$.ajax({
			
				url : 'realobjects/ajax/realobjects.ajax.php',
				
				data : 'action=objectfactory_get&key=' + key + '&query=' + query + '&view=' + view,
				
				success : function( data ) {
				
					// either place data in target, or send to callback
					if ( typeof( callback) != 'undefined' ) {
						if ( $.xo.debug )
							console.log('calling back...' + callback);
							callback( data );
					}
					else {
						if ( $.xo.debug )
							console.log('placing data directly...');
							$( target ).empty().append( data );
					}
				
				}
				
			
			});
			
			
		}
	
	},
	
	// search module
	"search" : {
	
	
		"settings" : false,
	
	
		// configure from xml
		"configure" : function ( xml ) {
		
			if ( $.xo.debug )
				console.log('configuring search');
		
			$.realobjects.search.settings = xml;
				
			
		},
	
		// instant search handler
		"instant_search" : function( e, input_elem ) {

			var activate = $( $.realobjects.search.settings).find('activate').text();
			
			var code; // set the variable that will hold the number of the key that has been pressed.
 
			if ( !e )
				e= window.event;
			code = e.keyCode;
 

		if(code != 13 && activate == 'enter' ) { //Enter keycode
				
				return false;
			}

			// get optional search field
			var field = $( $.realobjects.search.settings).find('search-field').text();
			var fval = $( field + ' option:selected').val() != undefined ? $( field + ' option:selected').val() : '' ;
			
			if ( $.xo.debug )
				console.log( 'search field selector value = ' + fval );
			
			// get optional search op
			var op = $( $.realobjects.search.settings).find('search-op').text();
			var oval = $( op + ' option:selected').val() != undefined ? $( op + ' option:selected').val() : '';
			
			// as long as we have  query
			var query = encodeURIComponent ( fval + oval + "'" + $( input_elem ).val() + "'" );
			if ( fval == '')
				query = encodeURIComponent( $(input_elem).val() );
			
			if ( $.xo.debug )
				console.log( 'xo search query = ' + query );

				
			var ajax = $( $.realobjects.search.settings).find('ajax').text();
			
			// if requested use built in API
			if ( $( $.xo.search.settings).find('api').text() == 'x-objects' )
				ajax = 'realobjects/ajax/realobjects.ajax.php';
			
			if ( query != '' ) 
				$.ajax({ 
			
					url : ajax,
					data : 'action=search&view=' + $( $.realobjects.search.settings).find('view').text()  + 
						'&key=' + $( $.realobjects.search.settings).find('key').text() + 
						'&query=' + query,
					success: function( data ){
						
						var target = $( $.realobjects.search.settings).find('target').text();
						
						$( target ).empty().append( data ).fadeIn('fast');
						$( target + ' div:nth-child(even)').addClass('even');
						
						// optional: callback after search completes
						var callback = $( $.realobjects.search.settings).find('callback').text();
						
						if ( callback != '' )
							$.realobjects.call_by_name( callback, window, data);
						
					}
			
				});
		
		}
	},
	
	// call a function by name
	"call_by_name" : function (functionName, context /*, args */) {
  
		if ( $.xo.debug )
			console.log( "xo:call_by_name: invoked! for " + functionName);

		if ( $.xo.debugie )
			alert( "xo:call_by_name: invoked! for " + functionName);
			
		var args = Array.prototype.slice.call(arguments,2);

		var namespaces = functionName.split(".");
		
		var func = namespaces.pop();
		var halt = false;
		for(var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
			if ( typeof(context)=='undefined'){
				console.log( "x-objects: call_by_name(): " + namespaces[i] + " is not defined. please check your app's jquery extension.");
				halt = true;
			}
		}
		
		//alert( "context func = " + context[func] );
		if ( ! halt )
			if ( typeof( context[func] ) == 'undefined') {
				console.log( "x-objects: call_by_name(): " + func + " is not defined. please check your app's jquery extension.");
			} else {
				var func = context[func].apply(this,args);
				return func;
			
			}
	},

	// apply a binding
	"apply_binding" : function ( xml ) {
	
		var query = $( xml).attr('query');
		var bind = $( xml).attr('bind');
		var handler = $( xml).attr('handler');
		var propagate = $( xml ).attr('propagate');
		
		//if ( $.xo.debugie )
			//alert( "xo:apply_binding: query, bind, handler = " + query + ' ' + bind + ' ' + handler );
				
		// special handling for hover
		if ( bind == 'hover' ) {
		
			// mouse enter
			$( query ).live( 'mouseenter' ,
				function(event) { 
					
				// handling for ie
				event = event ? event : window.event;

				// stop propagation
				event.stopPropagation();
						
				$.realobjects.call_by_name( handler, window, event , this, 'in');

			});
			
			// mouse leave
			$( query ).live( 'mouseleave',
					function(event) { 
					
						// handling for ie
						event = event ? event : window.event;
						
						if ( $.xo.debug )
							console.log( "xo:bind_jquery: hover event intercepted!");
	

						// stop propagation
						event.stopPropagation();

						$.realobjects.call_by_name( handler, window, event , this, 'out');

					
			});
		}	
			else $( query ).live( bind , function(event) {
			
				event = event ? event : window.event;
					
				//if ( propagate == 'no')
//					event.stopPropagation();
				
				if ( $.xo.debug )
					console.log( "xo:bind_jquery: event intercepted!");
						
				
				$.realobjects.call_by_name( handler, window, event , this);
						
			});
	
	},
	
	// bind document ready jquery
	"bind_jquery" : function( key, xml ) {
			
		if ( $.xo.debug )
			console.log("xo:bind_jquery: about to bind!");
			
		// context specific bindings
		if ( $.xo.context != '' && $.xo.context != undefined) {
		
			if ( $.xo.debug )
				console.log( "xo:bind_jquery: context is set = " + $.xo.context );
		
			$( xml ).find( key + ' context#' + $.xo.context + ' binding').each( function() {
			
				$.xo.apply_binding( this );
			
			});
		
		}
			
		// global context bindings
		
		$( xml ).find( key + ' context#global binding' ).each( function() {
		
			if ( $.xo.debug )
				console.log( "xo:bind_jquery: found binding query = " + $( this).attr('query') );
		
			$.xo.apply_binding( this );
		
				
		});
				
			
	}, 

	
	// populate a select element
	"populateSelect" : function( query, xml ) {
	
		$( xml ).children().each( function() {

			var selected = $(this).attr('selected') == 'selected' ? ' selected="selected" ' : ' ';
			
			$( query ).append( '<option value="' + $( this ).attr('value') + '" ' + selected + '>' + $(this).text() + '</option>');
		});
	},
	
	// get an XML file as a jquery object
	"getXML" : function( key, callback ) {
	
		$.ajax ({
		
			url : key + '.xml',
			dataType: 'xml',
			success: callback
		
		});
	},
	
	// get the browser name
	"browser_name" : function() {
	
		if ( $.browser.msie )
			return "ie";
		if ( $.browser.opera )
			return "opera";
		if ( $.browser.webkit )
			return "webkit";
		if ( $.browser.mozilla )
			return "mozilla";
			
	
	},
	
	// is ie
	"isIE" : function() {
		if ( $.browser.msie )
			return true;
		else return false;
	
	},
	
	// is this ie8?
	"isIE8" : function() {
		if ( $.browser.msie && $.browser.version == 8.0 )
			return true;
		else return false;
		
	},
	
	// is this ie7?
	"isIE7" : function() {
		if ( $.browser.msie && $.browser.version == 7.0 )
			return true;
		else return false;
		
	},
	
	// is this safari?
	"isSafari" : function() {
	
		// fix for safari and chrome
		if ( $.browser.safari && ! /chrome/.test(navigator.userAgent.toLowerCase()) )
			return true;
		else return false;
	},
	
	"isOpera" : function() {
		if ( $.browser.opera )
			return true;
		else return false;
	},
	
	"fixBrowsers" : function () {
	
		if ( $.realobjects.debug )
			alert( '$.realobjects.fixBrowsers(): entering...');

		// fix for ie
		if ( $.browser.msie ) {
		
			
			var version = $.browser.version;
			//alert( 'msie ' + version );
			
			// append classes
			if ( version == 7.0 )
				$( '.browserfix').removeClass('browserfix').addClass('ie7');
				
			else if ( version == 8.0 ) {
				$('.browserfix').removeClass('browserfix').addClass( 'ie8');
			} 
			else if ( version == 9.0 ) {
				$('.browserfix').removeClass('browserfix').addClass( 'ie9');
			} 
			
		}
			
		// fix for chrome
		if ( $.browser.chromium )
			$('.browserfix').removeClass('browserfix').addClass('chrome');
	
		// fix for safari and chrome
		if ( $.browser.safari ) {
			if ( /chrome/.test(navigator.userAgent.toLowerCase()) )
				$('.browserfix').removeClass('browserfix').addClass('chrome');
			else
				$('.browserfix').removeClass('browserfix').addClass('safari');
		}
		
		// fix for opera
		if ( $.browser.opera ) {
			$('.browserfix').removeClass('browserfix').addClass('opera');
		}

	},
	
	// today function to get date
	"today" : function ( what ) {
	
		var date = new Date();
	
	
		switch( what ) {
		
			case 'day':
				return date.getDate();
			break;
			case 'month':
				return date.getMonth();
			break;
			case 'year':
				return date.getFullYear();
			break;
		}
	},
	
	
	"parseWeekday" : function ( dayid ) {
	
		var days = {
		
			0 : 'Sunday',
			1 : 'Monday',
			2 : 'Tuesday',
			3 : 'Wednesday',
			4 : 'Thursday',
			5 : 'Friday',
			6 : 'Saturday'
			
		};
		
		return days[ dayid ];
	},
	
	// gets english month name from an id
	"getMonthName" : function ( id ) {
	
		var names  = {
		
			0 : 'January',
			1 : 'February' ,
			2 : 'March',
			3 : 'April',
			4 : 'May',
			5 : 'June',
			6 : 'July',
			7 : 'August',
			8 : 'September',
			9 : 'October',
			10 : 'November',
			11 : 'December'
		};
	
		return names [ id ];
	},
	
	
	
	"getMonthId" : function ( name ) {
	
		var ids  = {
		
			'january' : 0,
			'february' : 1,
			'march' : 2,
			'april' : 3,
			'may' : 4,
			'june': 5,
			'july' : 6,
			'august' : 7,
			'september' : 8,
			'october': 9,
			'november': 10,
			'december' : 11
		};
	
		return ids [ name.toLowerCase() ];
	},
	
	"firstDayOf" : function( month , year , format ) {
	
		// translate if necessary
		if ( typeof ( month ) == 'number' )
			month = $.realobjects.getMonthName( month );
			
		if ( format == 'numeric' )
			return (new Date( month + ' 1, ' + year )).getDay();
		else return $.realobjects.parseWeekday ( (new Date( month + ' 1, ' + year )).getDay() );
	},
	
	"isPercent" : function( value ) {
	
		return /%/.test( value );
	},
	
	// gets number of days in month
	"daysInMonth" : function( month, year ) {
		return 32 - new Date(year, month, 32).getDate();
	},
	
	// enter pressed on an event
	"enter_pressed" : function (e ) {

		var code = 0;
			
		if ( ! $.realobjects.isIE() )
			code = e.keyCode;
		else code = e.which;
			
		return code == 13;
		
	},
	
	// does the element have one of these classes?  separate classes with a space, like in html
	"hasClassFrom" : function( elem, classlist ) {
		
		if ( typeof( classlist) == 'undefined' || classlist == '' )
			return false;
			
		var classes = classlist.split(' ');
			
		for ( var i in classes )
			if ( $( elem).hasClass( classes[i] ) )
				return true;
			
		return false;
	},
	
	// hover tips module
	"hover_tip" : {
	
		// saved tips
		"tips" : false,

		// helper to hide a notice
		"hide_notice" : function() {
		
		
			if ( $( $.xo.hover_tip.tips).attr('autohide') == 'yes') {
					
				setTimeout( "$('.notice').hide()", 2000);
					
			}

		},
		
		// initialize with xml
		"init" : function ( xml ) {
		
			$.xo.hover_tip.tips = xml;
			
			if ( $.xo.debug )
				console.log( "xo:hover_tip:init: saved tips = " + $.xo.hover_tip.tips );
				
			// bind hover event
			$('.hover-tip').hoverIntent ({
			
				over: function(e) { $.xo.hover_tip.show_tip(e,this,"in");},
				timeout: 2000,
				sensitivity: 2,
				interval: 1000,
				out: function(e) { $.xo.hover_tip.show_tip(e,this,"out");}
			
			});
				
		},
		
		// show a tip
		"show_tip" : function( e, elem, which ) {
		
			
			if ( $('input#disable_tips').val() == "yes" )
				return false;
		
			
			if ( $.xo.debug )
				console.log( "xo:hover_tip:show_tip: entering function " + which );
		
			if ( which == 'in' ) {
			
				// do nothing if the notice is visible
				if ( $('.notice').is(':visible') )
					return false;
			
				var matches = /tip-([a-z\-]+)/.exec( $( elem ).attr('class'));
				var query = matches[1];
				
				// get exclusion class 
				var excludes = $( $.xo.hover_tip.tips).find( query ).attr('exclude');
				
				//set propagation
				var propagate = $( $.xo.hover_tip.tips).find( query ).attr('propagate');
				if ( propagate == 'no')
					e.stopPropagation();
		

				// proceed as long as the element doesn't have an excluded class
				if ( $.xo.hasClassFrom( elem , excludes ) ) 
					return false;
					
				
				$( '.notice').html( $( $.xo.hover_tip.tips ).find( query ).text() );
				
				if ( $( $.xo.hover_tip.tips).attr('floating') == 'yes' ) {
					$('.notice').css('left',e.pageX);
					$('.notice').css('top',e.pageY);
					
					$('.notice').fadeIn('fast', function() { $.xo.hover_tip.hide_notice();  });
					
					
				}
		
				if ( propagate == 'no')
					e.stopPropagation();
		
			} else {

								
			
			}
			
			return false;
		}
	},

	// rotate an element
	"rotate_element" : function( elem ) {
	
		var matches = /rotate-([0-9]+)/.exec( $(elem).attr('class'));
		var angle = parseInt( matches[1]) + 'deg';
		
		console.log( "xo:rotate: angle = " + angle);
		
		$( elem ).css( '-moz-transform', angle + 'deg');
		$( elem ).css( '-o-transform', angle + 'deg');
		$( elem ).css( '-ms-transform', angle + 'deg');
		$( elem ).css( 'transform', angle + 'deg');
		$( elem ).css( 'font-weight', 'bold');
			
	},
	
	// html module
	"html" : {
	
		"select_option" : function( id, value ) {
		
			$( id + ' option').removeAttr('selected');
			$( id + ' option[value="' + value + '"]').attr('selected','selected');
			
		}
	}
	
};

// synonym in preparation of migration to new name
$.xo = $.realobjects;
