<div class="config-wrap">

	<div class="wrapper">
		<div id="post-body" class="columns-2">
			<!-- main content -->
			<div class="box">
				<div class="float-left">
					<strong class=""><?php esc_html_e( 'Activer le service' , 'chalet-montagne');?></strong>
					<p>Obtenir une clé secrète pour synchroniser votre compte</p>
					<p>Rendez vous dans votre espace membre à la rubrique "Site Perso / Outils / Extension Wordpress Gratuite"</p>
				</div>
				<form name="chalet-montagne-activate" id="chalet-montagne-conf" action="https://www.chalet-montagne.com/membres/site-perso/outils-site-perso/" target="_blank" method="POST">
					<div class="float-right">
						<input class="right button button-primary" type="submit" name="get_key" value="<?php _e( 'Obtenir une clé' ); ?>" />
					</div>
				</form>
			</div>
			<div class="box dark">
				<div class="float-left">
					<strong class=""><?php esc_html_e( 'Saissisez vos informations' , 'chalet-montagne');?></strong>
					<form name="chalet-montagne-conf" id="chalet-montagne-conf" action="<?php echo esc_url( ChaletMontagneAdmin::get_page_url() ); ?>" method="POST">
						<!--<input class="" name="key" type="text">-->
						<p>
							<label for="loueur">Numéro de loueur (identifiant Chalet-Montagne.com)</label>
							<input id="loueur" class="" name="key" placeholder="<?php esc_html_e( 'Entrez le numéro de loueur' , 'chalet-montagne');?>" type="text">
						</p>
						<p>
							<label for="hash">Clé secrète</label>
							<input id="hash" class="" name="hash" placeholder="<?php esc_html_e( 'Entrez votre clé secrète' , 'chalet-montagne');?>" type="text">
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

		<br class="clear">
	</div>

</div>