(function( $ ){

  $.fn.validate = function() {
	//console.log( "xo-livevalidate: found a match");
	var matches=/xo-livevalidate-([a-z]+)/.exec( this.attr('class'));
	var which = matches[1];
	$.xo.livevalidate.bind(this.attr('id'),which);	
  };
})( jQuery );

// pagination module 
$.xo.livevalidate = {
     // is it an email?
    "is_email":function(cand){
        return  /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/.test( cand );
    },
	// handlers
	"handlers" : {
		"matches" : function(val,id){
			// get the matched element
			var matches = /matches-([a-z]+)/.exec( $(id).attr('class'));
			var match = matches[1];
			//console.log(match);
			if ( val != $("#"+match).val() )
				$(id).removeClass('xo-livevalidate-valid').addClass('xo-livevalidate-invalid');
			else
				$(id).removeClass('xo-livevalidate-invalid').addClass('xo-livevalidate-valid');
		},
		"password" : function(val,id){
			if ( val.length < 6)
				$(id).removeClass('xo-livevalidate-valid').addClass('xo-livevalidate-invalid');
			else
				$(id).removeClass('xo-livevalidate-invalid').addClass('xo-livevalidate-valid');
		},
		"presence" : function(val,id){
			if ( ! val )
				$(id).removeClass('xo-livevalidate-valid').addClass('xo-livevalidate-invalid');
			else
				$(id).removeClass('xo-livevalidate-invalid').addClass('xo-livevalidate-valid');
		},
		"usphone" : function(val,id){
			if ( ! /^[0-9]{3}[0-9]{3}[0-9]{4}$/.test(val))
				$(id).removeClass('xo-livevalidate-valid').addClass('xo-livevalidate-invalid');
			else
				$(id).removeClass('xo-livevalidate-invalid').addClass('xo-livevalidate-valid');
			}
	},
	// bind a field to a rule
	"bind":function(id,rule){
		if ( typeof( $.xo.livevalidate.handlers[rule] ) == 'undefined')
			console.log("Exception: xo: livevalidate: "+ rule +" is not a validation rule.");
		else
			$("#"+id).bind("keyup",function(e){ 
				$.xo.livevalidate.handlers[rule]($('#'+id).val(),'#'+id); 
		});
	}	
};