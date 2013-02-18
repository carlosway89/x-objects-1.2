// load jquery asynchonously
$script('/js/jquery.js', 'jquery');
$script("/js/jquery.x-objects.js",'xobjects');
   
_appname_ = {
        "key":"",
        "init": function(key){
             // save key for when needed
            _appname_.key = key;
            // when jquery is ready
            // load twitter async
            $script.ready('jquery',
                function(){
                    $script('/js/jquery.ui.js' ,'jquery_ui');
                    $script('/js/jquery.tmpl.min.js', 'jquery_tmpl');

                    $script.ready('jquery_ui',function(){
                    });
                    $script.ready('xobjects',function(){
                    });
                
                // set up ajax
                $(document).ajaxStart(function(){ 
                });
                $(document).ajaxStop(function() { 
                });
                // when document is ready
                $(function(){
                }); // end document ready
           
            });  
        },
        "bind_events":function(){

        }
};

