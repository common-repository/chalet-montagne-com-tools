<?php

require_once( CMCI_PATH . '/classes/chalet-montagne-admin.php' );

/**
 * Front controller
 */
if ( !class_exists( 'ChaletMontagne' ) ) {
    class ChaletMontagne
    {
        const ID = 'cmci';
        const NAME = 'Chalet-Montagne.com Tools';
        const NAME_MENU = 'Chalet Montagne';
        const LEVEL = 'publish_pages';
        const API_PORT = 80;

        const API_METHOD_GET_ID = 'api-wpp';

        const NONCELOC = 'cmci-ask-loc';
        const NONCECONTACT = 'cmci-ask-loc';
        const NONCETARIF = 'cmci-ask-tarif';
        const NONCEPLANNING = 'cmci-ask-planning';

        protected $loaded_textdomain = false;

        static $table_loc=CMCI_TABLE_PREFIX . "loc";
        static $table_planning_resa=CMCI_TABLE_PREFIX . "planning_resa";
        static $table_tarifs=CMCI_TABLE_PREFIX . "tarifs";


        public function __construct(){

            add_action('checkRentals', array('ChaletMontagneAdmin', 'actionCheckRentals'));

            add_action('wp_enqueue_scripts', array('ChaletMontagne', 'load_resources'));

            if(!empty(get_option('cmci_formDateAvailable')) &&
                !empty(get_option('cmci_formDateUnavailable')) &&
                !empty(get_option('cmci_formHeader')) &&
                !empty(get_option('cmci_formCancel'))) {
                add_action('wp_head', array('ChaletMontagne', 'loadCustomStyle'));
            }
        }

        /**
         * Init
         * @return void
         */
        public static function init(){
            // init the short code to display data in the front side
            add_shortcode('chalet-montagne', array('ChaletMontagne', 'shortcode'));
            add_action('wp_enqueue_scripts', array( 'ChaletMontagne', 'load_resources'));

        }

        /**
         * Load external ressources (css and js)
         * @return void
         */
        public static function load_resources() {
            global $hook_suffix;

            if (!in_array( $hook_suffix, array(
                'settings_page_cmci-key-config',
            ) ) ) {

                // jquery ui
                wp_register_style( 'jqueryui-min.css', CMCI_DIR . 'assets/css/jquery-ui.min.css', array(), CMCI_VERSION );
                wp_enqueue_style( 'jqueryui-min.css');

                wp_enqueue_script('jquery-ui-datepicker');

                wp_register_script( 'jquery.ui-datepicker-fr.js', CMCI_DIR . 'assets/js/jquery.ui-datepicker-fr.js', array('jquery'), CMCI_VERSION, false );
                wp_enqueue_script( 'jquery.ui-datepicker-fr.js');

                // bootstrap
                wp_register_style( 'bootstrap.min.css', CMCI_DIR . 'assets/css/bootstrap.min.css', array(), CMCI_VERSION );
                wp_enqueue_style( 'bootstrap.min.css');

                wp_register_script( 'bootstrap.min.js', CMCI_DIR . 'assets/js/bootstrap.min.js', array('jquery'), CMCI_VERSION, true );
                wp_enqueue_script( 'bootstrap.min.js');

                // custom style
                wp_register_style( 'chalet-montagne-front.css', CMCI_DIR . 'assets/css/chalet-montagne-front.css', array(), CMCI_VERSION );
                wp_enqueue_style( 'chalet-montagne-front.css');
            }
        }

        /**
         * Get the user api key
         * @return string the api key
         */
        public static function get_api_key() {
            return apply_filters( 'cmci_get_api_key', defined('CMCI_API_KEY') ? constant('CMCI_API_KEY') : get_option('cmci_api_key') );
        }

        /**
         * Get the user hash key
         * @return string the hash key
         */
        public static function get_hash_key() {
            return apply_filters( 'cmci_get_hash_key', defined('CMCI_HASH_KEY') ? constant('CMCI_HASH_KEY') : get_option('cmci_hash_key') );
        }

        /**
         * Update alert data
         * @param  array $response
         * @return voild
         */
        private static function update_alert( $response ) {
            $code = $msg = null;
            if ( isset( $response[0]['x-cmci-alert-code'] ) ) {
                $code = $response[0]['x-cmci-alert-code'];
                $msg  = $response[0]['x-cmci-alert-msg'];
            }

            // only call update_option() if the value has changed
            if ( $code != get_option( 'cmci_alert_code' ) ) {
                if ( ! $code ) {
                    delete_option( 'cmci_alert_code' );
                    delete_option( 'cmci_alert_msg' );
                }
                else {
                    update_option( 'cmci_alert_code', $code );
                    update_option( 'cmci_alert_msg', $msg );
                }
            }
        }

        /**
         * Create a short code
         * @param  array $atts Attributes of the shortcode
         * @return string       the shortcode view
         */
        public static function shortcode($atts){
            if(!ChaletMontagne::get_api_key())
                return;

            $args = array(
                'content' =>  self::find_the_content($atts)
            );

            return self::view('index.front', $args);
        }

        /**
         * Find data in the database
         * @param array The attributes (mainly the location id)
         * @return array data
         */
        public static function find_the_content($atts = ''){
            if(is_array($atts) && array_key_exists('id', $atts)){
                return ChaletMontagne::find_content_by_rental_id($atts['id']);
            }else{
                return ChaletMontagne::find_all_content();
            }
        }

        public static function find_content_by_rental_id($rentalId){
            global $wpdb;

            $args = array();

            $all_data = $wpdb->get_results(
                "SELECT * FROM ".ChaletMontagne::$table_loc
            );

            foreach ($all_data as $k => $r) {
                $content = unserialize($r->content);
                if($content->id == $rentalId){
                    $args[0]['content'] = $content;
                    $args[0]['id'] = $r->id;
                }
            }

            return $args;
        }

        public static function find_content_by_id($id){
            global $wpdb;

            $data = $wpdb->get_results(
                "SELECT * FROM ".ChaletMontagne::$table_loc." WHERE id = $id"
            );

            $dataToReturn = array();

            foreach ($data as $key => $row) {
                $dataToReturn[$key]['content'] = unserialize($row->content);
                $dataToReturn[$key]['id'] = $row->id;
            }

            return $dataToReturn;
        }

        public static function find_all_content(){
            global $wpdb;

            $data = $wpdb->get_results(
                "SELECT * FROM ".ChaletMontagne::$table_loc.""
            );

            $dataToReturn = array();

            foreach ($data as $key => $row) {
                $dataToReturn[$key]['content'] = unserialize($row->content);
                $dataToReturn[$key]['id'] = $row->id;
            }

            return $dataToReturn;
        }

        /**
         * Return a view
         * @param  string $name The name of the view
         * @param  array  $args Arguments
         * @return string       the view
         */
        public static function view( $name, array $args = array() ) {
            foreach ( $args AS $key => $val ) {
                $$key = $val;
            }

            load_plugin_textdomain( 'chalet-montagne', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

            $file = CMCI_PATH . 'views/'. $name . '.php';

            ob_start();
            include( $file );
            $content = ob_get_clean();

            return $content;
        }

        /**
         * Build a query
         * @param  array $args Arguments
         * @return string
         */
        public static function build_query( $args ) {
            return _http_build_query( $args, '', '&' );
        }

        /**
         * Send data to the server
         * @param  string $request the request
         * @param  string $path    the path for the url
         * @param  string $ip      the server ip
         * @return array           the response
         */
        public static function http_post( $request, $url, $ip=null ) {

            $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => $request,
                    'cookies' => array()
                )
            );

            if ( is_wp_error( $response ) )
                return array( '', '' );

            return $response['body'];
        }

        /**
         * Get data from the server
         * @param  string $request the request
         * @param  string $path    the path for the url
         * @param  string $ip      the server ip
         * @return array           the response
         */
        public static function http_get( $path,$request = array(),  $ip=null ) {

            $host = API_HOST;

            $url = '' . $host .  $path . '?';

            $params = '';

            if(!empty($request)){
                $params = http_build_query($request, '', '&');
            }

            $url = $url.$params;

            if(CMCI_DEBUG){
                echo '<pre>';
                var_dump($url);
                echo '</pre>';
            }


            $response = wp_remote_get($url);

            if ( is_wp_error( $response ) ) {

                if($response->errors["http_request_failed"][0] == "cURL error 60: SSL certificate problem: certificate has expired") {
                    $crt_file = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
                    $new_crt_url = 'http://curl.haxx.se/ca/cacert.pem';

                    if (is_writable($crt_file)) {
                        $new_str = file_get_contents($new_crt_url);

                        if ($new_str && strpos($new_str, 'Bundle of CA Root Certificates')) {
                            $up = file_put_contents($crt_file, $new_str);

                            // echo $up ? 'OK: ca-bundle.crt updated' : 'ERROR: can`t put data to ca-bundle.crt';
                        } else {
                            // echo 'ERROR: can\'t download curl.haxx.se/ca/cacert.pem';
                        }
                    } else {
                        // echo 'ERROR: ca-bundle.crt not fritable';
                    }

                    // exit;

                    return self::http_get(self::API_METHOD_GET_ID, $request );
                }

//                return array('', '');
            }

            return $response['body'];
        }

        /**
         * @param $headerStr string header of ics file
         * @return array name of renter and rental
         */

        public static function return_header_ics_string($headerStr) {

            $endPos = strpos($headerStr, 'BEGIN:VTIMEZONE');

            if($endPos === false){
                $endPos = strlen($headerStr);
            }

            $locInfos = substr($headerStr, 0, $endPos);

            $pos1 = strpos($locInfos, 'X-WR-CALNAME:');
            $pos2 = strpos($locInfos, 'X-WR-CALDESC:');
            $length = $pos2 - $pos1;
            $locName = substr($locInfos, $pos1, $length);
            $length = $endPos - $pos2;
            $locDesc = substr($locInfos, $pos2, $length);

            return array('locName' => $locName, 'locDesc' => $locDesc);

        }

        /**
         * @param $tag string needle to replace
         * @param $str string haystack
         * @return string
         */

        public static function sanitize_ics_string($tag, $str) {
            $str = str_replace($tag.':', '', $str);
            $str = str_replace("\n ", '', $str);
            $str = str_replace("\r", '', $str);
            $str = str_replace('\\', '', $str);
            return $str;
        }

        /**
         * @param $icsContent string content of an ics file
         * @return array Events
         */

        public static function return_all_events($icsContent) {
            $begin = strpos($icsContent, 'BEGIN:VEVENT');
            $icsContent = substr($icsContent, $begin);
            $tabEvent = explode('END:VEVENT', $icsContent);
            //var_dump($tabEvent);
            $returnEvents = array();
            $i = 0;
            foreach($tabEvent as $event){
                $tabEventInfo = explode("\n", $event);
                //var_dump($tabEventInfo);
                foreach($tabEventInfo  as $eventInfo){
                    if(strpos($eventInfo, 'DTSTART') !== false){
                        $event = explode(':',$eventInfo);
                        $tabEventBegin = explode('T', $event[1]);
                        $dateEventBegin = $tabEventBegin[0];
                        $timeEventBegin = $tabEventBegin[1];
                        $year = substr($dateEventBegin, 0, 4);
                        $month = substr($dateEventBegin, 4, 2);
                        $day = substr($dateEventBegin, 6, 2);
                        $hour = substr($timeEventBegin, 0, 2);
                        $minute = substr($timeEventBegin, 2, 2);
                        $second = substr($timeEventBegin, 4, 2);

                        $returnEvents[$i]['start'] = $year.'-'.$month.'-'.$day.'T'.$hour.':'.$minute.':'.$second;
                    }

                    if(strpos($eventInfo, 'DTEND') !== false){
                        $event = explode(':',$eventInfo);
                        $tabEventBegin = explode('T', $event[1]);
                        $dateEventBegin = $tabEventBegin[0];
                        $timeEventBegin = $tabEventBegin[1];
                        $year = substr($dateEventBegin, 0, 4);
                        $month = substr($dateEventBegin, 4, 2);
                        $day = substr($dateEventBegin, 6, 2);
                        $hour = substr($timeEventBegin, 0, 2);
                        $minute = substr($timeEventBegin, 2, 2);
                        $second = substr($timeEventBegin, 4, 2);

                        $returnEvents[$i]['end'] = $year.'-'.$month.'-'.$day.'T'.$hour.':'.$minute.':'.$second;
                    }
                }
                $i++;
            }
            return $returnEvents;
        }

        /**
         * Get all events of a rent and display a calendar
         * @param  int $id number of the rent
         */

        public static function getEventById($id) {

            // check if file exist
            if(file_exists(CMCI_UPLOAD_PATH.'/cmci/'.$id.'/planning.ics')){
                $fileContent = file_get_contents(CMCI_UPLOAD_PATH.'/cmci/'.$id.'/planning.ics');

                // Get array of events
                $events = ChaletMontagne::return_all_events($fileContent);
                $arrayEvent = array();
                $arrayStartEvent = array();
                $arrayEndEvent = array();


                // transform to three array
                // arrayEvent contains all full rent day
                // arrayStartEvent contains all first rental day
                // arrayEndEvent contains all end rental day
                foreach($events as $event){

                    $begin = explode('T', $event['start']);
                    $end = explode('T', $event['end']);
                    $objEnd = new DateTime($end[0]);
                    $objEnd = $objEnd->modify('+1 day');

                    $period = new DatePeriod(
                        new DateTime($begin[0]),
                        new DateInterval('P1D'),
                        $objEnd
                    );

                    $arrayPeriod = array();
                    foreach ($period as $key => $value) {
                        $arrayPeriod[] = $value->format('Y-n-j');
                    }

                    if (!in_array($arrayPeriod[0], $arrayEndEvent)) {
                        $arrayStartEvent[] = $arrayPeriod[0];
                    } else {
                        unset($arrayEndEvent[array_search($arrayPeriod[0], $arrayEndEvent)]);
                        $arrayEvent[] = $arrayPeriod[0];
                    }

                    $arrayEndEvent[] = end($arrayPeriod);

                    unset($arrayPeriod[0]);
                    unset($arrayPeriod[count($arrayPeriod)]);

                    $arrayEvent = array_merge($arrayEvent, $arrayPeriod);

                }

                $strEvent = implode('","',$arrayEvent);
                $strEvent = '["'.$strEvent.'"]';

                $strStartEvent = implode('","',$arrayStartEvent);
                $strStartEvent = '["'.$strStartEvent.'"]';


                $strEndEvent = implode('","',$arrayEndEvent);
                $strEndEvent = '["'.$strEndEvent.'"]';


                $boolPluginGratuit = true;
                if(ChaletMontagneAdmin::isAbonnementActif()){
                    $boolPluginGratuit = false;
                }


	            $args = array('events' => $strEvent,
                    'startEvents' => $strStartEvent,
                    'endEvents' => $strEndEvent,
                    'idLoc' => $id,
                    'idLoueur' => get_option('cmci_api_key'),
                    'pluginGratuit' => $boolPluginGratuit,
                    'prefixInput' => 'planning_');


                $argsModal = array ("id" => "modalReservation",
                    "inputStartDisabled" => "inputDisabled",
                    "inputEndDisabled" => "",
                    "isModal" => true,
                    'idLoc' => $id,
                    'idLoueur' => get_option('cmci_api_key'),
                    'hash' => md5(get_option('cmci_hash_key')),
                    'nonce' => self::NONCEPLANNING,
                    'prefixInput' => 'planning_');

                ChaletMontagne::load_resources();

                wp_register_script( 'planning.js', CMCI_DIR . 'assets/js/planning.js', '', CMCI_VERSION, '' );
                wp_enqueue_script( 'planning.js');

                $content = '';

                if(isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], self::NONCEPLANNING)){
                    if(ChaletMontagne::checkForm($_POST)) {
                        $content .= ChaletMontagne::render('notice.front', array('type' => 'contactOK'));
                    }else{
                        $content .= ChaletMontagne::render('notice.front', array('type' => 'contactNotOK'));
                    }
                }

	            //on check si on affiche les modal ou pas
	            $args['pluginParam'] = get_option('cmci_pluginParam');
//                var_dump( $args['pluginParam']);
//                die();
                if (!isset($args['pluginParam'] ["cacheModalPlanning"]) || $args['pluginParam'] ["cacheModalPlanning"] == false)
                {
                    //var_dump("chargement modal");
                    $content .= ChaletMontagne::render('modal.front', $argsModal);
	            }


                $content .= ChaletMontagne::render('planning.front', $args);

                return $content ;


            }else{
                esc_html_e( 'Le fichier n\'existe pas ou l\'ID renseigné n\'est pas bon. ID donné: '.$id , 'chalet-montagne');
            }
        }

        /**
         * Get prices for a rental
         * @param $id Rental id
         */
        public static function getPricesById ($id) {

            //array months
            $mois = array (1 => 'Janvier',
                2 => 'Février',
                3 => 'Mars',
                4 => 'Avril',
                5 => 'Mai',
                6 => 'Juin',
                7 => 'Juillet',
                8 => 'Août',
                9 => 'Septembre',
                10 => 'Octobre',
                11 => 'Novembre',
                12 => 'Décembre'
            );

            // check if file exist
            if(file_exists(CMCI_UPLOAD_PATH.'/cmci/'.$id.'/tarif.json')) {
                $fileContent = file_get_contents(CMCI_UPLOAD_PATH . '/cmci/' . $id . '/tarif.json');

                $objTarifs = json_decode($fileContent);

                $tarifs = array();

                setlocale(LC_TIME, "fr_FR");

                $moisCourant = date('m');
                $annee = date('Y');

                foreach($objTarifs->periodeslibres as $numMois => $tabPeriodesLibres){

                    if(!empty($tabPeriodesLibres)) {
                        $arrayPeriodeLibre = array("semaine" => array(), "courts_sejours" => array(), 'dernieres_minutes' => array());
                        $objNumMois = current($tabPeriodesLibres);
                        $tabNumMois = explode('-', $objNumMois->date_debut);
                        foreach ($tabPeriodesLibres as $periodeLibre) {
                            $arrayTemp = array();
                            if($periodeLibre->prix_base > 0 && $periodeLibre->nb_nuit_mini == 7) {
                                $arrayPeriodeLibre['semaine'][] = array('tarif_semaine' => $periodeLibre->prix_base,
                                    'tarif_weekend' => $periodeLibre->prix_weekend,
                                    'tarif_normal' => $periodeLibre->prix_normal,
                                    'tarif_promo' => $periodeLibre->prix_promo,
                                    'date_debut' => $periodeLibre->date_debut,
                                    'date_fin' => $periodeLibre->date_fin);
                            }
                            else if($periodeLibre->prix_base > 0){
                                $arrayTemp = array('tarif_base' => $periodeLibre->prix_base,
                                    'date_debut' => $periodeLibre->date_debut,
                                    'date_fin' => $periodeLibre->date_fin,
                                    'nb_nuit_mini' => $periodeLibre->nb_nuit_mini);

                                if($periodeLibre->prix_nuit_supp > 0) {
                                    $arrayTemp['prix_nuit_supp'] = $periodeLibre->prix_nuit_supp;
                                }
                                $arrayPeriodeLibre['courts_sejours'][] = $arrayTemp;

                            }
                            if($periodeLibre->prix_promo > 0 && $periodeLibre->prix_semaine == ""){
                                $arrayPeriodeLibre['dernieres_minutes'][] = array('tarif_semaine' => $periodeLibre->prix_semaine,
                                    'tarif_weekend' => $periodeLibre->prix_weekend,
                                    'tarif_normal' => $periodeLibre->prix_normal,
                                    'tarif_promo' => $periodeLibre->prix_promo,
                                    'date_debut' => $periodeLibre->date_debut,
                                    'date_fin' => $periodeLibre->date_fin);
                            }

                        }



                        $tarifs[$mois[$moisCourant].' '.$annee] = $arrayPeriodeLibre;
                    }


                    if($moisCourant == 12){
                        $moisCourant = 1;
                        $annee ++;
                    }else{
                        $moisCourant ++;
                    }

                }

                $boolPluginGratuit = true;
                if(ChaletMontagneAdmin::isAbonnementActif()){
                    $boolPluginGratuit = false;
                }

                ChaletMontagne::load_resources();

                wp_register_script( 'planning.js', CMCI_DIR . 'assets/js/planning.js', '', CMCI_VERSION, '' );
                wp_enqueue_script( 'planning.js');

                $content = '';
                if(isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], self::NONCETARIF)){
                    if(ChaletMontagne::checkForm($_POST)) {
                        $content .= ChaletMontagne::render('notice.front', array('type' => 'contactOK'));
                    }else{
                        $content .= ChaletMontagne::render('notice.front', array('type' => 'contactNotOK'));
                    }
                }
                $argsModal = array("id" => "modalReservationSemaine-".$id,
                    "inputStartDisabled" => "inputDisabled",
                    "inputEndDisabled" => "inputDisabled",
                    "isModal" => true,
                    "idLoc" => $id,
                    'idLoueur' => get_option('cmci_api_key'),
                    'hash' => md5(get_option('cmci_hash_key')),
                    'nonce' => self::NONCETARIF,
                    'prefixInput' => 'tarif_');

                $content .= ChaletMontagne::render('modal.front', $argsModal);

	            //on check si on affiche les modal ou pas
	            $args['pluginParam'] = get_option('cmci_pluginParam');
	            if (isset($args['pluginParam'] ["cacheModalTarif"]) && $args['pluginParam'] ["cacheModalTarif"] == true)
		            $cacheModal = 1;
	             else
		            $cacheModal = 0;

                $content .= ChaletMontagne::render('tarif.front', array('tarifs' => $tarifs, 'idLoc' => $id, 'idLoueur' => get_option('cmci_api_key'), 'urlPluginAssetsFolder' => CMCI_DIR, 'pluginGratuit' => $boolPluginGratuit, 'prefixInput' => 'tarif_', 'idModal' => "modalReservationSemaine-".$id, 'cacheModal' => $cacheModal));

                return $content;

            }else{
                esc_html_e( 'Le fichier n\'existe pas ou l\'ID renseigné n\'est pas bon. ID donné: '.$id , 'chalet-montagne');
            }
        }

        /**
         * Return a view
         * @param  string $name the name of the view to load
         * @param  array  $args
         * @return string
         */

        public static function render ($name, $args = ''){

            if(!empty($args)) {
                foreach ($args as $k => $v) {
                    $$k = $v;
                }
            }

            $file = CMCI_PATH . 'views/'. $name . '.php';

            ob_start();

            include( $file );

            $content = ob_get_clean();

            return $content;
        }

        /**
         * Load external ressources (css and js)
         * @return void
         */
        public static function loadFancyResources() {

            // jquery ui
            wp_register_style( 'fancy.css', CMCI_DIR . 'assets/css/jquery.fancybox.min.css', array(), CMCI_VERSION );
            wp_enqueue_style( 'fancy.css');

            wp_register_script( 'fancy.js', CMCI_DIR . 'assets/js/jquery.fancybox.min.js', array('jquery'), CMCI_VERSION, true );
            wp_enqueue_script( 'fancy.js');
        }


        /**
         *
         */
        public static function loadCustomStyle(){
            ?>
            <style type="text/css">
                .modal-header,
                .sendMail,
                .modal-dialog .modal-footer .btn-primary,
                .modal-dialog .modal-footer .btn-primary:link,
                .modal-dialog .modal-footer .btn-primary:visited,
                .modal-dialog .modal-footer .btn-primary:hover,
                .modal-dialog .modal-footer .btn-primary:active,
                .modal-dialog .modal-footer .btn-primary:focus
                {
                    border-color: <?php echo get_option('cmci_formHeader'); ?> !important;
                    background-color: <?php echo get_option('cmci_formHeader'); ?> !important;
                }

                .btn-secondary.btn-dismiss,
                .btn-primary.btn-dismiss:link,
                .btn-primary.btn-dismiss:visited,
                .btn-primary.btn-dismiss:hover,
                .btn-primary.btn-dismiss:active,
                .btn-primary.btn-dismiss:focus {
                    border-color: <?php echo get_option('cmci_formCancel'); ?> !important;
                    background-color: <?php echo get_option('cmci_formCancel'); ?> !important;
                }

                .calendar .ui-datepicker-group table.ui-datepicker-calendar tr td.loc-disable {
                    background-color: <?php echo get_option('cmci_formDateUnavailable'); ?> !important;
                }

                .calendar .ui-datepicker-group table.ui-datepicker-calendar tr td.loc-available,
                .tarif-semaine, .tarif-normal {
                    background-color: <?php echo get_option('cmci_formDateAvailable'); ?> !important;
                }

                .calendar .ui-datepicker-group table.ui-datepicker-calendar tr .loc-end a.ui-state-default {
                    background: <?php echo get_option('cmci_formDateUnavailable'); ?>; /* Old browsers */
                    background: -moz-linear-gradient(-45deg, <?php echo get_option('cmci_formDateUnavailable'); ?> 50%, <?php echo get_option('cmci_formDateAvailable'); ?> 50%); /* FF3.6-15 */
                    background: -webkit-linear-gradient(-45deg, <?php echo get_option('cmci_formDateUnavailable'); ?> 50%, <?php echo get_option('cmci_formDateAvailable'); ?> 50%); /* Chrome10-25,Safari5.1-6 */
                    background: linear-gradient(135deg, <?php echo get_option('cmci_formDateUnavailable'); ?> 50%, <?php echo get_option('cmci_formDateAvailable'); ?> 50%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo get_option('cmci_formDateUnavailable'); ?>', endColorstr=' <?php echo get_option('cmci_formDateAvailable'); ?>',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
                }

                .calendar .ui-datepicker-group table.ui-datepicker-calendar tr .loc-start a.ui-state-default {
                    background: <?php echo get_option('cmci_formDateUnavailable'); ?>; /* Old browsers */
                    background: -moz-linear-gradient(-45deg, <?php echo get_option('cmci_formDateAvailable'); ?> 50%, <?php echo get_option('cmci_formDateUnavailable'); ?> 50%); /* FF3.6-15 */
                    background: -webkit-linear-gradient(-45deg, <?php echo get_option('cmci_formDateAvailable'); ?> 50%,<?php echo get_option('cmci_formDateUnavailable'); ?> 50%); /* Chrome10-25,Safari5.1-6 */
                    background: linear-gradient(135deg, <?php echo get_option('cmci_formDateAvailable'); ?> 50%,<?php echo get_option('cmci_formDateUnavailable'); ?> 50%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo get_option('cmci_formDateAvailable'); ?>', endColorstr='<?php echo get_option('cmci_formDateUnavailable'); ?>',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
                }
            </style>
            <?php
        }

        /**
         * Get data from the server
         * @param  string $formPost data send by contact form
         * @return bool           the response
         */
        public static function checkForm ($formPost) {
            $args = array();

            $log = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                "Data: ".print_r($_POST, true).PHP_EOL.
                "-------------------------".PHP_EOL;

            if(!empty($formPost['nom'])){
                $nom = sanitize_text_field($formPost['nom']);
                if(!empty($formPost['prenom'])){
                    $prenom = sanitize_text_field($formPost['prenom']);
                    if((!empty($formPost['email']) && filter_var($formPost['email'], FILTER_VALIDATE_EMAIL)) || !empty($formPost['telephone'])){
                        if(!empty($formPost['telephone'])){
                            $telephone =sanitize_text_field($formPost['telephone']);
                        }else{
                            $telephone ='';
                        }
                        if(!empty($formPost['email'])){
                            $email = $formPost['email'];
                        }else{
                            $email = '';
                        }
                        if(!empty($formPost['adulte']) && $formPost['adulte'] > 0){
                            $adulte = $formPost['adulte'];
                            if(!empty($formPost['enfant']) || $formPost['enfant'] == 0){
                                $enfant = $formPost['enfant'];

                                if(!empty($formPost['dateArrivee'])){
                                    $dateArrivee = sanitize_text_field($formPost['dateArrivee']);
                                    if(!empty($formPost['dateDepart'])){
                                        $dateDepart = sanitize_text_field($formPost['dateDepart']);
                                        $commentaire = sanitize_text_field($formPost['commentaire']);
                                        if(!empty($formPost['idLoueur']) && $formPost['idLoueur'] > 0){
                                            $idLoueur = $formPost['idLoueur'];
                                            if(!empty($formPost['idLocation']) && $formPost['idLocation'] > 0){
                                                if(!empty($formPost['h'])){
                                                    $h = $formPost['h'];
                                                    $idLocation = $formPost['idLocation'];

                                                    $tabDateArrivee = explode('/', $dateArrivee);
                                                    $newDateArrivee = $tabDateArrivee[2].'-'.$tabDateArrivee[1].'-'.$tabDateArrivee[0];
                                                    $tabDateDepart = explode('/', $dateDepart);
                                                    $newDateDepart = $tabDateDepart[2].'-'.$tabDateDepart[1].'-'.$tabDateDepart[0];

                                                    $args = array(
                                                        'datetime' => time(),
                                                        'idLoueur' => $idLoueur,
                                                        'idLocation' => $idLocation,
                                                        'nom' => $nom,
                                                        'prenom' => $prenom,
                                                        'email' => $email,
                                                        'telephone' => $telephone,
                                                        'dateDepart' => $newDateDepart,
                                                        'dateArrivee' => $newDateArrivee,
                                                        'adulte' => $adulte,
                                                        'enfant' => $enfant,
                                                        'commentaire' => $commentaire,
                                                        'h' => $h,
                                                        'dateLu' => '0000-00-00 00:00:00'
                                                    );

                                                    $response = ChaletMontagne::http_post($args, get_option('cmci_url_contact'));

                                                    //Something to write to txt log
                                                    $log .= "Attempt: ".print_r($response, true).PHP_EOL.
                                                        "-------------------------".PHP_EOL;
                                                    //Save string to log, use FILE_APPEND to append.
                                                    file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                                    return $response;
                                                }else{

                                                    $log .= "False: hash".
                                                        "-------------------------".PHP_EOL;
                                                    //Save string to log, use FILE_APPEND to append.
                                                    file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                                    return false;
                                                }
                                            }else{

                                                $log .= "False: ID Loc".
                                                    "-------------------------".PHP_EOL;
                                                //Save string to log, use FILE_APPEND to append.
                                                file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                                return false;
                                            }
                                        }else{

                                            $log .= "False: ID Loueur".
                                                "-------------------------".PHP_EOL;
                                            //Save string to log, use FILE_APPEND to append.
                                            file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                            return false;
                                        }
                                    }else {

                                        $log .= "False: Date Depart".
                                            "-------------------------".PHP_EOL;
                                        //Save string to log, use FILE_APPEND to append.
                                        file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                        return false;
                                    }
                                }else{

                                    $log .= "False: Date Arrivee".
                                        "-------------------------".PHP_EOL;
                                    //Save string to log, use FILE_APPEND to append.
                                    file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                    return false;
                                }
                            }else{

                                $log .= "False: Enfants".
                                    "-------------------------".PHP_EOL;
                                //Save string to log, use FILE_APPEND to append.
                                file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                                return false;
                            }
                        }else{

                            $log .= "False: Adulte".
                                "-------------------------".PHP_EOL;
                            //Save string to log, use FILE_APPEND to append.
                            file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                            return false;
                        }
                    }else{

                        $log .= "False: Email".
                            "-------------------------".PHP_EOL;
                        //Save string to log, use FILE_APPEND to append.
                        file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                        return false;
                    }
                }else{

                    $log .= "False: Prenom".
                        "-------------------------".PHP_EOL;
                    //Save string to log, use FILE_APPEND to append.
                    file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                    return false;
                }
            }else{

                $log .= "False: Nom".
                    "-------------------------".PHP_EOL;
                //Save string to log, use FILE_APPEND to append.
                file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

                return false;
            }
        }

    }

}