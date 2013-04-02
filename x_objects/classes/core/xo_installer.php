<?php
/**
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 06/03/13
 * Time: 09:58 AM
 */
class xo_installer {
    private $debug = true;
    // create a new webapp with a given name
    public function webapp( $name, $root ){
        $name = preg_replace('/\-/','_',$name);
        if ( $this->debug) echo "app name is $name\r\n";
        $cwd = getcwd();
        $dirs = array(
            "css",
            "js",
            "images" ,
            'img',
            "app",
            "app/classes",
            "app/controllers",
            "app/views",
            "app/views/layouts",
            "app/views/pages",
            "app/models",
            "app/xml"
        );



       echo "creating webapp $name in $cwd\r\n";
       if ( file_exists( $name)){
            if ( ! is_dir( $name )){
                die("$name is not a directory!\r\n");
            } else {
            }
        } else {
            mkdir( $name);

        }

        foreach( $dirs as $dir)
            $this->create_dir( "$name/$dir");

        // copy images
        $dir = new xo_file_directory($root.'/templates/img');
        while ( ($file = $dir->next()) != null){
            //echo "file is $file\r\n";
            if ( ! in_array( $file,array('.','..')))
                copy($root.'/templates/img/'.$file,"$cwd/$name/img/$file");
        }

        // now copy over index file, translating vars
        $this->copy_and_replace( "$root/templates/index.newwebapp.php", "$cwd/$name/index.php",
            array(
                "/\_xobjects\_root\_/" => $root,
                "/\_webapp\_root\_/" => "$cwd/$name",

            ));

        // copy over xml config, translating vars
        $this->copy_and_replace( "$root/templates/x-objects.xml", "$cwd/$name/app/xml/x-objects.xml",
            array(
                "/\_appname\_/" => $name,

            ));

        // copy over some controllers
        copy( "$root/templates/controllers/page.php","$cwd/$name/app/controllers/page.php");
        copy( "$root/templates/controllers/home.php","$cwd/$name/app/controllers/home.php");

        // copy over css
        copy( "$root/templates/css/base.css","$cwd/$name/css/base.css");
        copy( "$root/templates/css/layout.css","$cwd/$name/css/layout.css");
        copy( "$root/templates/css/skeleton.css","$cwd/$name/css/skeleton.css");

        // default template
        $this->copy_and_replace( "$root/templates/views/layouts/skeleton.php", "$cwd/$name/app/views/layouts/skeleton.php",
            array(
                "/\_appname\_/" => $name,

            ));

        // page views
        copy("$root/templates/views/pages/e404.php","$cwd/$name/app/views/pages/e404.php" );
        copy("$root/templates/views/pages/home.php","$cwd/$name/app/views/pages/home.php" );

        // default template
        $this->copy_and_replace( "$root/templates/js/app-jquery.js", "$cwd/$name/js/$name.js",
            array(
                "/\_appname\_/" => $name,
            ));
        /* js
            copy("$root/js/script.js","$cwd/$name/js/script.js" );
            copy("$root/js/jquery.js","$cwd/$name/js/jquery.js" );
            copy("$root/js/jquery.x-objects.js","$cwd/$name/js/jquery.x-objects.js" );
        */
        // default template
        $this->copy_and_replace( "$root/templates/classes/service.php", "$cwd/$name/app/classes/$name.php",
            array(
                "/\_appname\_/" => $name,
            ));
        copy("$root/templates/misc/htaccess.txt","$cwd/$name/.htaccess" );

        echo "done\r\n";

    }

    private function copy_and_replace($source, $dest, $subs){
        $in = fopen ( $source, "r");
        $out = fopen( $dest, "w");
        // read in xml as a string
        while ( $data = fgets( $in ) ) {
            // replace app name
            foreach( $subs as $reg => $rep )
                $data = preg_replace( $reg , $rep , $data );
            // save it
            fputs( $out, $data );
        }
        fclose( $in);
        fclose( $out);
    }


    // create a directory
    private function create_dir( $name){
        if ( file_exists( $name)){
            if ( ! is_dir( $name )){
                die("$name is not a directory!\r\n");
            } else {
            }
        } else {
            mkdir( $name);

        }

    }

}
