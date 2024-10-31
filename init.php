<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Preloader Quotes
Plugin URI:  https://quizleads.io/wp
Description: Create Quotes and show them before page loads
Version:     1.0.0
Author:      Bhuvnesh Gupta
Author URI:  https://www.facebook.com/BhuvneshGupta03
*/

// CONST
define('PRELOADER_LOADING_PAGE_PLUGIN_DIR', dirname(__FILE__));
define('PRELOADER_QUOTE_PLUGIN_URL', plugins_url('', __FILE__));

/**
* Plugin activation
*/
register_activation_hook( __FILE__, 'preloader_quote_install' );
if(!function_exists('preloader_quote_install')){
	function preloader_quote_install()
	{
		global $wpdb;
		$table_name = "preloader_quotes";
		$table_name1 = "preloader_quotes_mapping";
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  quote tinytext NOT NULL,
		  author varchar(500) NOT NULL,
		  template varchar(50) NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );

		$sql1 = "CREATE TABLE $table_name1 (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  quote_ids varchar(1000) NOT NULL,
		  page_ids varchar(1000) NOT NULL,
		  post_ids varchar(9) NOT NULL,
		  random_quotes mediumint(9) NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql1 );
	}
}

add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_menu_page('Preloader Quotes', 'Preloader Quotes', 'manage_options', 'preloader-quotes', 'preloader_quotes' );
    add_submenu_page('preloader-quotes', 'Mapping', 'Mapping', 'manage_options', 'preloader-quotes-mapping','preloader_quotes_mapping' );    
}

if(!function_exists('preloader_quotes')){
    function preloader_quotes(){
    	include_once PRELOADER_LOADING_PAGE_PLUGIN_DIR.'/quotes.php';
	}
}

if(!function_exists('preloader_quotes_mapping')){
	function preloader_quotes_mapping(){
		include_once PRELOADER_LOADING_PAGE_PLUGIN_DIR.'/mapping.php';
	}
}

add_action( 'init', 'preloading_page_init');
if(!function_exists('preloading_page_init')){
    function preloading_page_init(){
        if(!is_admin()){
            add_action('wp_enqueue_scripts', 'preloading_page_enqueue_scripts', 1);
        }

        if(is_admin()){
        	add_action( 'admin_post_preloader_quotes', 'prefix_admin_preloader_quotes' );
			add_action( 'admin_post_nopriv_preloader_quotes', 'prefix_admin_preloader_quotes' );

			add_action( 'admin_post_preloader_quotes_mapping', 'prefix_admin_preloader_quotes_mapping' );
			add_action( 'admin_post_nopriv_preloader_quotes_mapping', 'prefix_admin_preloader_quotes_mapping' );
        }
    } // End loading_page_init
}



if(!function_exists('prefix_admin_preloader_quotes')){
	function prefix_admin_preloader_quotes() {
	    if( !isset( $_POST['pl_nonce_val'] ) || !wp_verify_nonce( $_POST['pl_nonce_val'], 'my_pl_nonce' ) ) return;

	    global $wpdb;

		$pl_quote =  sanitize_text_field($_POST['pl_quote']);
		$pl_quote_author =  sanitize_text_field($_POST['pl_quote_author']);
		$pl_quote_template =  sanitize_text_field($_POST['pl_quote_template']);

		$global_quote_id = '';


		if(!empty($_POST['quote_id'])){
			$query = "update preloader_quotes set quote='".$pl_quote."',author='".$pl_quote_author."', template='".$pl_quote_template."' where id='".$_POST['quote_id']."'";
			$wpdb->query($query);
			$global_quote_id = sanitize_text_field($_POST['quote_id']);
		}else{
			$wpdb->insert('preloader_quotes',array('quote'=>$pl_quote,'author'=>$pl_quote_author,'template'=>$pl_quote_template));

			$global_quote_id = $wpdb->insert_id;
		}


		if(isset($_POST['pl_quote_global']) && $_POST['pl_quote_global'] == '1'){
			update_option('pl_quote_global',$global_quote_id);
		}
		else{
			$global_quote = get_option('pl_quote_global',true);
			if($global_quote == $_POST['quote_id']){
				update_option('pl_quote_global','');
			}
		}

		wp_redirect(admin_url().'admin.php?page=preloader-quotes&message=Quote Saved Succesfully');
		exit;
	}
}

if(!function_exists('prefix_admin_preloader_quotes_mapping')){
	function prefix_admin_preloader_quotes_mapping(){
		global $wpdb;

		if( !isset( $_POST['pl_nonce_val'] ) || !wp_verify_nonce( $_POST['pl_nonce_val'], 'my_pl_nonce' ) ) return;

		$pl_quotes = sanitize_text_field($_POST['pl_quotes']);
		$pl_page = array_map( 'sanitize_text_field', wp_unslash($_POST['pl_page']));
		$pl_post = array_map( 'sanitize_text_field', wp_unslash($_POST['pl_post']));
		$random_quotes = sanitize_text_field( $_POST['random_quotes']);

		foreach($pl_page as $page_id){
			update_post_meta($page_id,'pl_quotes',$pl_quotes);
			update_post_meta($page_id,'pl_quotes_random',$random_quotes);
		}

		foreach($pl_post as $post_id){
			update_post_meta($post_id,'pl_quotes',$pl_quotes);
			update_post_meta($post_id,'pl_quotes_random',$random_quotes);
		}

		if(isset($_POST['mapping_id']) && !empty($_POST['mapping_id'])){
			$wpdb->delete( 'preloader_quotes_mapping', array('id' => sanitize_text_field($_POST['mapping_id'])) );
		}

		$wpdb->query("insert into preloader_quotes_mapping(quote_ids,page_ids,post_ids,random_quotes) values('".$pl_quotes."','".implode(",",$pl_page)."','".implode(",",$pl_post)."','".$random_quotes."')");

		if(isset($_POST['mapping_id']) && !empty($_POST['mapping'])){
			$wpdb->delete( 'preloader_quotes_mapping', array('id' => sanitize_text_field($_GET['delete'])) );
		}

		wp_redirect(admin_url().'admin.php?page=preloader-quotes-mapping&message=Mapping Saved Successfully');
		exit;
	}
}

if(!function_exists('preloading_page_enqueue_scripts')){
    function preloading_page_enqueue_scripts()
	{
		global $post,$wpdb;
		$quote_value = "";
		$author_value = "";
		$template_value = "1";

		if($post->ID){
			$post_id = $post->ID;
			$quote_ids = get_post_meta($post_id,'pl_quotes',true);
			if(empty($quote_ids)){
				$global_quote_id = get_option('pl_quote_global',true);
				if(!empty($global_quote_id)){
					$quote_data = $wpdb->get_row('select * from preloader_quotes where id='.$global_quote_id);
					if(!empty($quote_data)){
						$quote_value = $quote_data->quote;
						$author_value = $quote_data->author;
						$template_value = $quote_data->template;
					}
				}
				else{
					//echo "nothing";
				}
			}else{
				$random_quotes = get_post_meta($post_id,'pl_quotes_random',true);
				$quote_idsA = explode(",", $quote_ids);

				$quote_idsA = array_filter($quote_idsA, function($value) { return $value !== '' && $value !== 'null' && !empty($value); });

				if($random_quotes == '1'){

					function getRandomQuote($quote_idsA){
						global $wpdb;
						$quote_id = $quote_idsA[array_rand($quote_idsA)];
						$quote_data = $wpdb->get_row('select * from preloader_quotes where id='.$quote_id);
						
						if(empty($quote_data)){
							return getRandomQuote($quote_idsA);
						}
						else{
							return $quote_data;
						}
					}

					$quote_data = getRandomQuote($quote_idsA);
					
				}else{

					function getOrderQuote($post_id,$quote_idsA){
						global $wpdb;
						$used_cookies = get_post_meta($post_id,'pl_quotes_used_ids',true);
						$used_cookiesA = explode(",",$used_cookies);

						$remaining_quote_ids = array_diff($quote_idsA, $used_cookiesA);

						if(empty($remaining_quote_ids)){
							// it means all used
							$used_cookies = "";
						}else{
							$quote_id = '';
							foreach($remaining_quote_ids as $remaining_quote_id){
								$quote_id =  $remaining_quote_id;
								break;
							}

							if(!empty($used_cookies)){
								$used_cookies = $used_cookies.",".$quote_id;
							}
							else{
								$used_cookies = $quote_id;
							}
						}

						

						update_post_meta($post_id,'pl_quotes_used_ids',$used_cookies);

						$quote_data = $wpdb->get_row('select * from preloader_quotes where id='.$quote_id);
						if(empty($quote_data)){
							return getOrderQuote($post_id,$quote_idsA);
						}
						else{
							return $quote_data;
						}
					}
					$quote_data = getOrderQuote($post_id, $quote_idsA);
					
				}
				
				if(!empty($quote_data)){
					$quote_value = $quote_data->quote;
					$author_value = $quote_data->author;
					$template_value = $quote_data->template;
				}
			}	
		}
		
		$css_file_name = "preloading_".$template_value.".css";
		wp_enqueue_style('codepeople-preloading-page-style', PRELOADER_QUOTE_PLUGIN_URL.'/css/'.$css_file_name, array(), '1.0.1', false);

		wp_localize_script('jquery', 'pl_quote_value', $quote_value);
		wp_localize_script('jquery', 'pl_author_value', $author_value);
		wp_localize_script('jquery', 'pl_template_value', $template_value);


		wp_enqueue_script('codepeople-preloading-page-script', PRELOADER_QUOTE_PLUGIN_URL.'/js/preloading-page.js', array('jquery') , '1.0.1', false);
		
	}
}

function preloader_quotes_wp_admin_style($hook) {
        // Load only on ?page=mypluginname

		if($hook == 'preloader-quotes_page_preloader-quotes-mapping' || $hook == 'toplevel_page_preloader-quotes') {
                wp_enqueue_style( 'preloader_quotes_bootstrap_css', plugins_url('css/bootstrap.css', __FILE__) );
        }

		

        if($hook != 'preloader-quotes_page_preloader-quotes-mapping') {
                return;
        }

        wp_enqueue_style( 'preloader_quotes_jquery_ui_css', plugins_url('css/jquery-ui.css', __FILE__) );
    
        wp_enqueue_script('jquery-ui-sortable');
}
add_action( 'admin_enqueue_scripts', 'preloader_quotes_wp_admin_style' );