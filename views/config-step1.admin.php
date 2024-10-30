<div class="container bs-wizard">
	<div class="row">
		<div class="col-md-12">
			<h4><?php  esc_html_e('Bienvenue!', 'chalet-montagne-private'); ?></h4>
			<p>
				Cet asssistant a pour but de vous aider dans la configuration de votre site pour qu'il communique avec votre compte Chalet Montagne. Mais aussi pour configurer votre site pour que vous n'ayez rien à faire.
			</p>
			<p>
				Durant l'installation vous n'aurez besoin que de votre <u>identifiant Chalet Montagne</u> ainsi que de votre <u>clé secrète</u> disponible sur votre espace client.
			</p>
			<p>
				<a href="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('nextStep', 2, $noHeader) ); ?>" class="center button button-primary"><?php  esc_html_e('C\'est parti!', 'chalet-montagne-private'); ?></a>
			</p>
		</div>
        <div class="col-md-12 text-center col-pricing">
            <h4 class="mb-4">Le plugin Chalet-Montagne</h4>
            <div class="row ">
                <div class="col"></div>
                <div class="col">Version<br/>gratuit</div>
                <div class="col">Version<br/>payant</div>
            </div>
            <div class="row ">
                <div class="col pb-3">Imporation du planning de votre/vos bien(s)<br />depuis Chalet-Montagne</div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Imporation des tarifs de votre/vos bien(s)<br />depuis Chalet-Montagne</div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Imporation des photos de votre/vos bien(s)<br />depuis Chalet-Montagne</div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Utilisation du <a href="#" data-toggle="modal" data-target="#staticBackdrop"><u><i>shortcode</i></u></a> du planning et des tarifs pour chaque bien</div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Mise à jour automatique du planning et des tarifs</div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Création des pages de votre site</div>
                <div class="col pt-3 alert-danger"><span class="dashicons-before dashicons-no"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Importation du contenu de vos annonces dans les pages de votre site</div>
                <div class="col pt-3 alert-danger"><span class="dashicons-before dashicons-no"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
            <div class="row ">
                <div class="col pb-3">Mise à jour du contenu de vos pages si vous les modifiez sur Chalet-Montagne</div>
                <div class="col pt-3 alert-danger"><span class="dashicons-before dashicons-no"></span></div>
                <div class="col pt-3 alert-success"><span class="dashicons-before dashicons-yes"></span></div>
            </div>
        </div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Shortcode?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Un shortcode est un petit morceau de texte qu'il vous suffira de coller dans le contenu d'une page qui affichera un contenu spécifique
                <br />
                Dans le cas du plugin Chalet-Montagne il existe quatre shortcodes différents.
                <br />
                Ils afficheront au choix:
                <ul>
                    <li>- le calendrier de disponibilités d'un bien</li>
                    <li>- la tarification d'un bien</li>
                    <li>- une galierie d'image d'un bien</li>
                    <li>- un formulaire de contact pour vous envoyer des messages</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>