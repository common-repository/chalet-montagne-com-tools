<?php if ( $type == 'alert' ){?>
	<div class='error'>
		<p><strong><i class="dashicons dashicons-flag"></i><?php printf( esc_html__( 'Code erreur : %s', 'chalet-montagne' ), $code ); ?></strong></p>
		<p><?php echo esc_html( $msg ); ?></p>
		<p><?php

		/* translators: the placeholder is a clickable URL that leads to more information regarding an error code. */
		printf( esc_html__( 'Pour plus d\'informations: %s' , 'chalet-montagne'), '<a href="http://www.chalet-montagne.com/plugin-errors/' . $code . '">http://www.chalet-montagne.com/plugin-errors/' . $code . '</a>' );
		?>
		</p>
	</div>
<?php }elseif ( $type == 'noKeyOrHash' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Le numéro de loueur et la clé ne peuvent pas être vides.', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php }elseif ( $type == 'wrongKeyOrHash' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Le couple numéro de loueur et clé secrète n\'est pas correct.', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php }elseif ( $type == 'notValidParamColor' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Une erreur est survenue pendant la mise à jour des couleurs.', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php }elseif ( $type == 'validParamColor' ) {?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Vos choix de couleurs ont été enregistrées.', 'chalet-montagne'); ?></h5>
	</div>
<?php }elseif ( $type == 'notValidParamAccount' ) {?>
    <div class="container alert alert-danger" role="alert">
        <h5 class="key-status failed"><?php esc_html_e( 'Une erreur est survenue pendant la mise à jour des préférences de désinstallation.', 'chalet-montagne'); ?></h5>
        <p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
    </div>
<?php }elseif ( $type == 'validParamAccount' ) {?>
    <div class="container alert alert-success" role="alert">
        <h5 class="key-status failed"><?php esc_html_e( 'Vos choix ont été mis à jour.', 'chalet-montagne'); ?></h5>
    </div>
<?php }elseif ( $type == 'notValidParamAuto' ) {?>
    <div class="container alert alert-danger" role="alert">
        <h5 class="key-status failed"><?php esc_html_e( 'Une erreur est survenue pendant la mise à jour des préférences de mise à jour automatique.', 'chalet-montagne'); ?></h5>
        <p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
    </div>
<?php }elseif ( $type == 'validParamAuto' ) {?>
    <div class="container alert alert-success" role="alert">
        <h5 class="key-status failed"><?php esc_html_e( 'Vos choix pour la mise à jour automatique ont été mis à jour.', 'chalet-montagne'); ?></h5>
    </div>
<?php }elseif ( $type == 'validAssignMenu' ) {?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Le menu a été réassigné.', 'chalet-montagne'); ?></h5>
	</div>
<?php }elseif ( $type == 'errorWpContent' ) {?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'Un menu existe déjà. Il n\'est pas possible de continuer l\'installation tant que celui-ci n\'est pas supprimé.', 'chalet-montagne');?><br /><?php esc_html_e( 'Nous vous invitons à aller le supprimer en allant dans le menu Apparence->Menus', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php }elseif( $type == 'validReSync'){?>
	<div class="container alert alert-success" role="alert">
		<h5 class="key-status failed"><?php esc_html_e( 'La synchronisation a été effectuée.', 'chalet-montagne'); ?></h5>
		<p class="description"><?php printf( __('Pensez à réorganiser vos pages et votre menu si vous venez d\'importer un nouveau chalet.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php }elseif( $type != ''){?>
	<div class="container alert alert-danger" role="alert">
		<h5 class="key-status failed"><?php echo $type ?></h5>
		<p class="description"><?php printf( __('Contactez-nous <a href="%s" target="_blank">Support Chalet Montagne</a> pour obtenir une assistance.', 'chalet-montagne'), 'http://www.chalet-montagne.com/contact'); ?></p>
	</div>
<?php } ?>
