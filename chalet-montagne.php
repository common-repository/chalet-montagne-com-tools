<?php
/**
 * Plugin Name: Chalet-Montagne.com Tools
 * Plugin URI: http://www.alpium.com/
 * Description: Extension pour afficher dans votre site Wordpress des informations en provenance de Chalet-Montagne.com: (Planning, Tarifs, Formulaire ...)
 * Version: 2.7.8
 * Author: Alpium
 * Author URI: http://www.alpium.com/
 * License: GPL
 */

if ( !function_exists( 'add_action' ) ) {
    exit;
}

//definitions
//ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION +++++ ATTENTION
//maj verssion ligne suivante
if(!defined('CMCI_VERSION')) define( 'CMCI_VERSION', '2.7.8' );
if(!defined('CMCI_PATH')) define( 'CMCI_PATH', plugin_dir_path(__FILE__) );
if(!defined('CMCI_DIR')) define( 'CMCI_DIR', plugin_dir_url(__FILE__) );
if(!defined('CMCI_FILENAME')) define( 'CMCI_FILENAME', plugin_basename( __FILE__ ) );
if(!defined('CMCI_DEBUG')) define( 'CMCI_DEBUG', false );

global $wpdb;
if(!defined('CMCI_TABLE_PREFIX')) define( 'CMCI_TABLE_PREFIX',  $wpdb->prefix . "cmci_" );
if(!defined('CMCI_UPLOAD_PATH')){
    $wpUpload = wp_upload_dir();
    define( 'CMCI_UPLOAD_PATH', $wpUpload['basedir'] );
}
if(!defined('CMCI_UPLOAD_URL')){
    $wpUpload = wp_upload_dir();
    define( 'CMCI_UPLOAD_URL', $wpUpload['baseurl'] );
}

if(!defined('API_HOST')) {
   /* if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") {
        define( 'API_HOST', 'http://local.chalet-montagne.com/app_dev.php/');
    } else {*/
        define( 'API_HOST' ,'https://www.chalet-montagne.com/');
//    }
}

//Required classes
if ( is_admin() ) {

    require_once( CMCI_PATH . 'classes/chalet-montagne-admin.php' );

    new ChaletMontagneAdmin();

    register_activation_hook( __FILE__, array( 'ChaletMontagneAdmin', 'pluginInstall' ) );
    register_deactivation_hook( __FILE__, array( 'ChaletMontagneAdmin', 'pluginDesactivation' ) );
    register_uninstall_hook( __FILE__, array( 'ChaletMontagneAdmin', 'pluginUninstall' ) );
}

require_once( CMCI_PATH . 'classes/chalet-montagne.php' );

new ChaletMontagne();

function cmci_planning_function($atts) {
    if(isset($atts['id'])){

        return ChaletMontagne::getEventById($atts['id']);
    }else{
        esc_html_e( 'Identifiant non valide et/ou vide' , 'chalet-montagne');
    }
}

add_shortcode('cmci_planning', 'cmci_planning_function');

function cmci_tarifs_function($atts) {
    if(isset($atts['id'])){
        return ChaletMontagne::getPricesById($atts['id']);
    }else{
        esc_html_e( 'Identifiant non valide et/ou vide' , 'chalet-montagne');
    }
}

add_shortcode('cmci_tarifs', 'cmci_tarifs_function');


function cmci_voir_page_tarifs_function(){

    ChaletMontagne::load_resources();

    $args = array('post_parent' => get_the_ID(), 'post_status' => 'published', 'post_type' => 'page');
//var_dump($args);
    $pagesEnfants = get_posts( array(
        'post_type' => 'page',
        'post_parent' => get_the_ID(),
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'cmci_page_type',
        'meta_value' => 'tarif'
    ) );
    $page = current($pagesEnfants);
    $url = get_permalink($page->ID);

    return '<a href="'.$url.'" class="btn btn-primary" title="Aller à la page de tarifs et de planning">Voir le planning et les tarifs</a>';

}
add_shortcode('cmci_voir_page_tarifs', 'cmci_voir_page_tarifs_function');

function cmci_voir_galerie_function(){

    ChaletMontagne::load_resources();

    $args = array('post_parent' => get_the_ID(), 'post_status' => 'published', 'post_type' => 'page');
//var_dump($args);
    $pagesEnfants = get_posts( array(
        'post_type' => 'page',
        'post_parent' => get_the_ID(),
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'cmci_page_type',
        'meta_value' => 'galerie'
    ) );
    $page = current($pagesEnfants);
    $url = get_permalink($page->ID);

    return '<a href="'.$url.'" class="btn btn-primary" title="Aller à la page de tarifs et de planning">Voir les photos</a>';

}
add_shortcode('cmci_voir_galerie', 'cmci_voir_galerie_function');

// Shortcode to display contact form
function cmci_contact_function($atts) {

    ChaletMontagne::load_resources();
    $argsModal = array ("id" => "modalReservation",
        "inputStartDisabled" => "",
        "inputEndDisabled" => "",
        "isModal" => false,
        'idLoueur' => get_option('cmci_api_key'),
        'hash' => md5(get_option('cmci_hash_key')),
        'nonce' => ChaletMontagne::NONCECONTACT,
        'prefixInput' => '');

    $listRentals = get_option('cmci_list_rentals');

    if(count($listRentals) > 1){
        // Get all rental informations
        $files = glob(CMCI_UPLOAD_PATH . '/cmci/*', GLOB_BRACE);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $arrayPath = explode('/', $file);
                $idLoc = end($arrayPath);
                $icsFile = current(glob($file . '/*{ics}', GLOB_BRACE));
                if (file_exists($icsFile)) {
                    $icsFileContent = file_get_contents($icsFile);
                    $loc = ChaletMontagne::return_header_ics_string($icsFileContent);
                    $locName = ChaletMontagne::sanitize_ics_string('X-WR-CALNAME', $loc['locName']);
                    $argsModal['listLoc'][$idLoc] = $locName;
                }
            }
        }
    }else{
        $argsModal['idLoc'] = key($listRentals) ;
    }

    $content = '';

    wp_register_script( 'chalet-montagne-front.js', CMCI_DIR . 'assets/js/chalet-montagne-front.js', '', CMCI_VERSION, '' );
    wp_enqueue_script( 'chalet-montagne-front.js');

    if(!empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], ChaletMontagne::NONCECONTACT)){
        if(ChaletMontagne::checkForm($_POST)) {
            $content .= ChaletMontagne::render('notice.front', array('type' => 'contactOK'));
        }else{
            $content .= ChaletMontagne::render('notice.front', array('type' => 'contactNotOK'));
        }
    }
    $content .= ChaletMontagne::render('modal.front', $argsModal);

    return $content;

}
add_shortcode('cmci_contact', 'cmci_contact_function');

function cmci_galerie_function($atts){

$content = '';

//var_dump($atts);

    if(isset($atts['id_page'])) {
        $listImages = unserialize(current(get_post_meta($atts['id_page'], 'meta-image')));
    }


    if(isset($atts['id'])){
        $args = array(
            'post_type'      => array('attachment'),
            'posts_per_page' => -1,
            'post_status'    => 'inherit',
            'meta_key' => 'cmci_location_id',
            'meta_value' => $atts['id'],
            'order' => 'ASC',
            'orderby' => 'ID'

        );
        $wp_query = new WP_Query( $args );
        if ( $wp_query->have_posts() ) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $listImages[] = intval(get_the_ID());
            }
        }
    }


    if(!empty($listImages)){
        $renderImages = array();
        foreach($listImages as $IdImg) {
            $thumbnail = wp_get_attachment_image_url($IdImg);
            $img = wp_get_attachment_url($IdImg);
            $image = get_post($IdImg);
            $renderImages[] = array('thumb' => $thumbnail, 'img' => $img, 'caption' => $image->post_content);

        }
        ChaletMontagne::load_resources();
        ChaletMontagne::loadFancyResources();
        $content .= ChaletMontagne::render('gallery.front', array('images' =>$renderImages));
    }

    return $content;

}
add_shortcode('cmci_galerie', 'cmci_galerie_function');

add_filter( 'cron_schedules', 'cmci_cron_add_every_fifteen_minutes' );

function cmci_cron_add_every_fifteen_minutes( $schedules ) {
    $schedules['every_fifteen_minutes'] = array(
        'interval' => 900,
        'display' => __('Every 15 Minutes', 'textdomain')
    );
    return $schedules;
}

add_filter( 'cron_schedules', 'cmci_cron_add_every_five_minutes' );

function cmci_cron_add_every_five_minutes( $schedules ) {
    $schedules['every_five_minutes'] = array(
        'interval' => 300,
        'display' => __('Every 5 Minutes', 'textdomain')
    );
    return $schedules;
}

//if(has_action('fiveMinutesCheckRentals')) {
add_action('fiveMinutesCheckRentals', array('ChaletMontagneAdmin', 'actionCheckRentals'));
//}

//if(has_action('dailyCheckRentals')){
add_action('dailyCheckRentals', array('ChaletMontagneAdmin', 'actionCheckRentals'));
//}

// Declare daily or five minutes CRON depend on active subscription
if(ChaletMontagneAdmin::isAbonnementActif()) {
    if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event')) {
        if (!wp_next_scheduled('fiveMinutesCheckRentals')) {
            // for dev test we made 'minute' and not 'hourly' for the prod mode
            wp_schedule_event(time()+3600, 'every_five_minutes', 'fiveMinutesCheckRentals');

            if(wp_get_schedule('dailyCheckRentals') != false){
                wp_clear_scheduled_hook('dailyCheckRentals');
            }

        }
    }
}else{
    if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event')) {
        if (!wp_next_scheduled('dailyCheckRentals')) {
            // for dev test we made 'minute' and not 'hourly' for the prod mode
            wp_schedule_event(time()+3600, 'daily', 'dailyCheckRentals');

            if(wp_get_schedule('fiveMinutesCheckRentals') != false){
                wp_clear_scheduled_hook('fiveMinutesCheckRentals');
            }

        }
    }
}

add_action('wp_loaded', array('ChaletMontagneAdmin', 'upgrade_plugin'));