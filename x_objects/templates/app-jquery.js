// define your #appname as a jquery extension

$.#appname = {

	// holds the XML for your ##appnamename, once loaded
	"xml" : false,
	
	// callback to execute once x-objects has initialized
	"after_init" : function( e, elem, xml ) {
	
		// save xml for local use
		$.#appname.xml = xml;
	
	},

	"init" : function() {

		// when document is ready...
		$( document ).ready( function() {

			// initialize x-objects
			$.xo.init( '#appname' , $.#appname.after_init );
			
		});
		
	}
};