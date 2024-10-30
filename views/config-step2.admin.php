<div class="container">
	<div class="row">
		<div class="col-md-12 bs-wizard">
			<!-- main content -->
			<div class="box dark">
				<div class="float-left">
					<h4 class=""><?php esc_html_e( 'Saissisez vos informations' , 'chalet-montagne-private');?></h4>
					<form name="chalet-montagne-conf" id="chalet-montagne-conf" action="<?php echo esc_url( ChaletMontagneAdmin::get_page_url('nextStep', 2, true) ); ?>" method="POST">
						<p>
							<label for="loueur">Numéro de loueur<br />(identifiant Chalet-Montagne.com)</label>
							<input id="loueur" class="" name="key" placeholder="<?php esc_html_e( 'Entrez le numéro de loueur' , 'chalet-montagne-private');?>" type="text">
						</p>
						<p>
							<label for="hash">Clé secrète</label>
							<input id="hash" class="" name="hash" placeholder="<?php esc_html_e( 'Entrez votre clé secrète' , 'chalet-montagne-private');?>" type="text">
						</p>
						<p>
							<input type="hidden" name="action" value="enter-key">
						</p>
						<?php wp_nonce_field( ChaletMontagneAdmin::NONCE ) ?>
						<input class="button button-secondary" type="submit" name="get_key" value="<?php _e( 'Valider mes informations' ); ?>" />
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12 bs-wizard">
			<div class="box">
				<div class="float-left">
					<h4 class=""><?php esc_html_e( 'Activer le service' , 'chalet-montagne');?></h4>
					<p>Obtenir une clé secrète pour synchroniser votre compte</p>
					<p>Rendez vous dans votre espace membre à la rubrique "Site Perso / Outils / Extension Wordpress Gratuite"</p>
				</div>
				<form name="chalet-montagne-activate" id="chalet-montagne-conf" action="https://www.chalet-montagne.com/membres/site-perso/outils-site-perso/" target="_blank" method="POST">
					<div class="float-right">
						<input class="right button button-primary" type="submit" name="get_key" value="<?php _e( 'Obtenir une clé' ); ?>" />
					</div>
				</form>
			</div>
		</div>

	</div>

</div>