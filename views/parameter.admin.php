<?php

$data = ChaletMontagneAdmin::getData();

$timeLimitAbonnement = get_option('cmci_date_fin_api_wpp');


if( isset( $_GET['update-wp-ca-bundle'] ) ){

    $crt_file = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
    $new_crt_url = 'http://curl.haxx.se/ca/cacert.pem';

    if( is_writable( $crt_file ) ){
        $new_str = file_get_contents( $new_crt_url );

        if( $new_str && strpos( $new_str, 'Bundle of CA Root Certificates' ) ){
            $up = file_put_contents( $crt_file, $new_str );

            echo $up ? 'OK: ca-bundle.crt updated' : 'ERROR: can`t put data to ca-bundle.crt';
        }
        else {
            echo 'ERROR: can\'t download curl.haxx.se/ca/cacert.pem';
        }
    }
    else {
        echo 'ERROR: ca-bundle.crt not fritable';
    }

    exit;
}

?>
<div class="config-wrap">
    <div class="wrapper">
        <div class="columns-2 float-left">
            <!-- main content -->
            <div class="box">
                <h2><?php esc_html_e( 'Paramètrage du plugin' , 'chalet-montagne');?></h2>
                <hr>
                <div class="columns-2 float-left indentLeft">
                    <form name="chalet-montagne-plugin-conf" id="" action="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('set_pluginParam') ); ?>" method="POST">
                        <input type="hidden" name="updatePluginParam" value="true" />
                        <h4><?php esc_html_e( 'Lors de la désactivation du plugin' , 'chalet-montagne');?></h4>
                        <p>
                            <input type="checkbox" name="cmci_keepAccount" value="true" <?php echo ($pluginParam['keepAccount']) ? 'checked="checked"' : ''; ?> /><span for="keepAccount"><?php esc_html_e('Garder mes informations pour ne pas avoir à les renseigner de nouveau', 'chalet-montagne'); ?></span>
                        </p>
                        <h4><?php esc_html_e( 'Lors de la suppression du plugin' , 'chalet-montagne');?></h4>
                        <p>
		                    <?php
		                    if(ChaletMontagneAdmin::checkMenu()){
			                    ?>
                                <input type="checkbox" name="cmci_keepPages" value="true" <?php echo ($pluginParam['keepPages']) ? 'checked="checked"' : ''; ?> /><span for="keepAccount"><?php esc_html_e('Conserver les pages ainsi que le menu créé par le plugin Chalet Montagne', 'chalet-montagne'); ?></span>
                                <br />
			                    <?php
		                    }
		                    ?>
                            <input type="checkbox" name="cmci_keepMedia" value="true" <?php echo ($pluginParam['keepMedias']) ? 'checked="checked"' : ''; ?> /><span for="keepAccount"><?php esc_html_e('Conserver les images importées par le plugin Chalet Montagne', 'chalet-montagne'); ?></span>
                        </p>
                        <h4><?php esc_html_e( 'Desactiver les formulaires Chalet-Montagne.com' , 'chalet-montagne');?></h4>
                        <p>
                            <input type="checkbox" name="cmci_cacheModalPlanning" value="true" <?php echo ($pluginParam['cacheModalPlanning']) ? 'checked="checked"' : ''; ?> /><span for="keepAccount"><?php esc_html_e('Ne plus afficher le formulaire de contact dans le planning', 'chalet-montagne'); ?></span>
                            <br />
                            <input type="checkbox" name="cmci_cacheModalTarif" value="true" <?php echo ($pluginParam['cacheModalTarif']) ? 'checked="checked"' : ''; ?> /><span for="keepAccount"><?php esc_html_e('Ne plus afficher le formulaire de contact dans les tarifs', 'chalet-montagne'); ?></span>
                            <br />
                        </p>
                        <p>
                            <input type="submit" class="button button-primary" name="validPluginParam" value="<?php esc_html_e( 'Enregistrer les modifications' , 'chalet-montagne');?>" />
                        </p>
                    </form>
                </div>
                <div class="clearfix"></div>
                <?php
                if(ChaletMontagneAdmin::isAbonnementActif()) {
                    if(ChaletMontagneAdmin::checkMenu()){
                        ?>
                        <hr/>
                        <h4><?php esc_html_e('Replacer le menu', 'chalet-montagne'); ?></h4>
                        <div class="columns-2 float-left indentLeft">
                            <p>Votre menu n'est plus présent sur le site? Cliquez sur le bouton ci-dessous pour le
                                réaffecter au premier emplacement disponible du thème courant</p>
                            <p>
                                <a class="button-primary"
                                   href="<?php echo esc_url(ChaletMontagneAdmin::get_page_url('set_menu')); ?>"><?php esc_html_e('Réaffecter le menu au premier emplacement', 'chalet-montagne'); ?></a>
                            </p>
                        </div>
                        <div class="clearfix"></div>
                        <h4><?php esc_html_e('Créer l\'arborescence', 'chalet-montagne'); ?></h4>
                        <hr>
                        <div class="columns-2 float-left indentLeft">
                            <p>Si vous n'avez pas créé l'arborescence à l'installation du plugin, vous pouvez la créer en
                                cliquant sur le bouton ci-dessous</p>
                            <p>
                                <a class="button-primary"
                                   href="<?php echo esc_url(ChaletMontagneAdmin::get_page_url('set_arborescence')); ?>"><?php esc_html_e('Créer l\'arborescence', 'chalet-montagne'); ?></a>
                            </p>
                        </div>
                        <div class="clearfix"></div>
                        <?php
                    }
                    ?>
                    <hr/>
                    <h4><?php esc_html_e('Remplacement automatique du contenu', 'chalet-montagne'); ?></h4>
                    <div class="columns-2 float-left indentLeft">
                        <form name="chalet-montagne-plugin-conf" id=""
                              action="<?php echo esc_url(ChaletMontagneAdmin::get_page_url('set_pluginParam')); ?>"
                              method="POST">
                            <input type="hidden" name="updatePluginParamAuto" value="true"/>
                            <p>
                                <input type="checkbox" name="cmci_auto_update_content"
                                       value="true" <?php echo ($pluginParamAuto['updatePages']) ? 'checked="checked"' : ''; ?> /><?php esc_html_e('Mettre à jour automatiquement le contenu des annonces si vous les modifiez sur Chalet-Montagne', 'chalet-montagne'); ?>
                                <br/>
                                <input type="checkbox" name="cmci_auto_update_media"
                                       value="true" <?php echo ($pluginParamAuto['updateMedias']) ? 'checked="checked"' : ''; ?> /><?php esc_html_e('Mettre à jour automatiquement les images des annonces si vous les modifiez sur Chalet-Montagne', 'chalet-montagne'); ?>
                            </p>
                            <p>
                                <input type="submit" class="button button-primary" name="validPluginParam"
                                       value="<?php esc_html_e('Enregistrer les modifications', 'chalet-montagne'); ?>"/>
                            </p>
                        </form>
                    </div>
                    <div class="clearfix"></div>
                    <?php
                }
                ?>
            </div>
            <?php
            if(ChaletMontagneAdmin::isAbonnementActif()) {
                ?>
                <div class="box">
                    <h4><?php esc_html_e('Re-synchroniser les informations', 'chalet-montagne'); ?></h4>
                    <hr>
                    <div class="columns-2 float-left indentLeft">
                        <p>Vous souhaitez remettre les informations présentes sur Chalet Montagne ou vous voulez
                            rajouter un nouveau Chalet?</p>
                        <form name="chalet-montagne-plugin-conf" id=""
                              action="<?php echo esc_url(ChaletMontagneAdmin::get_page_url('set_synchro')); ?>"
                              method="POST" class="formReSyncLocations">
                            <input type="hidden" name="validReSyncLocations" value="true"/>
                            <?php
                            echo '<p>' . esc_html('Quelles informations des locations suivantes voulez-vous re-synchroniser?', 'chalet-montagne') . '</p>';
                            echo '<ul>';
                            foreach ($locationsStored['locations'] as $idLocation => $location) {
                                echo '<li>' . $idLocation . ' - ' . $location['name'];
                                echo '<ul class="adminReSyncLocations">';
                                echo '<li><input type="checkbox" name="syncData[]" value="' . $idLocation . '-title" /> Titre</li>';
                                echo '<li><input type="checkbox" name="syncData[]" value="' . $idLocation . '-content" /> Contenu</li>';
                                echo '<li><input type="checkbox" name="syncData[]" value="' . $idLocation . '-images" /> Photos</li>';
                                echo '</ul>';
                                echo '</li>';
                            }
                            echo '</ul>';


                            if (!empty($locationsList)) {
                                echo '<p>' . esc_html('Quelle(s) location(s) voulez-vous importer?', 'chalet-montagne') . '</p>';
                                echo '<ul>';
                                foreach ($locationsList as $location) {
                                    echo '<li><input type="checkbox" name="getData[]" value="' . $location->id . '" />' . $location->nom . '</li>';
                                }
                                echo '</ul>';
                            }

                            ?>
                            <input type="submit" class="button button-primary" name="submitReSyncLocations"
                                   value="<?php esc_html_e('Re synchroniser les données', 'chalet-montagne'); ?>"/>
                        </form>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <?php
            }
            ?>
            <div class="box">
                <h4><?php esc_html_e( 'Choix des couleurs' , 'chalet-montagne');?></h4>
                <hr>
                <div class="columns-2 float-left indentLeft">
                    <form name="chalet-montagne-conf" id="chalet-montagne-conf" action="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('set_color') ); ?>" method="POST">
                        <input type="hidden" name="updateColor" value="true" />
                        <p>Vous souhaitez modifier les couleur du calendrier ainsi que celles du formulaire? Selectionnez les couleurs voulues pour les champs associés</p>
                        <div class="input-group colorpicker-component">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><?php esc_html_e( 'Couleur dates disponibles' , 'chalet-montagne');?></span>
                            </div>
                            <input name="color-date-available" type="text" class="form-control input-lg" value="<?php if(!empty(get_option('cmci_formDateAvailable'))){echo get_option('cmci_formDateAvailable');}else{echo "#649316";} ?>" data-color="<?php if(!empty(get_option('cmci_formDateAvailable'))){echo ChaletMontagneAdmin::hex2rgb(get_option('cmci_formDateAvailable'));}else{echo "#649316";} ?>"/>
                            <span class="input-group-addon"><i></i></span>
                        </div>
                        <div class="input-group colorpicker-component">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><?php esc_html_e( 'Couleur dates non-disponibles' , 'chalet-montagne');?></span>
                            </div>
                            <input name="color-date-unavailable" type="text" class="form-control input-lg" value="<?php if(!empty(get_option('cmci_formDateUnavailable'))){echo get_option('cmci_formDateUnavailable');}else{echo "#ff3232";} ?>" data-color="<?php if(!empty(get_option('cmci_formDateUnavailable'))){echo ChaletMontagneAdmin::hex2rgb(get_option('cmci_formDateUnavailable'));}else{echo "#ff3232";} ?>"/>
                            <span class="input-group-addon"><i></i></span>
                        </div>
                        <hr>
                        <div class="input-group colorpicker-component">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><?php esc_html_e( 'Formulaire de contact en-tête' , 'chalet-montagne');?></span>
                            </div>
                            <input name="color-form-header" type="text" class="form-control input-lg" value="<?php if(!empty(get_option('cmci_formHeader'))){echo get_option('cmci_formHeader');}else{echo "#f2a554";} ?>" data-color="<?php if(!empty(get_option('cmci_formHeader'))){echo ChaletMontagneAdmin::hex2rgb(get_option('cmci_formHeader'));}else{echo "#f2a554";} ?>"/>
                            <span class="input-group-addon"><i></i></span>
                        </div>

                        <div class="input-group colorpicker-component">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><?php esc_html_e( 'Formulaire de contact bouton annuler' , 'chalet-montagne');?></span>
                            </div>
                            <input name="color-form-cancel" type="text" class="form-control input-lg"  value="<?php if(!empty(get_option('cmci_formCancel'))){echo get_option('cmci_formCancel');}else{echo "#372020";} ?>" data-color="<?php if(!empty(get_option('cmci_formCancel'))){echo ChaletMontagneAdmin::hex2rgb(get_option('cmci_formCancel'));}else{echo "#372020";} ?>"/>
                            <span class="input-group-addon"><i></i></span>
                        </div>
                        <br />
                        <input type="submit" class="button button-primary" name="validColor" value="<?php esc_html_e( 'Enregistrer les modifications' , 'chalet-montagne');?>" />
                        <script>
                            var $ = jQuery.noConflict();

                            $(function () {
                                $('.colorpicker-component').colorpicker({format: "hex"});

                            });
                        </script>

                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
