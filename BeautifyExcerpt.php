<?php

/*
Plugin Name: Excerpt Beautifier
Description: Parses markup in excerpts
Version:     1.0.0
Author:      Pieter Hoekstra
Text Domain: bbedit-markup-in-excerpt
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

class BBEditMarkupInExcerpt{

    public function __construct(){

        add_filter( 'get_the_excerpt', array( $this, 'parseBBEditInExcerpt'));
        
        register_activation_hook( __FILE__, array( $this, 'plugin_activate' ));
        
        register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' )); 
    }

    public function plugin_activate(){  
        flush_rewrite_rules();
    }

    public function plugin_deactivate(){
        flush_rewrite_rules();
    }

    /*
    parseBBEditInExcerpt: Parses reduced BBEdit style markup:

        **watch out!** 										becomes   italic        <strong>watch out!</strong>
        *mark this* 										becomes   bold          <em>mark this</em>
        [click here](https://example.com)					becomes   link          <a href='https://example.com'>click here</a>
        [click here](https://example.com hi there)			becomes   link          <a href='https://example.com' title='hi there'>click here</a>
        ![alt](/wp-content/uploads/image.png hover text)	becomes   image         <img src='/wp-content/uploads/image.png' title='hover text' alt='alt'/>

    */
    public function parseBBEditInExcerpt( $str ){
        
        $tags = array( 
            "/\\*{2}([^*]*)\\*{2}/i" 						=> '<em>?</em>',
            "/\\*{1}([^*]*)\\*{1}/i" 						=> '<strong>?</strong>',
            "/\\!\\[([^\\]]*)\\](\\([^)]*\\))/i" 	        => '<img src="?" title="?" alt="?"/>',
            "/\\[([^\\]]*)\\](\\([^)]*\\))/i" 	            => '<a href="?" title="?">?</a>'
        );

        foreach( $tags as $key => $value ){
            $parts = explode( "?", $value );

            if( count( $parts ) == 2 )
                $str = preg_replace( $key, $parts[ 0 ] . "$1" . $parts[ 1 ], $str );

            else if( count( $parts ) == 4 ){
                $m = array();
                preg_match_all( $key, $str, $m );

                foreach( $m[ 0 ] as $key => $orig ){
                    $parts = explode( "?", $value );
                    $title = '';
                    $info = preg_replace( "/\\(|\\)/", "", $m[ 2 ][ $key ] );
                    $infoA = explode( " ", trim( $info ) );
                    $url = $infoA[ 0 ];
                    array_shift( $infoA );
                    if( count( $infoA ) > 0 )
                        $title = str_replace( '"', '' , implode( " ", $infoA ));
                    $str = str_replace( $orig, $parts[ 0 ] . $url . $parts[ 1 ] . $title . $parts[2] . $m[ 1 ][ $key ] .  $parts[ 3 ], $str );
                }
            }
        }

        return $str;
    }
}

new BBEditMarkupInExcerpt();

?>