<?php
//! javascript library bundle
class js_lib_bundle extends magic_object {
	//! construct with list of libs
	public function __construct( $list ){
		global $webroot,$directory_name;
		$libs = explode( "," , $list);
		$this->xhtml = '';
		foreach ($libs as $lib) {
			// determine lib
			switch($lib){
				case 'jquery':
					$src = "jquery-1.6.3.js";
				break;
				case 'jquery-ui':
					$src = "jquery-ui-1.8.12.custom.min.js";
				break;
				case 'xo-base':
				case 'xo-paginate':
				case 'xo-manage':
				case 'ajax-editor':
				case 'xo-livevalidate':
				case 'simple_login':
				case 'xevents':
				case 'jheartbeat':
					$src = "$lib.js";
				break;
				default:
					// just ignore it
					$src = false;
				break;
			}
			// add it
			if ( $src )
				$this->xhtml .= "\t".'<script type="text/javascript" language="javascript" src="'.$webroot.$directory_name.'/js/'.$src.'"></script>'."\r\n";
		}
	}
	//! create it
	public static function create($list){ return new js_lib_bundle($list); }
}
?>