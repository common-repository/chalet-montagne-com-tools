<script>

	$ = jQuery.noConflict();

    var disabledDays = <?php echo $events ?>;
    var startDays = <?php echo $startEvents ?>;
    var endDays = <?php echo $endEvents ?>;
    <?php
    if(!isset($courtsSejours) OR strlen($courtsSejours) == 0){
        $courtsSejours = "''";
    }
    if(!isset($nbNuitMini) OR strlen($nbNuitMini) == 0){
        $nbNuitMini = "''";
    }
    ?>
    var courtsSejours = <?php echo $courtsSejours; ?>;
    var tabNbNuitMini = <?php echo $nbNuitMini; ?>;
    var today = new Date();

	$(document).ready(function() {

        initCalendrier('<?php echo $idLoc.'-calendrier'; ?>', '<?php echo $prefixInput; ?>', <?php echo $events ?>, <?php echo $startEvents ?>, <?php echo $endEvents ?>);

		$('#modalReservation').on('hidden.bs.modal', function (e) {
			$('.errorDate').empty();
			$('.sendMail').removeAttr('disabled');
		});


		$('.sendMail').click(function () {
			form = $(this).closest('form');
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
				$("#<?php echo $prefixInput; ?>sendMessage").submit(function(){
					console.log('submit');
					$.post('<?php echo admin_url( 'admin-ajax.php' ) ?>', $(this).serialize(), function(result){
						});
					});
			}
		});

		/*$('.ui-datepicker-multi').css({'width': '100%'});*/
        /*
                if($(document).width() < 580){
                  moveButton();
                }
        /*
                $('body').on('DOMNodeInserted', '#calendar', function(e) {
                    if ($(e.target).hasClass('ui-datepicker-group-first') && $(document).width() < 580 ) {
                        moveButton();
                    }
                });*/
	});


	function getIndexCourtsSejours(date, listeCourtsSejours){

		console.log('Date: '+date);

		indexKey = 0;

		for( var key in listeCourtsSejours){
			listDate = listeCourtsSejours[key]
			if(listDate.indexOf(date) != -1){
				indexKey = key;
			}
		};

		return indexKey;
	}

</script>


<div id='calendar' class="calendar <?php echo $idLoc.'-calendrier' ?>"></div>
<?php

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