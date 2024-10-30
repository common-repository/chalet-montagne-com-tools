<div class="container">
	<div class="row">
		<div class="col-md-12 bs-wizard step3">
            <?php

            if($folderExist){
            ?>
                <h4>Nous avons déjà trouvé des informations relatives à Chalet-Montagne. De ce fait nous n'allons pas tout réinstaller pour rien. Vous pouvez continuer l'installation</h4>
                <p>
                    <a href="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('nextStep', 4) ); ?>" class="center button button-primary"><?php  esc_html_e('Continuer', 'chalet-montagne-private'); ?></a>
                </p>
            <?php
            }else {
                ?>
                <!-- main content -->
                <h4 class=""><?php esc_html_e('Locations actives détectées sur votre compte Chalet-Montagne:', 'chalet-montagne-private'); ?></h4>
                <?php
                if (!empty($locations)) {
                    echo '<ul class="adminListLocations">';
                    foreach ($locations as $location) {
                        echo '<li>' . $location->nom . '</li>';
                    }
                    echo '</ul>';
                }
            }
			?>
		</div>
        <?php
        if ($plugin == "gratuit"){
        ?>
            Vous utilisez le plugin dans sa version gratuite. Vous ne bénéficierez que des shortcodes ; ni pages ni arborescence ne seront créées.        <?php
        }else {
        ?>
        <div class="col-md-12 pt-3">
            <p>
                Suite à l'importation de votre/vos location(s), nous vous proposons de créer l'arborescence suivante
                pour votre site:
            </p>
            <ul class="adminArborescence">
                <li>Accueil</li>
                <?php
                if (!empty($locations)) {
                    ?>
                    <li>
                        Nos locations
                        <ul class="adminArborescenceLocations">
                            <?php

                            foreach ($locations as $location) {
                                echo '<li>' . $location->nom;
                                echo '<ul>';
                                echo '<li>Galerie</li>';
                                echo '<li>Planning / Tarifs</li>';
                                echo '</ul>';
                                echo '</li>';
                            }

                            ?>
                        </ul>
                    </li>
                    <?php
                }
                ?>
                <li>Activités</li>
                <li>Contact (coordonnées / formulaire)</li>
                <li>Mentions légales</li>
            </ul>
        </div>
        <?php
        }

            if(!$folderExist && $createMenu){
                ?>
                <div class="col-md-12 bs-wizard">
                    <form name="chalet-montagne-conf" id="chalet-montagne-conf" action="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('nextStep', 3, true) ); ?>" method="POST">
                        <input type="hidden" name="action" value="import_data" />
                        <?php
                        if ($plugin != "gratuit") {
                            ?>
                            <input type="checkbox" value="true" checked="checked" name="creer_arborescence"/>Oui je veux créer l'arborescence proposée
                            <?php
                        }
                        ?>
                        <input class="button button-primary float-right" type="submit" name="get_key" value="<?php _e( 'Etape Suivante' ); ?>" />
                    </form>
                </div>
                <?php
            }
            ?>
            <div class="clearfix"></div>
            <div class="stepLoader">
                <div class="innerStepLoader">
                    <div class="row">
                        <div class="col-12 mt-5">
                            <img src="<?php echo CMCI_DIR; ?>/assets/img/loader.gif"/>
                        </div>
                        <div class="col-12">
                            <?php _e('Veuillez patienter pendant l\'importation des données.'); ?>
                            <br/>
                            <?php _e('Cela peut prendre quelques instants. Merci de patienter'); ?>
                        </div>
                    </div>
                </div>
            </div>
	</div>
</div>