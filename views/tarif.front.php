<script type="text/javascript">

	$ = jQuery.noConflict();
	$(document).ready(function() {

		$('#<?php echo $idModal; ?>').on('hidden.bs.modal', function (e) {
			$('.errorDate').empty();
			$('.sendMail').removeAttr('disabled');
		});

		$('.btn-reserver').click(function(){
			var nbNuitMini =  $(this).attr('data-nuitmin');

			console.log('nuit min: '+nbNuitMini);
			console.log('Date debut: '+$(this).data('datedebut'))
			console.log('Date fin: '+$(this).data('datefin'))

			if(nbNuitMini != undefined && !isNaN(nbNuitMini)){
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').removeAttr('disabled');
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').removeAttr('disabled');
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').removeClass('inputDisabled');
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').removeClass('inputDisabled');
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').datepicker('destroy');
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').datepicker('destroy');

				minDateIndex = $(this).data('datedebut');
				tabMinDate = minDateIndex.split('/');
				// Format mm/dd/YYYY
				minDateArrivee = new Date(tabMinDate[1]+'/'+tabMinDate[0]+'/'+tabMinDate[2]);
				minDateDepart = new Date(tabMinDate[1]+'/'+tabMinDate[0]+'/'+tabMinDate[2]);
				minDateDepart.setDate(minDateDepart.getDate() + parseInt(nbNuitMini));

				maxDateIndex = $(this).data('datefin');
				tabMaxDate = maxDateIndex.split('/');
				// Format mm/dd/YYYY
				maxDateDepart = new Date(tabMaxDate[1]+'/'+tabMaxDate[0]+'/'+tabMaxDate[2]);
				maxDateArrivee = new Date(tabMaxDate[1]+'/'+tabMaxDate[0]+'/'+tabMaxDate[2]);
				maxDateArrivee.setDate(maxDateArrivee.getDate() - parseInt(nbNuitMini));

				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').val($(this).data('datedebut'));
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').val($(this).data('datefin'));
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').datepicker({
                    dateFormat: 'dd/mm/yy',
					minDate: minDateDepart,
					maxDate: maxDateDepart,
					onSelect: function(date) {
						tabDateDepart = date.split('/');
						dateArrivee = $('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').val();
						tabDateArrivee = dateArrivee.split('/');
						checkDiffDays(tabDateArrivee, tabDateDepart, nbNuitMini);
					}
				});
				$('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').datepicker({
                    dateFormat: 'dd/mm/yy',
					minDate: minDateArrivee,
					maxDate: maxDateArrivee,
					onSelect: function(date) {
						tabDateArrivee = date.split('/');
						dateDepart = $('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').val();
						tabDateDepart = dateDepart.split('/');
						checkDiffDays(tabDateArrivee, tabDateDepart, nbNuitMini);
					}
				});

			}else {
                $('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>startDate').addClass('inputDisabled');
                $('#<?php echo $idModal; ?> #<?php echo $prefixInput; ?>endDate').addClass('inputDisabled');
				$('#<?php echo $prefixInput; ?>startDate').val($(this).data('datedebut'));
				$('#<?php echo $prefixInput; ?>endDate').val($(this).data('datefin'));
			}
		});

		$('.sendMail').click(function () {
			form = $(this).closest('form');
			//$(form).addClass('was-validated');
			inputs = $(form).find("input");
			var formSubmission = true;
			$('.invalid-feedback').hide();
			$.each(inputs, function(key, inputObj){
				$(this).removeClass('fieldInvalid');
				$(this).removeClass('fieldValid');

				if(inputObj.id === '<?php echo $prefixInput; ?>mail'){
					objMail = inputObj;
				}
				if(inputObj.id === '<?php echo $prefixInput; ?>phone'){
					objPhone = inputObj;
				}
console.log(inputObj.id)
                if(inputObj.id === '<?php echo $prefixInput; ?>consentement'){
                    if($(inputObj).is(':checked')){
                        $(this).addClass('fiedlvalid');
                    }else{
                        $(this).addClass('fiedlInvalid');
                        formSubmission = false;
                    }
                }

				if($(inputObj).hasClass('form-control') && (inputObj.id != '<?php echo $prefixInput; ?>mail' || inputObj.id != '<?php echo $prefixInput; ?>phone')){
					if(!$(this).val()){
						$(this).addClass('fieldInvalid');
						$(this).next().show();
						formSubmission = false;
					}else{
						$(this).addClass('fieldValid');
					}
				}
			});
			if($(objMail).val().length === 0 && $(objPhone).val().length === 0){
					$(objMail).next().show();
					$(objMail).addClass('fieldInvalid');
					$(objPhone).next().show();
					$(objPhone).addClass('fieldInvalid');
			}

			if($(objPhone).val().length > 0){
				if(!isPhone($(objPhone).val())){
					$(objPhone).addClass('fieldInvalid');
					$(objPhone).next().next().show();
				}else{
					$(objPhone).addClass('fieldValid');
				}
			}

			if($(objMail).val().length > 0){
				if(!isEmail($(objMail).val())){
					$(objMail).addClass('fieldInvalid');
					$(objMail).next().next().show();
				}else{
					$(objMail).addClass('fieldValid');
				}
			}

			if(formSubmission){
				$("#sendMessage").submit(function(){
					$.post('<?php echo admin_url( 'admin-ajax.php' ) ?>', $(this).serialize(), function(result){

						console.log(result);

						});
					});
			}
		});

	});

</script>

<div class='tarifs'>
	 <div class="container">
	<?php

	foreach($tarifs as $mois => $typePeriodes){
		echo '<div class="row row-periodes">';
			echo '<div class="col-12">';
				echo '<div class="row">';
					echo '<div class="col col-mois">';
						echo $mois;
					echo '</div>';
				echo '</div>';
			foreach($typePeriodes as $type => $periodes) {
				if(!empty($periodes)) {
					echo '<div class="row">';
						echo '<div class="col col-semaine">';
							if($type == 'semaine') {
								echo "Prix à la " . $type;
							}elseif($type == 'dernieres_minutes'){
								echo "Prix dernières minutes ";
							}else{
								echo "Prix courts séjours";
							}
						echo '</div>';
					echo '</div>';
					foreach ($periodes as $periode) {
						echo '<div class="row row-tarif">';
							echo '<div class="col-sm-4">';
								$tabDateDebut = explode('-', $periode['date_debut']);
								$tabDateFin = explode('-', $periode['date_fin']);
								echo "Du " . $tabDateDebut[2] . '/' . $tabDateDebut[1] . ' au ' . $tabDateFin[2] . '/' . $tabDateFin[1];
							echo '</div>';
						if (!$cacheModal)
							echo '<div class="col-sm-5 col-tarif">';
                        else
	                        echo '<div class="col-sm-7 col-tarif">';
							if ($periode['tarif_semaine'] && empty($periode['tarif_normal'])) {
								echo '<div class="tarif-semaine">';
								echo $periode['tarif_semaine'] . " &euro;";
								echo '</div>';
							} elseif (!empty($periode['tarif_promo']) && !empty($periode['tarif_normal'])) {
								echo '<div class="tarif-normal">';
								echo $periode['tarif_normal'] . "&euro;";
								echo '</div>';
								echo '<div class="tarif-promo">';
								echo $periode['tarif_promo'] . "&euro;";
								echo '</div>';
							} elseif (!empty($periode['tarif_base'])) {
								echo '<div class="tarif-semaine">';
								echo $periode['tarif_base'] . " &euro;/".$periode['nb_nuit_mini']."nuit(s)";
								if($periode['prix_nuit_supp'] > 0){
									echo ' + '.$periode['prix_nuit_supp']."&euro;/nuit	";
								}
								echo '</div>';
							}

							echo '</div>';
                            if (!$cacheModal)
                            {
	                            echo '<div class="col-sm-2 col-reserver">';
                                echo '<div class="btn-primary btn-reserver" data-toggle="modal" data-target="#modalReservationSemaine-'.$idLoc.'" data-dateDebut="' . $tabDateDebut[2] . '/' . $tabDateDebut[1] . '/' . $tabDateDebut[0] . '" data-dateFin="' . $tabDateFin[2] . '/' . $tabDateFin[1] . '/' . $tabDateFin[0] . '"';
	                            if(!empty($periode['nb_nuit_mini'])){
		                            echo 'data-nuitMin="'.$periode['nb_nuit_mini'].'"';
	                            }
	                            echo '>';
	                            echo '<img src="'.$urlPluginAssetsFolder.'assets/img/Mail-icon.png" />';
	                            echo '</div>';
	                            echo '</div>';
                            }
						echo '</div>';
					}
				}
			}
		echo '</div>';
		echo '</div>';
	}

	if($pluginGratuit){
	    ?>
        <div class="row">
        <div class="col-sm-12">
            <p style="font-style: italic; font-size: 10px;">Service fourni par   <a target="_blank" href="https://www.chalet-montagne.com"><img src="<?php echo CMCI_DIR;?>assets/img/logo-mini.png" alt="logo Chalet-montagne.com" style="box-shadow: 0 0 0 0px" /></a></p>
        </div>
        </div>

         <?php
    }

	?>
	 </div>
</div>

