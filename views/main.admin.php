<div class="config-wrap">
    <div class="wrapper">
        <div class="columns-2 float-left">
            <!-- main content -->
            <div class="box">
                <h2><?php esc_html_e( 'Liste de vos locations' , 'chalet-montagne');?></h2>
                <hr>
                <?php

                $listRentals = get_option('cmci_list_rentals');

                foreach ($locations as $idLoc => $loc): ?>
                    <div class="loc">
                        <div class="columns-2 float-left">
                            <h2><?php echo $loc['name']; ?></h2>
                            <?php if(get_option('WPLANG') === 'fr_FR'): ?>
                                <p><?php echo $loc['descFr']; ?></p>
                            <?php else: ?>
                                <p><?php echo $loc['descEn']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo ChaletMontagneAdmin::shortcode_text('planning', $idLoc); ?><strong> Code à copier/coller pour afficher le planning des disponibilités</strong>
                        <br />
                        <?php echo ChaletMontagneAdmin::shortcode_text('tarifs', $idLoc); ?><strong> Code à copier/coller pour afficher les tarifs</strong>
                        <?php
                        if(ChaletMontagneAdmin::isAbonnementActif()){

                            ?>
                            <br />
                            <?php echo ChaletMontagneAdmin::shortcode_text('galerie', $idLoc); ?><strong> Code à copier/coller pour afficher la galerie</strong>
                            <?php
                        }
                        ?>
                        <div class="clearfix"></div>
                        <div class="columns-2 float-left talign-left">
                            <?php


                            $timeUpdateRental = $listRentals[$idLoc];

                            ?>
                            Dernière mise à jour du planning et tarif le: <?php echo date_i18n('d F Y à H:i', $timeUpdateRental + 7200, true); ?>
                        </div>
                        <br />
                        <br />
                        <br />
                        <?php

                        //                        if(ChaletMontagneAdmin::isAbonnementActif()) {
                        ?>
                        <div class="columns-1 float-right talign-right">
                            <a class="button-primary"
                               href="<?php echo esc_url(ChaletMontagneAdmin::get_page_url('update_loc', $idLoc)); ?>"><?php esc_html_e('Mettre à jour le planning et les tarifs de cette location', 'chalet-montagne'); ?></a>
                        </div>
                        <?php
                        //                        }
                        ?>
                        <div class="clearfix"></div>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="columns-1 float-left">
            <!-- main content -->
            <div class="box">
                <h2><?php esc_html_e('Informations', 'chalet-montagne'); ?></h2>
                <hr>
                <?php
                if (!is_array($user)) $user = unserialize($user);
                foreach($user as $k => $val){
                    echo '<p>'.ucfirst($k).': '.$val.'</p>';
                }
                ?>
                <strong>Code à copier/coller pour afficher le formulaire de contact</strong><br />
                [cmci_contact]
                <hr>
                <a href="<?php echo esc_url( ChaletMontagneAdmin::get_page_url( 'delete_key' ) ); ?>" onclick="return confirm('<?php esc_html_e('Êtes-vous sûr(e) de vouloir déconnecter le compte? (Toutes les données seront supprimées)', 'chalet-montagne'); ?>');"><?php esc_html_e('Déconnecter le compte', 'chalet-montagne'); ?></a>
                <hr>
                <a href="<?php echo esc_url( ChaletMontagneAdmin::get_page_url( 'resync-api' ) ); ?>"><?php esc_html_e('Re synchroniser les données', 'chalet-montagne'); ?></a>
                <hr>
                <?php
                if(isset($resync_api)){
                    echo '<h5>Données récupérées et stockées dans l\'api:</h5>';
                    echo '<ul>';
                    echo '<li>Date fin api: '.$resync_api['cmci_date_fin_api_wpp']->date.'</li>';
                    echo '<li>Date mise à jour: '.$resync_api['cmci_update_date'].'</li>';
                    echo '<li>Liste des biens:<ul>';
                    foreach($resync_api['cmci_list_rentals'] as $idLoc => $timeLOC){
                        echo '<li>ID Loc: '.$idLoc.' mis à jour le: '.$timeLOC.' / '.date('d-m-Y H:i:s', $timeLOC).'</li>';
                    }
                    echo '</ul></li>';
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

        <br class="clear">
    </div>
</div>