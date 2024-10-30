<?php

if ( !class_exists( 'ChaletMontagneAdmin' ) ) {
    class ChaletMontagneAdmin {

        const minPhpVersion = 5.6;

        const NONCE = 'cmci-register-key';

        const pageMenu = 'cmci-page';
        const pageSubMenu = 'cmci-parameter-page';

        const pageListMenu = array('Accueil',
            'Nos Locations',
            /*'Galeries photos',*/
            'Activités',
//            'Blog',
            'Contact',
            'Mentions légales',
            'Politique de confidentialité');

        const cmciMenuName = "Menu Chalet Montagne";

        const contentAccueil = "Bienvenue en [secteur] en [departement] pour vos prochaines vacances à la Montagne.<br />
Découvrez ici notre offre de location saisonnière, de particulier à particulier, pour votre prochain séjour.<br />
Vous pourrez profiter des bienfaits de la montagne et des activités ski et plein air de la station [station].<br />
Nous sommes ravis de vous accueillir dans notre belle région !";

        const fileContentPolitiqueConfidentialite = 'politique-de-confidentialite';
        const fileContentMentionsLegales = 'mentions-legales';
        const fileContentActivites = 'activites';


        private static $notices = array();
        private static $hooksInitiated = false;
        private static $pluginInitiated = false;

        public function __construct(){

            add_action('add_meta_boxes_page', array( 'ChaletMontagneAdmin', 'metaBoxGalerie'));
            add_action( 'save_post', array( 'ChaletMontagneAdmin', 'saveImageMetaBox') );

            add_filter( 'plugin_action_links_chalet-montagne-com-tools/chalet-montagne.php', array('ChaletMontagneAdmin', 'displaySettingMenuLink'), 10, 1 );

            if(!self::$hooksInitiated){
                self::initHooks();
            }

        }

        /**
         * On plugin installation, register CRON, update WP options
         *
         */
        public static function pluginInstall(){


            update_option('default_ping_status', 'closed');
            update_option('default_comment_status', 'closed');
            update_option('blog_public', 0);

            // Check if Old private plugin was installed
            if( get_option('cmp_api_key') == false)
                update_option('cmci_version', CMCI_VERSION);
        }

        /**
         * On plugin desactivation, delete all Chalet Montagne options stored in DB
         */
        public static function pluginDesactivation(){
            self::removeAllData();
        }

        /**
         * On plugin uninstallation, remove CMCI folder and subfolders
         */
        public function pluginUninstall(){

            // Delete JSON & ICS files
            if (is_dir(CMCI_UPLOAD_PATH . '/cmci')) {
                self::rrmdir(CMCI_UPLOAD_PATH . '/cmci');
            }

            $pluginParam = get_option('cmci_pluginParam');

            if(!$pluginParam['keepPages']) {
                $args = array(
                    'post_type' => array('page', 'nav_menu_item'),
                    'numberposts' => -1,
                    'post_status' => null,
                    'post_parent' => null,
                    'meta_key' => 'cmci_content',
                    'meta_value' => 1
                );
                $attachments = get_posts($args);
                if ($attachments) {
                    foreach ($attachments as $post) {
                        setup_postdata($post);
                        wp_delete_post($post->ID);
                    }
                }


                wp_delete_nav_menu(self::cmciMenuName);

            }
            if(!$pluginParam['keepMedias']) {
                $args = array(
                    'post_type' => 'attachment',
                    'numberposts' => -1,
                    'post_status' => null,
                    'post_parent' => null,
                    'meta_key' => 'cmci_content',
                    'meta_value' => 1
                );
                $attachments = get_posts($args);
                if ($attachments) {
                    foreach ($attachments as $post) {
                        setup_postdata($post);
                        wp_delete_attachment($post->ID);
                    }
                }
            }

            self::removeAllData();
            delete_option('cmci_pluginParam');

        }

        /**
         * Init hook (add actions and filters)
         * @return  void
         */
        public static function initHooks()
        {

            add_action('admin_init', array('ChaletMontagneAdmin', 'adminInit'));
            add_action('admin_menu', array('ChaletMontagneAdmin', 'adminMenu'));
            //add_action('plugins_loaded', 'cmci_update_db_check');
            add_action('admin_enqueue_scripts', array('ChaletMontagneAdmin', 'loadAdminRessources'));
            self::$hooksInitiated = true;

        }

        /**
         * Launched by cron task
         */
        public static function actionCheckRentals()
        {
            self::updateLoc();
        }

        /**
         * Active the admin button in the admin inteface
         * @return void
         */
        public static function adminMenu()
        {
            add_menu_page(
                "ChaletMontagne", // le titre de la page
                'ChaletMontagne',	// le nom de la page dans le menu d'admin
                'publish_pages',  // le rôle d'utilisateur requis pour voir cette page
                self::pageMenu, // un identifiant unique de la page
                array('ChaletMontagneAdmin','displayPage'),// le nom d'une fonction qui affichera la page
                'dashicons-admin-tools'
            );

            add_submenu_page( self::pageMenu,
                'Paramètrage',
                'Paramètrage',
                'publish_pages',
                self::pageSubMenu,
                array('ChaletMontagneAdmin', 'displayParamPage'));
        }

        public static function displaySettingMenuLink($links) {
            $links = array_merge( $links, array(
                '<a href="' . esc_url( admin_url( '/admin.php?page=cmci-page' ) ) . '">' . __( 'Location(s)', 'textdomain' ) . '</a>',
                '<a href="' . esc_url( admin_url( '/admin.php?page=cmci-parameter-page' ) ) . '">' . __( 'Paramètres', 'textdomain' ) . '</a>',
            ) );

            return $links;
        }

        /**
         * Load translate text domain
         * @return void
         */
        public static function adminInit()
        {
            load_plugin_textdomain('chalet-montagne-private', false, 'chalet-montagne-full/langs/');
        }


        /**
         * Get a page url
         * @param  string $page the page
         * @return array
         */
        public static function get_page_url($page = 'config', $id = '', $noheader = false, $notice = '')
        {
            $args = array('page' => 'cmci-page');

            if ($page == 'delete_key') {
                $args = array('page' => 'cmci-page',
                    'action' => 'delete-key',
                    '_wpnonce' => wp_create_nonce(self::NONCE));

            }elseif($page == 'resync-api') {
                $args = array('page' => 'cmci-page',
                    'action' => 'resync-api',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }elseif ($page == 'update_loc') {
                if ($id != '' && is_int($id)) {
                    $args = array('page' => 'cmci-page',
                        'action' => 'update-loc',
                        '_wpnonce' => wp_create_nonce(self::NONCE),
                        'loc_id' => $id);
                }
            }elseif ($page == 'set_menu') {
                $args = array('page' => 'cmci-parameter-page',
                    'action' => 'set-menu',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }elseif ($page == 'nextStep'){
                $args = array('page' => 'cmci-page',
                    'step' => $id,
                    'noheader' => $noheader);
                if($notice != ''){
                    $args['notice'] = $notice;
                }
            }elseif($page == 'set_color'){
                $args = array('page' => 'cmci-parameter-page',
                    'action' => 'set-color',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }elseif($page == 'set_pluginParam'){
                $args = array('page' => 'cmci-parameter-page',
                    'action' => 'set-pluginParam',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }elseif($page == 'set_synchro'){
                $args = array('page' => 'cmci-parameter-page',
                    'action' => 'set-synchro',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }elseif($page =='set_arborescence'){
                $args = array('page' => 'cmci-parameter-page',
                    'action' => 'set-arborescence',
                    '_wpnonce' => wp_create_nonce(self::NONCE));
            }

            $url = add_query_arg($args, admin_url('admin.php'));

            return $url;
        }

        /**
         * Load external ressources (css and js)
         * @return void
         */
        public static function loadAdminRessources() {

            global $hook_suffix;

            if (strstr( $hook_suffix, 'cmci-page') || strstr( $hook_suffix, 'cmci-parameter-page') ) {

                // bootstrap
                wp_register_style('bootstrap.min.css', CMCI_DIR . 'assets/css/bootstrap.min.css', array(), CMCI_VERSION);
                wp_enqueue_style('bootstrap.min.css');

                wp_register_script('bootstrap.min.js', CMCI_DIR . 'assets/js/bootstrap.min.js', array('jquery'), CMCI_VERSION, true);
                wp_enqueue_script('bootstrap.min.js');

                wp_register_style('color-picker.css', CMCI_DIR . 'assets/css/bootstrap-colorpicker.css', array(), CMCI_VERSION);
                wp_enqueue_style('color-picker.css');

                wp_register_script('bootstrap-colorpicker.js', CMCI_DIR . 'assets/js/bootstrap-colorpicker.js', array(), CMCI_VERSION, true);
                wp_enqueue_script('bootstrap-colorpicker.js');

                wp_register_script('chalet-montagne-back.js', CMCI_DIR . 'assets/js/chalet-montagne-back.js', array('jquery'), CMCI_VERSION, true);
                wp_enqueue_script('chalet-montagne-back.js');

                // custom style
                self::loadAdminRessourcesCm();
            }
        }

        /**
         * Load chalet montagne ressources (css and js)
         * @return void
         */
        public static function loadAdminRessourcesCm() {
            // custom style
            wp_register_style('chalet-montagne.css', CMCI_DIR . 'assets/css/chalet-montagne.css', array(), CMCI_VERSION);
            wp_enqueue_style('chalet-montagne.css');
        }

        /**
         * Display specific page
         */
        public static function displayPage(){

            //Plugin not configured
            if(get_option('cmci_api_key') == false || get_option('cmci_hash_key') == false || !is_dir(CMCI_UPLOAD_PATH . '/cmci') || (isset($_GET['step']) && ($_GET['step'] == 4 || $_GET['step'] == 3))){
                self::displayConfigurationPage();
            }
            // Ask to disconnect Chalet Montagne account for this website
            elseif(isset($_GET['action']) && $_GET['action'] == 'delete-key' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], self::NONCE)) {

                self::removeAllData();
                self::displayConfigurationPage();

                // Ask to resync data from API Chalet Montagne for this website
            }elseif(isset($_GET['action']) && $_GET['action'] == 'resync-api' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], self::NONCE)){

                $data = self::getData(get_option('cmci_api_key'), get_option('cmci_hash_key'), '', true);
                if (!empty($data->location)) {
                    foreach($data->location as $idLoc => $location) {
                        self::insertAllData(array($idLoc => $location));
                    }
                }

                if(!empty($data->date_fin_api_wpp)) {
                    update_option('cmci_date_fin_api_wpp', $data->date_fin_api_wpp);
                }

                $argsView['resync_api'] = array('cmci_date_fin_api_wpp' => get_option('cmci_date_fin_api_wpp'),
                    'cmci_update_date' => get_option('cmci_update_date'),
                    'cmci_list_rentals' => get_option('cmci_list_rentals'));


                $argsView['user'] = get_option('cmci_user_data');

                // Get all rental informations
                $argsView['locations'] = current(self::getRentalsInformations());

                echo ChaletMontagne::render('header.admin');
                echo ChaletMontagne::render('main.admin', $argsView);

            }else{

                // Update specific rental
                if(isset($_GET['action']) && $_GET['action'] == 'update-loc' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], self::NONCE)){
                    $data = self::getData(get_option('cmci_api_key'), get_option('cmci_hash_key'), '', true);

                    if (!empty($data->location)) {
                        foreach($data->location as $idLoc => $location) {
                            if($location->id == $_GET['loc_id']) {
                                self::insertAllData(array($idLoc => $location));
                            }
                        }
                    }
                }

                $argsView = array();
                $argsView['user'] = get_option('cmci_user_data');

                // Get all rental informations
                $argsView['locations'] = current(self::getRentalsInformations());

                echo ChaletMontagne::render('header.admin');
                echo ChaletMontagne::render('main.admin', $argsView);
            }
        }

        /**
         * Display installation steps
         *
         */
        public static function displayConfigurationPage(){

            $args = array( 'steps' => array('step1' => array('title' => 'Bienvenue',
                'content' => '',
                'status' => 'disabled'),
                'step2' => array('title' => 'Enregistrement de la clé',
                    'content' => 'Enregistrez votre clé pour connecter votre site avec votre compte Chalet Montagne.',
                    'status' => 'disabled'),
                'step3' => array('title' => 'Paramétrage du site',
                    'content' => '',
                    'status' => 'disabled'),
                'step4' => array('title' => 'Terminé',
                    'content' => '',
                    'status' => 'disabled')));

            $argsNotice = array('type' => '');
            $argsView = array();

            //if(!get_option('cmci_key') && !get_option('cmci_key')){
            if(!isset($_GET['step'])){
                $args['steps']['step1']['status'] = 'active';
                if(get_option('cmci_api_key') == false) {
                    $argsView['noHeader'] = false;
                }else{
                    $argsView['noHeader'] = true;
                }

                $step = 'step1';

            }elseif(isset($_GET['step']) && $_GET['step'] == 2){

                $args['steps']['step2']['status'] = 'active';
                $step = 'step2';

                if(isset($_POST['action']) && $_POST['action'] == 'enter-key'){

                    // Redirect to step 2 if hash or key empty or nonce is not valid
                    if(empty($_POST['hash']) || empty($_POST['key']) || !wp_verify_nonce($_POST['_wpnonce'], self::NONCE)){
                        wp_redirect(self::get_page_url('nextStep', 2, false, 'noKeyOrHash'));
                        exit;
                    }

                    // Get key values
                    $new_key = $_POST['key'];
                    // Get hash values
                    $new_hash = $_POST['hash'];
                    // Query Chalet Montagne API with posted values
                    $boolReturnApi = self::saveKeyHash($new_key, $new_hash);

                    if($boolReturnApi === true ){
                        // Check first activation or not by tested presence of CMCI folder
//                        if (is_dir(CMCI_UPLOAD_PATH . '/cmci')) {
                        wp_redirect(self::get_page_url('nextStep', 3));
                        exit;
//                        }else {
//                            wp_redirect(self::get_page_url('nextStep', 3));
//                        }
//                        exit;
                    }else{
                        // Check if bool value. If not, string have been returned.
                        if(!is_bool($boolReturnApi)) {
                            wp_redirect(self::get_page_url('nextStep', 2, false, $boolReturnApi));
                        }else{
                            wp_redirect(self::get_page_url('nextStep', 2, false, 'wrongKeyOrHash'));
                        }
                        exit;
                    }

                }
                // API & Hash key already in database
                elseif(get_option('cmci_hash_key') && get_option('cmci_api_key')){
                    wp_redirect(self::get_page_url('nextStep', 3));
                    exit;
                }else{

                }


            }elseif(isset($_GET['step']) && $_GET['step'] == 3){

                $data = self::getData(get_option('cmci_api_key'), get_option('cmci_hash_key'));

                $argsView = array('locations' => $data->location,
                    'folderExist' => false);


                if(!self::isAbonnementActif()) {
                    $argsView['plugin'] = 'gratuit';
                }else{
                    $argsView['plugin'] = 'payant';
                }

                $checkMenu = self::checkMenu();
                // Check if menu exists
                if($checkMenu){
                    $argsView['createMenu'] = false;
                    $argsNotice['type'] = 'errorWpContent';
                }else{
                    $argsView['createMenu'] = true;
                }

                // Rental folder already created
                if (is_dir(CMCI_UPLOAD_PATH . '/cmci')) {

                    $argsView = array('folderExist' => true);

                }elseif(isset($_POST['action']) && $_POST['action'] == 'import_data'){

                    if (!empty($data->location)) {
                        // Create rentals files
                        if(self::insertAllData($data->location)){
                            if(self::isAbonnementActif()) {

                                /*
                                                                if ( function_exists('wp_next_scheduled') && function_exists('wp_schedule_event') ) {
                                                                    if ( !wp_next_scheduled( 'checkRentals' ) )
                                                                        wp_schedule_event( time(), 'every_five_minutes', 'checkRentals');
                                                                }
                                */
                                if(isset($_POST['creer_arborescence']) && $_POST['creer_arborescence'] == 'true') {
                                    // Creating Wordpress content
                                    if (self::createWpContent($data)) {

                                        update_option('cmci_pageCreated', true);

                                        wp_redirect(self::get_page_url('nextStep', 4));
                                        exit;
                                    } else {
                                        // Error during pages creation
                                        $argsNotice['type'] = 'errorWpContent';
                                    }
                                }else{
                                    // Import location media
                                    foreach($data->location as $location){
                                        if(!empty($location->photos)){
                                            foreach($location->photos as $photo) {
                                                self::importMedia($location, $photo);
                                            }
                                        }
                                    }

                                    update_option('cmci_pageCreated', false);

                                    wp_redirect(self::get_page_url('nextStep', 4));
                                }
                            }else{

                                update_option('cmci_pageCreated', false);

//                                if ( function_exists('wp_next_scheduled') && function_exists('wp_schedule_event') ) {
//                                    if ( !wp_next_scheduled( 'checkRentals' ) )
//                                        wp_schedule_event( time(), 'every_daily_minutes', 'checkRentals');
//                                }

                                wp_redirect(self::get_page_url('nextStep', 4));
                                exit;
                            }
                        }
                    }else{
                        wp_redirect(self::get_page_url('nextStep', 4));
                        exit;
                    }
                }

//                if($data){
//                    $argsView = array('locations' => $data->location);
//                }
                $args['steps']['step3']['status'] = 'active';
                $step = 'step3';

            }elseif(isset($_GET['step']) && $_GET['step'] == 4) {

                $args['steps']['step4']['status'] = 'active';
                $step = 'step4';

            }

            if(isset($_GET['notice']) && !empty($_GET['notice'])){
                $argsNotice['type'] = $_GET['notice'];
            }

            echo ChaletMontagne::render('headerConfig.admin', $args);
            echo ChaletMontagne::render('notice.admin', $argsNotice);
            echo ChaletMontagne::render('config-'.$step.'.admin', $argsView);

        }

        /**
         * Display parameter page
         */
        public static function displayParamPage() {

            //Plugin not configured
            if(get_option('cmci_api_key') == false || get_option('cmci_hash_key') == false || !is_dir(CMCI_UPLOAD_PATH . '/cmci') || (isset($_GET['step']) && ($_GET['step'] == 4 || $_GET['step'] == 3))){
                self::displayConfigurationPage();
            }else {

                $args = array();
                $argsNotice = array('type' => '');

                //Get data from Chalet Montagne
                $data = self::getData(get_option('cmci_api_key'), get_option('cmci_hash_key'));

                $dateFinApiWpp = get_option('cmci_date_fin_api_wpp');
                if($data->date_fin_api_wpp != $dateFinApiWpp && $dateFinApiWpp != false){
                    update_option('cmci_date_fin_api_wpp', $data->date_fin_api_wpp);
                }

                if (isset($_POST['updateColor']) && $_POST['updateColor']) {
                    update_option('cmci_formDateAvailable', $_POST['color-date-available']);
                    update_option('cmci_formDateUnavailable', $_POST['color-date-unavailable']);
                    update_option('cmci_formHeader', $_POST['color-form-header']);
                    update_option('cmci_formCancel', $_POST['color-form-cancel']);

                    $argsNotice['type'] = 'validParamColor';

                }

                if (isset($_GET['action']) && $_GET['action'] == 'set-menu') {
                    self::assignMenu();
                    $argsNotice['type'] = 'validAssignMenu';
                }

                if (isset($_GET['action']) && $_GET['action'] == 'set-arborescence') {
                    self::createWpContent($data);
                }


                if (isset($_POST['updatePluginParam']) && $_POST['updatePluginParam']) {
                    $params = array();
                    $params['keepAccount'] = (isset($_POST['cmci_keepAccount'])) ? $_POST['cmci_keepAccount'] : false;
	                $params['keepPages'] = (isset($_POST['cmci_keepPages'])) ? $_POST['cmci_keepPages'] : false;
	                $params['cacheModalPlanning'] = (isset($_POST['cmci_cacheModalPlanning'])) ? $_POST['cmci_cacheModalPlanning'] : false;
	                $params['cacheModalTarif'] = (isset($_POST['cmci_cacheModalTarif'])) ? $_POST['cmci_cacheModalTarif'] : false;

                    $params['keepMedias'] = (isset($_POST['cmci_keepMedia'])) ? $_POST['cmci_keepMedia'] : false;

                    if (update_option('cmci_pluginParam', $params)) {
                        $argsNotice['type'] = 'validParamAccount';
                    } else {
                        $argsNotice['type'] = 'notValidParamAccount';
                    }
                }

                if (isset($_POST['updatePluginParamAuto']) && $_POST['updatePluginParamAuto']) {
                    $params = array();
                    $params['updatePages'] = (isset($_POST['cmci_auto_update_content'])) ? $_POST['cmci_auto_update_content'] : false;
                    $params['updateMedias'] = (isset($_POST['cmci_auto_update_media'])) ? $_POST['cmci_auto_update_media'] : false;

                    if (update_option('cmci_pluginParamAuto', $params)) {
                        $argsNotice['type'] = 'validParamAuto';
                    } else {
                        $argsNotice['type'] = 'notValidParamAuto';
                    }
                }

                $locationsFromApi = $data->location;
                $locationsList = array();

                // Get rental already sotred
                $locationsStored = self::getRentalsInformations();
                if(ChaletMontagneAdmin::isAbonnementActif()) {
                    foreach ($locationsFromApi as $location) {
                        if (!array_key_exists($location->id, $locationsStored['locations'])) {
                            $locationsList[] = $location;
                        }
                    }
                }

                if (isset($_POST['validReSyncLocations']) && $_POST['validReSyncLocations']) {
                    if(isset($_POST['syncData']) && !empty($_POST['syncData'])){
                        foreach($_POST['syncData'] as $value){
                            $tabValue = explode('-', $value);

                            //Get post associate to location ID
                            global $post;
                            $args = array(
                                'post_type' => 'page',
                                'meta_query' => array(
                                    array(
                                        'key' => 'cmci_location_id',
                                        'value' => $tabValue[0],
                                        'compare' => '=',
                                    )
                                )
                            );
                            $query = new WP_Query($args);
                            if($query->have_posts()){
                                while($query->have_posts()){
                                    $query->the_post();
                                    $id = get_the_ID();
                                }
                            }

                            if($tabValue[1] != 'images') {
                                $locTemp = null;
                                foreach($locationsFromApi as $locFromApi){
                                    if($locFromApi->id == $tabValue[0]){
                                        $locTemp = $locFromApi;
                                    }
                                }
                                $argValue = '';
                                if($tabValue[1] == 'title'){
                                    $argValue = $locTemp->nom;
                                }elseif($tabValue[1] == 'content'){
                                    $argValue = self::createContent($locTemp);
                                }

                                $postArgs = array('ID' => $id,
                                    'post_'.$tabValue[1] => $argValue);

                                if(wp_update_post($postArgs) != 0){
                                    $argsNotice['type'] = 'validReSync';
                                }

                            }else{
                                $args = array('post_type'=>'attachment',
                                    'numberposts'=>-1,
                                    'post_status'=>'any',
                                    'meta_key' => 'cmci_location_id',
                                    'meta_value' => $tabValue[0],
                                    'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png',);
                                $attachments = get_posts($args);
                                if($attachments){
                                    foreach($attachments as $attachment){
                                        // here your code
                                        wp_delete_attachment($attachment->ID);
                                    }
                                }

                                delete_post_meta($id, 'meta-image');

                                foreach($locationsFromApi as $locFromApi){
                                    if($locFromApi->id == $tabValue[0]){
                                        self::createGallery($id, $locFromApi);
                                    }
                                }
                                $argsNotice['type'] = 'validReSync';
                            }
                        }
                    }

                    if(isset($_POST['getData']) && !empty($_POST['getData'])){

                        foreach($locationsFromApi as $location){
                            if(in_array($location->id, $_POST['getData'])){

                                self::createLoc($location);

                                $content = self::createContent($location);

                                $args = array('post_author' => get_current_user_id(),
                                    'post_date' => date('Y-m-d H:i:s'),
                                    'post_title' => $location->nom,
                                    'post_content' => $content,
                                    'post_status' => 'publish',
                                    'comment_status' => 'closed',
                                    'ping_status' => 'closed',
                                    'post_type' => 'page'
                                );
                                $pageLocationId = self::addWpContent($args, 'posts');

                                self::addMetaValue($pageLocationId);
                                self::addMetaValue($pageLocationId, 'cmci_location_id', $location->id);
                                self::createGallery($pageLocationId, $location);


                                $args = array('post_author' => get_current_user_id(),
                                    'post_date' => date('Y-m-d H:i:s'),
                                    'post_title' => 'Tarifs / Planning',
                                    'post_status' => 'publish',
                                    'post_content' => '[cmci_planning id="'.$location->id.'"][cmci_tarifs id="'.$location->id.'"]',
                                    'comment_status' => 'closed',
                                    'ping_status' => 'closed',
                                    'post_type' => 'page',
                                    'post_parent' => $pageLocationId
                                );
                                $pageID = self::addWpContent($args, 'posts');

                                // Get last menu position and menu id
                                $tabMenu = wp_get_nav_menu_items(self::cmciMenuName);
                                $lastItemMenu = end($tabMenu);
                                $lastPosition = $lastItemMenu->menu_order;
                                $lastPosition++;
                                $term = get_term_by('name', self::cmciMenuName, 'nav_menu');
                                $menu_id = $term->term_id;

                                // Add new rental at the end of menu
                                $newItemMenu = self::createMenuItem($pageLocationId, $menu_id, $lastPosition );
                                self::createMenuItem($pageID, $menu_id, $newItemMenu[0], $newItemMenu[1] );

                                $argsNotice['type'] = 'validReSync';

                            }
                        }
                    }

                }

                $args['pluginParam'] = get_option('cmci_pluginParam');
                $args['pluginParamAuto'] = get_option('cmci_pluginParamAuto');
                $args['locationsStored'] = $locationsStored;
                $args['locationsList'] = $locationsList;

                echo ChaletMontagne::render('header.admin');
                echo ChaletMontagne::render('notice.admin', $argsNotice);
                echo ChaletMontagne::render('parameter.admin', $args);
            }
        }

        /**
         * NOT USED ACTUALLY
         * Get the user api key
         * @return string the api key
         */
        private static function get_api_key() {
            return apply_filters( 'cmci_get_api_key', defined('cmci_API_KEY') ? constant('cmci_API_KEY') : get_option('cmci_api_key') );
        }

        /**
         * NOT USED ACTUALLY
         * Get the user hash key
         * @return string the hash key
         */
        private static function get_hash_key() {
            return apply_filters( 'cmci_get_hash_key', defined('cmci_HASH_KEY') ? constant('cmci_HASH_KEY') : get_option('cmci_hash_key') );
        }

        /**
         * Save the new api key in a wordpress option
         * @param  string $api_key The new api key to save
         * @param  string $hash_key The new hash key to save
         * @return bool
         */
        protected static function saveKeyHash($api_key, $hash_key)
        {
            $data =  (array) self::getData($api_key, $hash_key);
            if ($data && !array_key_exists('error', $data)) {

                if(update_option('cmci_api_key', $api_key) && update_option('cmci_hash_key', $hash_key)){
                    if(self::insertuserData($data)) {
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            } else {
                return $data->error_message;
            }

        }

        /**
         * Get data associated to a user
         * @return object The data
         */
        public static function getData($api_key = false, $hash_key = false, $version = CMCI_VERSION, $forceManual = false)
        {

	        $version = CMCI_VERSION;

            if ($api_key === false) {
                $api_key = get_option('cmci_api_key');
            }

            if ($hash_key === false) {
                $hash_key = get_option('cmci_hash_key');
            }

            $params = array('idLoueur' => $api_key, 'h' => $hash_key, 'ver' => $version);

            if($forceManual == true){
                $params['force'] = 1;
            }

            $json = ChaletMontagne::http_get(ChaletMontagne::API_METHOD_GET_ID,$params, );

            // convert the json file into an object
            $obj = json_decode($json);
            return $obj;
        }

        /**
         * Insert user data in the DB
         * @return void
         */
        protected static function insertUserData($data)
        {

            $userData = array();

            $userData['nom'] = $data->nom;
            $userData['prenom'] = $data->prenom;
            if ($data->societe != '') {
                $userData['societe'] = $data->nom;
            }
            $userData['adresse'] = $data->adresse;
            $userData['zip'] = $data->zip;
            $userData['ville'] = $data->ville;
            $userData['tel'] = $data->tel;
            $userData['telpor'] = $data->telpor;
            $userData['email'] = $data->email;

            if($data->date_fin_api_wpp == null || $data->date_fin_api_wpp == 'null'){
                $finAPI = false;
            }else{
                $finAPI = $data->date_fin_api_wpp;
            }

            if (add_option('cmci_user_data', serialize($userData)) && add_option('cmci_url_contact', $data->formpost) && add_option('cmci_update_time', $data->updatetime) && add_option('cmci_date_fin_api_wpp', $finAPI)) {
                return true;
            } else {
                return false;
            }
        }


        /**
         * Insert data in database
         * @return void
         */
        public static function insertAllData($locations, $createFolder = true)
        {

            update_option('cmci_update_date', date('d-m-Y'));

            $arrayUpdateLocs = array();

            $cmciListRentals = get_option('cmci_list_rentals');
            if($cmciListRentals != false) {
                $arrayUpdateLocs = $cmciListRentals;
            }

            if($createFolder) {
                if (!is_dir(CMCI_UPLOAD_PATH . '/cmci')) {
                    if (!mkdir(CMCI_UPLOAD_PATH . '/cmci')) {
                        return false;
                    }
                }
            }

            foreach ($locations as $key => $location) {
                $arrayUpdateLocs[$location->id] = time();

                if(!self::createLoc($location)){
                    return false;
                }
            }

            update_option('cmci_list_rentals', $arrayUpdateLocs);

            return true;

        }

        /**
         * Create rental folder and get schedule and prices files
         * @param  array $location Location data
         * @return void
         */
        protected static function createLoc($location)
        {

            if (!file_exists(CMCI_UPLOAD_PATH . '/cmci/' . $location->id)) {
                if(!is_dir(CMCI_UPLOAD_PATH . '/cmci')) {
                    if (!mkdir(CMCI_UPLOAD_PATH . '/cmci', 0755)) {
                        die("Can't create cmci folder. Contact Chalet Montagne");
                    }
                }
                if(!is_dir(CMCI_UPLOAD_PATH . '/cmci/' . $location->id)){
                    if(!mkdir(CMCI_UPLOAD_PATH . '/cmci/' . $location->id, 0755)){
                        die("Can't create folder location folder. Contact Chalet Montagne");
                    }
                }
            }

            $boolIcal = false;

            // create ics file
            $icalFile = fopen(CMCI_UPLOAD_PATH . '/cmci/' . $location->id . "/planning.ics", "w+") or die("Unable to open file!");
            if ($icalFile != false) {
                $ical = file_get_contents($location->ical);
                fwrite($icalFile, $ical);
                if (fclose($icalFile)) {
                    $boolIcal = true;
                }
            }

            $boolFile = false;
            // create json file
            $tarifFile = fopen(CMCI_UPLOAD_PATH . '/cmci/' . $location->id . "/tarif.json", "w+") or die("Unable to open file!");
            if ($tarifFile != false) {
                $tarifs = file_get_contents($location->tarifs);
                fwrite($tarifFile, $tarifs);
                if (fclose($tarifFile)) {
                    $boolFile = true;
                }
            }

            if ($boolFile && $boolIcal) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Update a specific rental with his $id
         * @param  int $id The id of the rental
         * @param  array $data New data
         * @return void
         */
        protected static function updateLoc($id = '')
        {

            $update = true;
            // If $id is not defined it mean this function is launched by wordpress CRON
            if ($id == '') {
                $arrayRentals = get_option('cmci_list_rentals');
                $update_time = get_option('cmci_update_time');
                if (!empty($arrayRentals)) {

                    asort($arrayRentals);
                    $id = key($arrayRentals);
                    $rentalUpdateTime = current($arrayRentals);
                    $diffTime = time() - $rentalUpdateTime;
                    // If rental have been updated recently
                    if ($diffTime < $update_time && $update_time != '') {
                        $update = false;
                    }
                } else {
                    $update = false;
                }
            }

            if ($update) {
                $data = self::getData();

                $urlFormContact = get_option('cmci_url_contact');

                if ($data->formpost != $urlFormContact || $urlFormContact == '') {
                    update_option('cmci_url_contact', $data->formpost);
                }

                $updateTime = get_option('cmci_update_time');
                if ($data->updatetime != $updateTime || $updateTime == '') {
                    update_option('cmci_update_time', $data->updatetime);
                }

                $dateFinApiWpp = get_option('cmci_date_fin_api_wpp');
                if($data->date_fin_api_wpp != $dateFinApiWpp){
                    update_option('cmci_date_fin_api_wpp', $data->date_fin_api_wpp);
                }

                $userDatas = get_option('cmci_user_data');
                if (is_string($userDatas)) {
                    $userDbData = unserialize($userDatas);
                }
                else {
                    $userDbData = $userDatas;
                }


                if($userDbData['email'] != $data->email ||
                    $userDbData['tel'] != $data->tel ||
                    $userDbData['telpor'] != $data->telpor) {

                    $userData = array();
                    $userData['nom'] = $data->nom;
                    $userData['prenom'] = $data->prenom;
                    if ($data->societe != '') {
                        $userData['societe'] = $data->nom;
                    }
                    $userData['adresse'] = $data->adresse;
                    $userData['zip'] = $data->zip;
                    $userData['ville'] = $data->ville;
                    $userData['tel'] = $data->tel;
                    $userData['telpor'] = $data->telpor;
                    $userData['email'] = $data->email;

                    update_option('cmci_user_data', $userData);

                }

                foreach ($data->location as $objLocation) {
                    if ($objLocation->id == $id) {
                        $location = $objLocation;
                        break;
                    }
                }

                $uptadeAuto = get_option('cmci_pluginParamAuto');

                if($uptadeAuto['updatePages'] == 'true' && self::isAbonnementActif()){
                    $args = array(
                        'post_type'      => array('page'),
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key' => 'cmci_page_type',
                                'value' => 'galerie',
                                'compare' => 'NOT EXISTS'
                            ),
                            array(
                                'key' => 'cmci_page_type',
                                'value' => 'tarif',
                                'compare' => 'NOT EXISTS'
                            )
                        ),
                        'meta_key' => 'cmci_location_id',
                        'meta_value' => $location->id

                    );
                    $wp_query = new WP_Query( $args );
                    if ( $wp_query->have_posts() ) {
                        while ($wp_query->have_posts()) {
                            $wp_query->the_post();
                            $content = self::createContent($location);
                            wp_update_post(array('ID' => get_the_ID(), 'post_content' => $content));
                        }
                    }
                }

                if($uptadeAuto['updateMedias'] == 'true' && self::isAbonnementActif()){

                    $listPhotosDb = array();
                    $lisPhotoApi = array();

                    $args = array(
                        'post_type'      => array('attachment'),
                        'posts_per_page' => -1,
                        'post_status'    => 'inherit',
                        'meta_key' => 'cmci_location_id',
                        'meta_value' => $location->id

                    );
                    $wp_query = new WP_Query( $args );
                    if ( $wp_query->have_posts() ) {
                        while ($wp_query->have_posts()) {
                            $wp_query->the_post();
                            $listPhotosDb[get_the_ID()] = intval(get_the_title());
                        }
                    }

                    foreach($location->photos as $photo){
                        $lisPhotoApi[] = $photo->id;
                    }

                    if(count(array_diff($listPhotosDb, $lisPhotoApi)) > 0 || count(array_diff($lisPhotoApi, $listPhotosDb)) > 0){
                        foreach($listPhotosDb as $idPhoto => $photoID)
                        {
                            wp_delete_attachment($idPhoto);
                        }

                        foreach($location->photos as $photo){
                            self::importMedia($location, $photo);
                        }

                    }

                }

                $return = self::createLoc($location);

                if ($return) {

                    $arrayRentals[$id] = time();

                    update_option('cmci_list_rentals', $arrayRentals);

                    ChaletMontagne::render('notice.admin', array('type' => 'maj'));
                }
            } else {
                return;
            }
        }

        /**
         * Check if CMCI menu already exists
         * @return bool | int
         */
        public static function checkMenu() {
            $menu_name = self::cmciMenuName;
            $menu_exists = wp_get_nav_menu_object( $menu_name );

            if( $menu_exists) {
                return  true;
            }else{
                // Suppose that menu already exist so content already exist too
                return false;
            }
        }

        /**
         * Prepare arguments for page creation, create main menu
         * @param $data data returned by CM API
         */
        protected static function createWpContent($data){

            $menu_name = self::cmciMenuName;
            $menu_exists = wp_get_nav_menu_object( $menu_name );
            $menu_id = wp_create_nav_menu($menu_name);

            update_option('show_on_front', 'page');

            if($menu_id > 0) {
                $position = 1;
                $menu_order = 1;
                foreach (self::pageListMenu as $pageMenu) {

                    $args = array('post_author' => get_current_user_id(),
                        'post_date' => date('Y-m-d H:i:s'),
                        'post_title' => $pageMenu,
                        'post_status' => 'publish',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_type' => 'page',
                        'menu_order' => $menu_order
                    );

                    if($pageMenu == 'Accueil'){

                        $content = str_replace('[station]', $data->location[0]->station_nom, self::contentAccueil);
                        $content = str_replace('[secteur]', $data->location[0]->station_massif, $content);
                        $content = str_replace('[departement]', $data->location[0]->station_departement, $content);

                        $args['post_content'] = $content;
                    }elseif($pageMenu == 'Contact'){
                        $strInfo = "<h2><u><strong>Informations loueur: </strong></u></h2><br />";
                        if($data->societe != ''){
                            $strInfo .= '<p><strong>Société:</strong> '.$data->societe."</p><br />";
                        }

                        $strInfo .= $data->prenom.' '.$data->nom."</h2></:h2><br />";

                        if($data->ville != ''){

                            $strInfo .= '<p><strong>Adresse:</strong> '.$data->adresse.' '.$data->zip.' '.$data->ville."</p><br />";
                        }
                        if($data->tel != ''){
                            $strInfo .= '<p><strong>Téléphone:</strong> '.$data->tel."</p><br />";
                        }
                        if($data->tel != ''){
                            $strInfo .= '<p><strong>Téléphone portable:</strong> '.$data->telpor."</p><br />";
                        }
                        $args['post_content'] = $strInfo."[cmci_contact]";
                    }elseif($pageMenu == 'Mentions légales'){

                        if($data->societe != '') {
                            $content = file_get_contents(CMCI_PATH . '/assets/texte/' . self::fileContentMentionsLegales . '-societe.txt');
                            $content = utf8_encode($content);
                            $content = str_replace('[societe]', $data->societe, $content);
                        }else{
                            $content = file_get_contents(CMCI_PATH . '/assets/texte/' . self::fileContentMentionsLegales . '.txt');
                            $content = utf8_encode($content);
                        }

                        $content = utf8_encode($content);
                        $content = str_replace('[prenom]', $data->prenom, $content);
                        $content = str_replace('[nom]', strtoupper($data->nom), $content);
                        $content = str_replace('[adresse]', $data->adresse, $content);
                        $content = str_replace('[zip]', $data->zip, $content);
                        $content = str_replace('[ville]', $data->ville, $content);
                        $content = str_replace('[tel]', 'Telephone: '.$data->tel, $content);
                        $content = str_replace('[telpor]', 'Telephone portable: '.$data->telpor, $content);
                        $content = str_replace('[email]', 'Mail: '.$data->email, $content);
                        $args['post_content'] = $content;

                    }elseif($pageMenu == 'Politique de confidentialité'){
                        $content = utf8_encode(file_get_contents(CMCI_PATH.'/assets/texte/'.self::fileContentPolitiqueConfidentialite.'.txt'));
                        $args['post_content'] = $content;
                        $storePrivacyPolicy = true;
                    }elseif($pageMenu == 'Activités'){
                        $content = utf8_encode(file_get_contents(CMCI_PATH.'/assets/texte/'.self::fileContentActivites.'.txt'));
                        $locations = $data->location;
                        $location = $locations[0];

                        if($location->urlStation != '') {
                            $content = str_replace('[nom_station]', '<a href="' . $location->urlStation . '">' . $location->station_nom . '</a>', $content);
                        }elseif($location->station_nom != ''){
                            $content = str_replace('[nom_station]', $location->station_nom, $content);
                        }
                        $args['post_content'] = $content;
                    }

                    $ID = self::addWpContent($args, 'posts');
                    self::addMetaValue($ID);

                    if(isset($storePrivacyPolicy) && $storePrivacyPolicy){
                        update_option('cmci_privacy_policy', $ID);
                        $storePrivacyPolicy = false;
                    }

                    // Don't add Legals page to menu
                    // 2.4.6 Add Legals page to menu
//                    if($pageMenu != 'Mentions légales') {
                    $return = self::createMenuItem($ID, $menu_id, $position, 0);
                    $position = $return[0];
//                    }
                    if ($ID != false) {
                        if ($pageMenu == 'Nos Locations') {

                            $position = self::createWpSubContent($menu_id, $position, $data->location, $ID, false, $return[1]);

                            global $wpdb;

                            $locationContent = '<p>Voici notre offre de logement(s) meublé(s) disponible(s) en location saisonnière :</p>';

                            // Get children page
                            $args = array('post_type' => 'page',
                                'post_parent' => $ID,
                                'post_status' => 'publish',
                                'order' => 'asc'
                            );
                            $i = 0;
                            $the_query = new WP_Query($args);
                            if ($the_query->have_posts()) {

                                //$locationContent .= '<ul>';

                                while ($the_query->have_posts()) {
                                    $the_query->the_post();
                                    $locationContent .= '<p class="title-liste-locations"><a href="'.get_permalink().'"><img src="'.get_the_post_thumbnail_url('', 'thumbnail').'" /></a></p><p><a href="'.get_permalink().'">'.get_the_title();
                                    $idLoc = get_post_meta(get_the_ID(), 'cmci_location_id', true);

                                    foreach($data->location as $location){

                                        if($location->id == $idLoc){
                                            $locationContent .= ' &agrave; '.$location->ville.' ('.$location->place.' personnes) ';
                                        }
                                    }

                                    $locationContent .= '</p></a>';

                                }
                                //$locationContent .= '</ul>';
                                $args = array('ID' => $ID,
                                    'post_content' => $locationContent);

                                wp_update_post($args);
                            }

                        }elseif($pageMenu == "Blog"){
                            update_option('page_for_posts', $ID);
                        }elseif($pageMenu == "Accueil"){
                            update_option('page_on_front', $ID);
                        }
                    } else {
                        //return false;
                    }
                    $menu_order++;
                }

                // Modify content page Policy
                /* $the_slug = self::fileContentPolitiqueConfidentialite;
                 $args = array(
                     'name'        => $the_slug,
                     'post_type'   => 'page',
                     'post_status' => array('publish', 'draft'),
                     'numberposts' => 1
                 );*/
                /*$my_posts = get_posts($args);
                if( $my_posts ){
                    $content = file_get_contents(cmci_PATH.'/assets/texte/'.self::fileContentPolitiqueConfidentialite.'.txt');
                    if($content != false){
                        $args = array('ID' => $my_posts->ID,
                            'post_content' => utf8_encode($content));
                        wp_update_post($args);
                    }
                }*/

                // Assign Chalet Montagne menu to the first menu location of current theme
                self::assignMenu($menu_id);
                return true;

            }else{
                return false;
            }
        }

        /**
         * @param array $locations array Rentals List
         * @param int $parentID int Parent page
         * @param bool $pageTarif Conditional, create args rental for page or price page
         * @param int $menu_id ID menu to add item
         * @param int position item position in the menu
         * @param int parent_id of the menu
         * @return
         */
        protected static function createWpSubContent($menu_id, $position,$locations = array(), $parentID = 0, $pageTarif = false,  $parent_id = 0)
        {
            // Rental page
            if (!$pageTarif) {
                foreach ($locations as $location) {

                    $content = self::createContent($location);

                    $args = array('post_author' => get_current_user_id(),
                        'post_date' => date('Y-m-d H:i:s'),
                        'post_title' => $location->nom,
                        'post_content' => $content,
                        'post_status' => 'publish',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_type' => 'page',
                        'post_parent' => $parentID,
                        'menu_order' => $position
                    );
                    $pageLocationId = self::addWpContent($args, 'posts');

                    self::addMetaValue($pageLocationId);
                    self::addMetaValue($pageLocationId, 'cmci_location_id', $location->id);
                    self::createGallery($pageLocationId, $location);

                    if($pageLocationId != false) {

                        $return = self::createMenuItem ($pageLocationId, $menu_id, $position, $parent_id);
                        $position = self::createWpSubContent($menu_id, $return[0],$location->id, $pageLocationId, true,  $return[1]);

                    }else{
                        return false;
                    }
                }

                return $position;

            }
            // Price page
            elseif($pageTarif){

                // Update 4.2.6 - Create Galery Page first

                $args = array('post_author' => get_current_user_id(),
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_title' => 'Galerie',
                    'post_status' => 'publish',
                    'post_content' => '<h2>'.get_the_title($parentID).'</h2><p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a></p>[cmci_galerie id="'.$locations.'"]<p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a></p>',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_type' => 'page',
                    'post_parent' => $parentID,
                    'menu_order' => 1
                );
                $pageGalerieId = self::addWpContent($args, 'posts');
                if($pageGalerieId != false){
                    self::addMetaValue($pageGalerieId);
                    // Mark this page as a galery page
                    self::addMetaValue($pageGalerieId, 'cmci_page_type', 'galerie');

                    $return = self::createMenuItem ($pageGalerieId, $menu_id, $position, $parent_id);

                    $args = array('post_author' => get_current_user_id(),
                        'post_date' => date('Y-m-d H:i:s'),
                        'post_title' => 'Tarifs / Planning',
                        'post_status' => 'publish',
                        'post_content' => '<h2>'.get_the_title($parentID).'</h2><p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageGalerieId).'">Voir les photos</a></p>[cmci_planning id="'.$locations.'"][cmci_tarifs id="'.$locations.'"]<p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageGalerieId).'">Voir les photos</a></p>',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_type' => 'page',
                        'post_parent' => $parentID,
                        'menu_order' => 2
                    );
                    $pageTarifId = self::addWpContent($args, 'posts');
                    if($pageTarifId != false){
                        self::addMetaValue($pageTarifId);

                        // Mark this page as a price page
                        self::addMetaValue($pageTarifId, 'cmci_page_type', 'tarif');

                        $return = self::createMenuItem ($pageTarifId, $menu_id, $position, $parent_id);

                        // Update Galerie page content
                        $args = array('ID' => $pageGalerieId,
                            'post_content' => '<h2>'.get_the_title($parentID).'</h2><p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a></p>[cmci_galerie id="'.$locations.'"]<p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a></p>',
                        );
                        wp_update_post($args);

                        return $return[0];
                    }else{
                        //return false;
                    }
                }else{
                    //return false;
                }
            }
        }

        /**
         * Insert data in database
         * @param $args Arguments to insert
         * @param $table Table used to insert args
         * @return int ID line inserted
         */
        protected static function addWpContent($args, $table){
            return wp_insert_post($args);
        }

        /**
         * Insert meta data in database
         * @param int $id post ID
         * @return int ID line inserted
         */
        protected static function addMetaValue($id = 0, $key = 'cmci_content', $value = true){
            if($id != 0) {
                return add_post_meta($id, $key, $value);
            }else{
                return;
            }
        }

        /**
         * Create menu item
         * @param $pageId Page ID
         * @param $menu_id ID of the menu
         * @param $position Item position
         * @param int $parent_id Parent item
         * @return array position given in parameter +1, item ID after creation
         */
        protected static function createMenuItem ($pageId, $menu_id, $position, $parent_id = 0){
            $itemData =  array(
                'menu-item-object-id' => $pageId,
                'menu-item-parent-id' => $parent_id,
                'menu-item-position'  => $position,
                'menu-item-object' => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish'
            );

            $itemID = wp_update_nav_menu_item($menu_id, 0, $itemData);

            self::addMetaValue($itemID);

            $position++;
            return array($position, $itemID);
        }

        /**
         * The shortcode text with the option
         * @param  string $key The rental id
         * @return string
         */
        public static function shortcode_text($mode, $key)
        {
            return '[cmci_' . $mode . ' id="' . $key . '"]';
        }

        /**
         * Remove data from database
         */
        private static function removeAllData(){

            $pluginParam = get_option('cmci_pluginParam');

            if($pluginParam['keepAccount'] == false) {
                // Delete all data
                delete_option('cmci_api_key');
                delete_option('cmci_hash_key');
                delete_option('cmci_user_data');
                delete_option('cmci_url_contact');
                delete_option('cmci_update_time');
                delete_option('cmci_update_date');
                delete_option('cmci_db_version');
                delete_option('cmci_list_rentals');
                delete_option('cmci_formDateAvailable');
                delete_option('cmci_formDateUnavailable');
                delete_option('cmci_formHeader');
                delete_option('cmci_formCancel');
                delete_option('cmci_privacy_policy');
                delete_option('cmci_update_16');
                delete_option('cmci_version');
                delete_option('cmci_pageCreated');
                delete_option('cmci_pluginParamAuto');
                delete_option('cmci_date_fin_api_wpp');
                wp_clear_scheduled_hook('checkRentals');
                wp_clear_scheduled_hook('dailyCheckRentals');
                wp_clear_scheduled_hook('fiveMinutesCheckRentals');
            }

        }

        /**
         * Remove directory recursively
         * @param $dir Directory to remove
         */
        protected static function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir."/".$object))
                            self::rrmdir($dir."/".$object);
                        else
                            unlink($dir."/".$object);
                    }
                }
                rmdir($dir);
            }
        }

        /**
         * Assign Chalet Montagne menu to the first menu location of current theme
         * @param $menuId Menu ID
         */
        protected static function assignMenu($menuId = 0){


            if($menuId == 0){
                $menu_name = self::cmciMenuName;
                $menu_exists = wp_get_nav_menu_object( $menu_name );
                $menuId = $menu_exists->term_id;
            }

            $allMenus = get_registered_nav_menus();

            $key  = key($allMenus);
            $allMenus[$key] = $menuId;

            set_theme_mod ( 'nav_menu_locations', $allMenus );
        }

        /**
         * Add metabox
         */
        public static function metaBoxGalerie() {
//            add_meta_box('cm-meta-box', 'Galerie', array('ChaletMontagneAdmin', 'cmCreateMetaBox'), 'page', 'normal');
        }

        /**
         * Create metabox on page
         */
        public static function cmCreateMetaBox () {

            self::loadAdminRessourcesCm();

            global $post;

            $listImages  = unserialize(current(get_post_meta($post->ID, 'meta-image')));
            ?>
            <p>Pour afficher la galerie où vous voulez dans le site, vous pouvez utiliser le code suivant: [cmci_galerie id_page="<?php echo $post->ID; ?>"]</p>
            <label for="case-study-bg" class="lacuna2-row-title">Images présentes dans la galerie</label>
            <br />
            <input type="button" id="meta-image-button" class="button button-secondary" value="Ajouter une image" />
            <div id="metaBoxImagesList">
                <ul>
                    <?php if(!empty($listImages)){
                        $i = 1;
                        foreach($listImages as $galImage){

                            $src = wp_get_attachment_url($galImage);
                            echo '<li><img style="max-width:200px;height:auto;" src="'.$src.'" /><input type="hidden" name="meta-image[]"  value="'.$galImage.'" /><span class="removeImageGallery">Supprimer cette image</span></li>';

                            if($i % 5 == 0){
                                echo '<li class="clear"></li>';
                                $i = 1;
                            }else {
                                $i++;
                            }
                        }
                    }
                    ?>
                </ul>
                <div class="clear"></div>
            </div>
            <script>
                jQuery('#meta-image-button').click(function() {

                    var send_attachment_bkp = wp.media.editor.send.attachment;

                    wp.media.editor.send.attachment = function(props, attachment) {
                        jQuery('#meta-image').val(attachment.url);
                        jQuery('#metaBoxImagesList ul').append('<li><img style="max-width:200px;height:auto;" src="'+attachment.url+'" /><input type="hidden" name="meta-image[]"  value="'+attachment.id+'" /><span class="removeImageGallery">Supprimer cette image</span></li>');
                        wp.media.editor.send.attachment = send_attachment_bkp;
                    }

                    wp.media.editor.open();

                    return false;
                });

                jQuery('#metaBoxImagesList').on('click', 'li', function(){
                    console.log(this);
                    jQuery(this).remove();
                });
            </script>
            <?php
        }

        /**
         * Save image posted in metabox on a page
         * @param $post_id ID post
         */
        public static function saveImageMetaBox ($post_id) {
            $is_autosave = wp_is_post_autosave( $post_id );
            $is_revision = wp_is_post_revision( $post_id );
            $is_valid_nonce = ( isset( $_POST[ 'case_study_bg_nonce' ] ) && wp_verify_nonce( $_POST[ 'case_study_bg_nonce' ], 'case_study_bg_submit' ) ) ? 'true' : 'false';

            // Exits script depending on save status
            if ( $is_autosave || $is_revision || !$is_valid_nonce  ) {
                return;
            }


            // Checks for input and sanitizes/saves if needed
            if( isset( $_POST[ 'meta-image' ] ) ) {
                $images = array();
                foreach($_POST['meta-image'] as $image){
                    $images[] = sanitize_text_field($image);
                }
                $serImages = serialize($images);
                // $serImages = $images;
                update_post_meta( $post_id, 'meta-image', $serImages );
            }
        }

        /**
         * Get an image from an url
         * @param $url string Url of a image to save
         * @param $path string Path to save iamge
         */
        protected static function downloadImageFromUrl($url, $path){
            $channel = curl_init();

            curl_setopt($channel, CURLOPT_URL, $url);
            curl_setopt($channel, CURLOPT_POST, 0);
            curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
            $fileBytes = curl_exec($channel);
            curl_close($channel);

            $fileWritter = fopen($path, 'w');
            fwrite($fileWritter, $fileBytes);
            fclose($fileWritter);
        }

        /**
         * @param $hex Hexadecimal code
         * @return string RGB values
         */
        public static function hex2rgb($hex) {
            $hex = str_replace("#", "", $hex);

            if(strlen($hex) == 3) {
                $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                $b = hexdec(substr($hex,2,1).substr($hex,2,1));
            } else {
                $r = hexdec(substr($hex,0,2));
                $g = hexdec(substr($hex,2,2));
                $b = hexdec(substr($hex,4,2));
            }
            $rgb = array($r, $g, $b);
            return implode(",", $rgb); // returns the rgb values separated by commas
            //return $rgb; // returns an array with the rgb values
        }

        /**
         * @return array list rentals stored
         */
        public static function getRentalsInformations() {

            $argsView = array();
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
                        $locDesc = ChaletMontagne::sanitize_ics_string('X-WR-CALDESC', $loc['locDesc']);
                        $argsView['locations'][$idLoc]['name'] = $locName;
                        $argsView['locations'][$idLoc]['descFr'] = $locDesc;
                    }


                }
            }

            return $argsView;
        }

        /**
         * @param object $location
         * @return string Formatted content
         */
        public static function createContent($location) {
            $infos = '';
            // Create benefits list
            $content = '[cmci_voir_page_tarifs] [cmci_voir_galerie]';
            $content .= '<p>'.nl2br($location->remarques).'</p>';
            //$infos .= '[cmci_voir_page_tarifs]';
            $infos .= '<h2>Informations:</h2>
                                <ul>
                                <li>Type: '.$location->type_nom_fr.'</li>
                                <li>Surface: '.$location->surface.'m²</li>';
            if($location->nb_pieces > 0){
                $infos .= '<li>Nombre de pièce(s): '.$location->nb_pieces.'</li>';
            }
            $infos .= '<li>Places: '.$location->place.'</li>';
            if($location->lit2place > 0){
                $infos .= '<li>Lit(s) 2 places: '.$location->lit2place.'</li>';
            }
            if($location->lit1place > 0){
                $infos .= '<li>Lit(s) 1 place: '.$location->lit1place.'</li>';
            }
            if($location->canapelit > 0){
                $infos .= '<li>Canap&eacute; lit: '.$location->canapelit.'</li>';
            }
            if($location->chambre > 0){
                $infos .= '<li>Chambre(s): '.$location->chambre.'</li>';
            }
            $infos .= '<li>Etage: '.$location->etage_lnom_fr.'</li>';
            $infos .= '<li>Salle(s) de bain: '.$location->salledebain.'</li>';
            $infos .= '<li>Salle(s) d\'eau: '.$location->salleeau.'</li>';
            $infos .= '<li>Sanitaire(s): '.$location->sanitaires.'</li>';
            $infos .= '<li>Sanitaire(s) indépendant(s): '.$location->sanitairesindependant.'</li>';
            $infos .= '</ul>';

            if($location->classement_nom_fr != '') {
                $infos .= '<h2>Classement: ' . $location->classement_nom_fr . '</h2>';
            }

            $optionIncluses = '<h2>Services inclus</h2><ul>';
            $optionNonIncludes = '<h2>Services en supplément</h2><ul>';

            foreach($location->options as $option){
                if($option->locationoption_prix == 0){
                    $optionIncluses .= '<li>'.$option->nom_fr.'</li>';
                }elseif($option->inclus == "OPTIONNEL" || $option->locationoption_prix > 0){
                    $optionNonIncludes .= '<li>'.$option->nom_fr.' ('.$option->locationoption_prix.' &euro;)</li>';
                }
            }
            $optionIncluses .= '</ul>';
            $optionNonIncludes .= '</ul>';

            $infosStations = '';
            if($location->station_id > 0){
                $infosStations .= '<h2>Station(s):</h2>';
                $infosStations .= '<h3>'.$location->station_nom.'</h3>';
                $infosStations .= '<ul>';
                $infosStations .= '<li>Distance de la station: '.$location->distance_station_nom_fr.'</li>';
                $infosStations .= '<li>Distance des remontées: '.$location->distance_remontee_nom_fr.'</li>';
                $infosStations .= '</ul>';
            }
            if($location->station2_id > 0){
                $infosStations .= '<h3>'.$location->station2_nom.'</h3>';
                $infosStations .= '<ul>';
                $infosStations .= '<li>Distance de la station: '.$location->distance_station2_nom_fr.'</li>';
                $infosStations .= '<li>Distance des remontées: '.$location->distance_remontee2_nom_fr.'</li>';
                $infosStations .= '</ul>';
            }

            $infosStations .= '[cmci_voir_page_tarifs]';


            $infoCompl = '<h2>Informations complémentaire(s):</h2>';
            $infoCompl .= '<ul>';
            $tabDateHeureDepart = explode(' ', $location->heure_arrivee->date);
            $tabHeureDepart = explode('.', $tabDateHeureDepart[1]);
            $infoCompl .= '<li>Heure d\'arriv&eacute;e: '.str_replace(':', 'H', substr($tabHeureDepart[0], 0, -3)).'</li>';
            $tabDateHeureDepart = explode(' ', $location->heure_depart->date);
            $tabHeureDepart = explode('.', $tabDateHeureDepart[1]);
            $infoCompl .= '<li>Heure de d&eacute;part: '.str_replace(':', 'H', substr($tabHeureDepart[0], 0, -3)).'</li>';
            $infoCompl .= '<li>Ch&egrave;ques vacances: ';
            if($location->chequevac){
                $infoCompl .= 'Oui</li>';
            }else{
                $infoCompl .= 'Non</li>';
            }
            if($location->accompte > 0){
                $infoCompl .= '<li>Pourcentage d\'accompte ou d\'arrhes: '.$location->accompte.' %</li>';
            }
            if($location->caution > 0){
                $infoCompl .= '<li>Caution: '.$location->caution.' &euro;</li>';
            }
            if($location->charges > 0){
                $infoCompl .= '<li>Charges: '.$location->charges.' &euro;</li>';
            }
            if($location->tarif_saison_hiver > 0){
                $infoCompl .= '<li>Tarif pour toute la saison d\'hiver: '.$location->tarif_saison_hiver.' &euro;</li>';
            }
            if($location->tarif_saison_ete > 0){
                $infoCompl .= '<li>Tarif pour toute la saison d\'&eacute;t&eacute;: '.$location->tarif_saison_ete.' &euro;</li>';
            }
            if($location->locationdimanche_id == 1 || $location->locationdimanche_id == 3){
                $infoCompl .= '<li>Possibilité de louer du dimanche au dimanche: '.$location->locationdimanche_valeur.'</li>';
            }



            return $content. $infos.$optionIncluses.$optionNonIncludes.$infosStations.$infoCompl;
        }

        /**
         * @param $postID
         * @param $location
         */
        public static function createGallery($postID , $location) {
            // Create Gallery list
            if(!empty($location->photos)){
                $listPhotos = array();
                foreach($location->photos as $photo){

                    $attach_id = self::importMedia($location, $photo);
                    // If array is empty mean we are on the first picture. So register first picture as post thumbnail
                    if(empty($listPhotos)){
                        set_post_thumbnail($postID, $attach_id);
                    }
                    $listPhotos[] = $attach_id;

                }
                $serImages = serialize($listPhotos);
                // $serImages = $images;
                update_post_meta( $postID, 'meta-image', $serImages );
            }
        }

        /**
         * @param $location
         * @param $photo
         * @return mixed
         */
        public static function importMedia($location, $photo) {
            $file = $location->pathphotos.$photo->path;

            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();

            $tabPath = explode('/', $photo->path);
            $name = end($tabPath);
            $photoContent = sanitize_text_field($photo->commentaires);

            // Check if image url does not return something else than response code 200
            if(substr(get_headers($location->pathphotos.$photo->path)[0], 9, 3) == 200) {
                if (copy($location->pathphotos . $photo->path, $wp_upload_dir['path'] . '/' . $name)) {

                    $filetype = wp_check_filetype(basename($wp_upload_dir['path'] . '/' . $name), null);

                    // Prepare an array of post data for the attachment.
                    $attachment = array(
                        'guid' => $wp_upload_dir['url'] . '/' . basename($name),
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($name)),
                        'post_content' => $photoContent,
                        'post_status' => 'inherit'
                    );

                    // Insert the attachment.
                    $attach_id = wp_insert_attachment($attachment, $wp_upload_dir['path'] . '/' . $name);
                    // If upload is a success
                    if($attach_id > 0) {
                        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        // Generate the metadata for the attachment, and update the database record.
                        $attach_data = wp_generate_attachment_metadata($attach_id, $wp_upload_dir['path'] . '/' . $name);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        self::addMetaValue($attach_id, 'cmci_location_id', $location->id);

                        return $attach_id;

                    }
                }
            }
        }

        /**
         * Get data associated to a user
         * @return object The data
         */
        public static function isAbonnementActif()
        {

            $timeLimitAbonnement = get_option('cmci_date_fin_api_wpp');

            if($timeLimitAbonnement === false || $timeLimitAbonnement == '') {
                $data = self::getData();

                $timeLimitAbonnement = strtotime($data->date_fin_api_wpp->date);

                update_option('cmci_date_fin_api_wpp', $data->date_fin_api_wpp);


            }else{
                $timeLimitAbonnement = strtotime($timeLimitAbonnement->date);
            }
            if($timeLimitAbonnement < time()){
                return false;
            }

            return true;
        }

        public static function upgrade_plugin(){

            $current_version = get_option('cmci_version');

            // Check if OLD Chalet Montagne Private plugin was installed
            if($current_version == false){
                self::update_plugin_2_0();
            }elseif(version_compare(CMCI_VERSION, $current_version) > 0){

                $ver = str_replace('.', '_', CMCI_VERSION);

                $fonctionName = 'update_plugin_'.$ver;
                if(method_exists('ChaletMontagneAdmin', $fonctionName)) {
                    self::$fonctionName();
                    update_option('cmci_version', CMCI_VERSION);
                }
            }
        }

        /**
         * Mise à jour version 2.0
         * Convertit le contenu Chalet Montagne Privé en Chalet Montagne (Plugin payant vers Plugin gratuit)
         */
        private static function update_plugin_2_0(){

            global $wp_query;
            global $post;

            // Migration des options
            $arrOption = array('api_key',
                'hash_key',
                'user_data',
                'url_contact',
                'update_time',
                'update_date',
                'db_version',
                'list_rentals',
                'formDateAvailable',
                'formDateUnavailable',
                'formHeader',
                'formCancel',
                'privacy_policy',
                'pluginParam',
                'update_16'
            );

            foreach($arrOption as $cmpOption){
                $optionValue = get_option('cmp_'.$cmpOption);
                if($optionValue != false){
                    update_option('cmci_'.$cmpOption, $optionValue);
                    delete_option('cmp_'.$cmpOption);
                }
            }


            // Migration des meta données des pages
            $meta_keys = array(

                array( 'cmp_content','cmci_content' ),
                array( 'cmp_location_id','cmci_location_id' )
            );

            foreach ( $meta_keys as $k ) {

                $args = array(
                    'post_type'      => array('page'),
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'meta_key' => $k[0]
                );

                $wp_query = new WP_Query( $args );

                if ( $wp_query->have_posts() ) {
                    while ( $wp_query->have_posts() ) {
                        $wp_query->the_post();
                        $meta = get_post_meta( get_the_ID(), $k[0], true );
                        if ( $meta ) {
                            // Migrate the meta to the new name
                            update_post_meta(get_the_ID(), $k[1], $meta );  // add the meta with the new name
                            delete_post_meta( get_the_ID(), $k[0] );        // delete the old meta
                        }

                        $content = get_the_content();
                        $content = str_replace('cmp_', 'cmci_', $content);
                        $content = str_replace('<a class="btn btn-primary" href="#locationGallery">Voir les photos</a>', '[cmci_voir_galerie]', $content);

                        wp_update_post(array('ID' => get_the_ID(), 'post_content' => $content));
                    }
                }
                wp_reset_postdata();    // Restore original Post Data
            }


            // Ajout des métas pour les images qui étaient dans une galerie
            $args = array(
                'post_type'      => array('attachment'),
                'posts_per_page' => -1,
                'post_status'    => 'inherit',
            );

            $wp_query = new WP_Query( $args );

            if ( $wp_query->have_posts() ) {
                while ( $wp_query->have_posts() ) {
                    $wp_query->the_post();

                    $idImage = get_the_ID();

                    $args = array(
                        'post_type'      => array('page'),
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key' => 'meta-image',
                                'value' => $idImage,
                                'compare' => 'LIKE'
                            )
                        )
                    );

                    $wp_sub_query = new WP_Query($args);
                    if($wp_sub_query->have_posts()){
                        while ($wp_sub_query->have_posts()){
                            $wp_sub_query->the_post();
                            $IdLoc = get_post_meta( get_the_ID(), 'cmci_location_id', true );

                            if($IdLoc != '' && $IdLoc != false){
                                update_post_meta($idImage, 'cmci_location_id', $IdLoc );
                            }
                        }
                    }
                }
            }

            // Marquage des pages tarifs et création des pages galeries
            $args = array(
                'post_type'      => array('page'),
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'meta-image',
                        'value' => array(''),
                        'compare' => 'NOT IN'
                    )
                )
            );
            $wp_page_query = new WP_Query($args);
            if($wp_page_query->have_posts()){
                while ($wp_page_query->have_posts()){
                    $wp_page_query->the_post();
                    $IdLoc = get_post_meta( get_the_ID(), 'cmci_location_id', true );

                    $parentID = get_the_ID();

                    $args = array(
                        'post_type'      => array('page'),
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'post_parent' => $parentID
                    );
                    $wp_sub_page_query = new WP_Query($args);
                    if($wp_sub_page_query->have_posts()) {
                        while ($wp_sub_page_query->have_posts()) {
                            $wp_sub_page_query->the_post();
                            // Marquage de la page courante comme étant la page tarif
                            update_post_meta(get_the_ID(), 'cmci_page_type', 'tarif');
                            update_post_meta(get_the_ID(), 'cmci_location_id', $IdLoc);
                            $pageTarifId = get_the_ID();

                            wp_update_post(array('ID' => $pageTarifId, 'menu_order' => 2));

                            $pageTarifId = get_the_ID();

                        }
                    }

                    $args = array('post_author' => get_current_user_id(),
                        'post_date' => date('Y-m-d H:i:s'),
                        'post_title' => 'Galerie',
                        'post_status' => 'publish',
                        'post_content' => '<h2>'.get_the_title($parentID).'</h2>
<p><a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;
<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a>
</p>[cmci_galerie id="'.$IdLoc.'"]<p>
<a class="btn btn-primary" href="'.get_permalink($parentID).'">Voir la description du bien</a>&nbsp;&nbsp;
<a class="btn btn-primary" href="'.get_permalink($pageTarifId).'">Voir les tarifs</a></p>',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_type' => 'page',
                        'post_parent' => $parentID,
                        'menu_order' => 1
                    );
                    $idPageGalerie = self::addWpContent($args, 'posts');
                    // Marquage de la page comme étant la page galerie
                    update_post_meta($idPageGalerie, 'cmci_page_type', 'galerie');
                    update_post_meta($idPageGalerie, 'cmci_location_id', $IdLoc);

                    if($IdLoc != '' && $IdLoc != false){
                        update_post_meta($idImage, 'cmci_location_id', $IdLoc );
                    }
                }
            }

            if(is_dir(CMCI_UPLOAD_PATH . '/cmp')) {
                rename(CMCI_UPLOAD_PATH . '/cmp', CMCI_UPLOAD_PATH . '/cmci');
            }


            // Reste rentals list and update time
            delete_option('cmci_list_rentals');
            $api_key = get_option('cmci_api_key');
            $hash_key = get_option('cmci_hash_key');
            $data = self::getData($api_key, $hash_key);
            self::insertAllData($data->location, false);

            update_option('cmci_version', CMCI_VERSION, true);

        }

        /**
         * Mise à jour version 2.0
         * Marque les pages galeries & tarifs pour les retrouver peu importe l'ordre qu'elles sont enfants d'une page location
         */
        private function update_plugin_2_6(){

            // Récupération des pages identifiées comme pages d'une location
            $args = array(
                'post_type'      => array('page'),
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'cmci_location_id',
                        'value' => array(''),
                        'compare' => 'NOT IN'
                    )
                )
            );
            $wp_page_query = new WP_Query($args);
            if($wp_page_query->have_posts()) {
                while ($wp_page_query->have_posts()) {
                    $wp_page_query->the_post();
                    // Marquage page galerie
                    $pagesEnfants = get_posts(array(
                        'post_type' => 'page',
                        'post_parent' => get_the_ID(),
                        'orderby' => 'menu_order',
                        'order' => 'ASC'
                    ));
                    $page = current($pagesEnfants);

                    update_post_meta($page->ID, 'cmci_page_type', 'galerie');

                    // Marquage page tarif
                    $pagesEnfants = get_posts(array(
                        'post_type' => 'page',
                        'post_parent' => get_the_ID(),
                        'orderby' => 'menu_order',
                        'order' => 'ASC'
                    ));
                    $page = $pagesEnfants[1];

                    update_post_meta($page->ID, 'cmci_page_type', 'tarif');
                }
            }

        }

    }
}