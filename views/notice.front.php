<?php if ( $type == 'contactOK' ) {?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Votre message a été envoyé.', 'chalet-montagne'); ?></h5>
	</div>
<?php }elseif ( $type == 'contactNotOK' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Une erreur est survenue pendant l\'envoi du message. Veuillez contacter l\'administrateur du site.', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact/'); ?></p>
	</div>
<?php }elseif ( $type == 'validParamAccount' ) {?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Vos choix ont été mis à jour.', 'chalet-montagne'); ?></h5>
	</div>
<?php }elseif ( $type == 'validAssignMenu' ) {?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Le menu a été réassigné.', 'chalet-montagne'); ?></h5>
	</div>
<?php }elseif ( $type == 'errorWpContent' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Une erreur est survenue pendant la création du contenu', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact/'); ?></p>
	</div>
<?php }?>