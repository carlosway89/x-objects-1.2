<?php
/**
 * @property object $container a pointer to X-Objects container instance (singleton)
 */
$view_key = null;
$page_vars = null;
$controller_name = null;

/**
 * base class to create new controllers
 */
 abstract class xo_controller extends magic_object {
     // controller resources (mainly for ajax)
     protected $resources = null;
 	/**
 	 * render a view
 	 * @param string $key the key to identify the view
 	 * @return void no return value
 	 */
 	 protected function render( $key,$vars = null,$lang = 'en_US' ){
 	 	global $webapp_location,$view_key,$page_vars,$controller_name,$container;
          $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

        if ($container->app_debug) echo "$tag->event_format: about to render view<br>\r\n";
          $view_key = $key;
 	 	$this->view_key = $key;
 	 	$page_vars = $vars;
     	$controller_name = $this->uri->part(1)?$this->uri->part(1):"home";
        // load resources
          if ( file_exists( $webapp_location."/app/resources/$lang/$key.php"))
              require_once($webapp_location."/app/resources/$lang/$key.php");

         // echo "contr=".$controller_name;
 	 	$layout = $this->layout;

          if ( $this->layout ) {
              // load layout resources
              if ($container->app_debug) echo "$tag->event_format: about to load layout $layout<br>\r\n";

              if ( file_exists( $webapp_location."/app/resources/$lang/$layout.php")){
                  require_once($webapp_location."/app/resources/$lang/$layout.php");
                    if ( $container->debug || $container->app_debug)
                        echo "$tag->event_format: loaded resource file $lang $layout<br>\r\n";
              } else {
                  if ( $container->debug || $container->app_debug)
                      echo "<span style='color:red'>$tag->event_format: failed to load resource file $lang $layout</span><br>\r\n";
              }
              $layout = $webapp_location. "/app/views/layouts/".$this->layout.".php";
              if ( file_exists( $layout))
                require_once($layout);
              else {
                  echo "<div style='font-family: Verdana,Helvetica,sans-serif;font-size: 20pt; padding: 10px; margin: 20px auto; width: 700px; height: auto; overflow:hidden ;min-height: 300px; background-color: #565656; color: white'><p>X-Objects says:</p> <p>'Something has not gone well here.</p> <p></p> I simply can't load the Layout <span style='color:#90ee90;'>$this->layout</span>.</p><p>  Are you certain the file $this->layout.php exists in /app/views/layouts?'</p><p style='font-size: 8pt'>Hopefully this is helpful, but if not, please email <a style='color: lightgreen; font-weight: bold' href='mailto:support@x-objects.org'>Our Support Team</a></p> </div>";
              }
              if ($container->app_debug) echo "$tag->event_format: done to load layout $layout<br>\r\n";

          } else {
              if ( $container->debug || $container->app_debug)
                  echo "<span style='color:#ff8c00;'>$tag->event_format: no layout specified</span><br>\r\n";
          }
          if ($container->app_debug) echo "$tag->event_format: done to render view<br>\r\n";


      }
 	
 	/**
 	 * magic get for standard stuph
 	 * @param string $what key of what to get
 	 * @return mixed the stuff or null if nothing found
 	 */
 	 public function __get( $what){
          global $container;
 	 	switch( $what ){
              case 'ses': return new SESSION; break;
              case 'files': return new FILES; break;
              case 'container': return $container; break;
 	 		case 'req': return new REQUEST; break;
 	 	case 'uri': return new REQUEST_URI; break; 
 	 	default: return parent::__get( $what ); break;
 	 		
 	 	}
 	 }
 	 
 	 public function __call( $what, $how){
		global $container;
          $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);

          $mr = $container->xml->site->controllers->missing_redirect;
		if ( $mr){
            if ( ! $container->debug)
                header("Location: /$mr");
            else echo "$tag->event_format: abstaining from redirect in debug mode.  called method was $what with args ". new xo_array($how)."<br>\r\n";
        }
 	 	else
			echo "call to unknown method $what() from ". get_called_class();
 	 }
 	 
 	 public function is_active( $key ) { return $this->view_key == $key? "active":"";}

     public function __construct(){
         $this->resources = new xo_resource_bundle("a_controller");

     }
 }
?>
