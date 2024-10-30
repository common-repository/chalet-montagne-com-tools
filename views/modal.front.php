<div class="chaletMontagne">
    <div class="<?php if($isModal) echo "modal fade"; ?>" id="<?php echo $id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="" method="post" id="<?php echo $prefixInput; ?>sendMessage">
                <?php wp_nonce_field( $nonce ) ?>
                <input name="action" value="sendMessage" type="hidden" />
                <input name="prefix" value="<?php echo $prefixInput; ?>" type="hidden" />
                <input name="datetime" value="<?php echo time() ?>"  type="hidden" />
                <?php if(isset($idLoc)){ ?>
                    <input name="idLocation" value="<?php echo $idLoc ?>"  type="hidden" />
                <?php } ?>
                <input name="idLoueur" value="<?php echo $idLoueur ?>"  type="hidden" />
                <input name="h" value="<?php echo $hash;?>" type="hidden" />
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e( 'Formulaire de demande d\'informations' , 'chalet-montagne'); ?></h5>
                        <?php if($isModal){ ?>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        <?php } ?>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>name" class="col-form-label required"><?php esc_html_e( 'Nom' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control" name="nom" id="<?php echo $prefixInput; ?>name" required>
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>firstName" class="col-form-label required"><?php esc_html_e( 'Prénom' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control" name="prenom" id="<?php echo $prefixInput; ?>firstName" required>
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>mail" class="col-form-label required"><?php esc_html_e( 'E-mail' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control" name="email" id="<?php echo $prefixInput; ?>mail" required>
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ email ou téléphone obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                    <div class="invalid-email invalid-feedback">
                                        <?php esc_html_e( 'Email non valide' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>phone" class="col-form-label required"><?php esc_html_e( 'Téléphone' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control" name="telephone" id="<?php echo $prefixInput; ?>phone" required>
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ email ou téléphone obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                    <div class="invalid-email invalid-feedback">
                                        <?php esc_html_e( 'Téléphone non valide' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>nbAdult" class="col-form-label required"><?php esc_html_e( 'Adultes' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" onkeyup="clean_code(this);" class="form-control" name="adulte" id="<?php echo $prefixInput; ?>nbAdult" required>
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>nbChildren" class="col-form-label required"><?php esc_html_e( 'Enfants' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" onkeyup="clean_code(this);" class="form-control" name="enfant" id="<?php echo $prefixInput; ?>nbChildren">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>startDate" class="col-form-label required"><?php esc_html_e( 'Date de début' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control <?php echo $inputStartDisabled; ?>" name="dateArrivee" id="<?php echo $prefixInput; ?>startDate" required="required" placeholder="jj/mm/aaaa">
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="<?php echo $prefixInput; ?>endDate" class="col-form-label required"><?php esc_html_e( 'Date de fin' , 'chalet-montagne'); ?>:</label>
                                    <input type="text" class="form-control <?php echo $inputEndDisabled; ?>" name="dateDepart" id="<?php echo $prefixInput; ?>endDate" required="required" placeholder="jj/mm/aaaa">
                                    <div class="invalid-feedback">
                                        <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="errorDate"></p>
                            </div>
                        </div>
                        <?php if(isset($listLoc)){ ?>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="<?php echo $prefixInput; ?>idLoc" class="col-form-label required"><?php esc_html_e( 'Choix de la location' , 'chalet-montagne'); ?>:</label>
                                        <select name="idLocation" id="<?php echo $prefixInput; ?>idLoc" class="form-control">
                                            <option value=""></option>
                                            <?php foreach($listLoc as $idLoc => $nom){
                                                echo '<option value="'.$idLoc.'">'.$nom.'</option>';
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Message:</label>
                            <textarea class="form-control" name="commentaire" id="<?php echo $prefixInput; ?>message-text"></textarea>
                        </div>
                        <?php
                        if(get_option('cmci_privacy_policy') != false){
                            ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check form-checkbox">
                                        <input type="checkbox" id="<?php echo $prefixInput; ?>consentement" name="consentement" class="form-check-input checkboxConsentement" value="true" required />
                                        <label class="form-label-check" for="consentement">J’autorise l’enregistrement de mes données conformément à la <a href="<?php echo get_permalink(get_option('cmci_privacy_policy')); ?>">politique de confidentialité*</a></label>
                                        <div class="invalid-feedback">
                                            <?php esc_html_e( 'Champ obligatoire' , 'chalet-montagne'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="mt-3">
                            <span><?php esc_html_e( '(*) Champs obligatoires' , 'chalet-montagne'); ?></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="token" value="" />
                        <button type="button" class="btn btn-secondary btn-dismiss fermer" data-dismiss="modal"><?php esc_html_e( 'Fermer' , 'chalet-montagne'); ?></button>
                        <button type="button" class="btn btn-primary <?php echo $prefixInput; ?>sendMail"><?php esc_html_e( 'Envoyer message' , 'chalet-montagne'); ?></button>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        var $ = jQuery.noConflict();

        $(document).ready(function(){

            $('.<?php echo $prefixInput; ?>sendMail').click(function () {
                form = $(this).closest('form');
                //$(form).addClass('was-validated');
                inputs = $(form).find("input");
                var formSubmission = true;
                $('.invalid-feedback').hide();
                $.each(inputs, function(key, inputObj){
                    $(this).removeClass('fieldInvalid');
                    $(this).removeClass('fieldCheckBoxInvalid');
                    $(this).removeClass('fieldValid');
                    $(this).removeClass('fieldCheckboxValid');

                    if(inputObj.id === '<?php echo $prefixInput; ?>mail'){
                        objMail = inputObj;
                    }
                    if(inputObj.id === '<?php echo $prefixInput; ?>phone'){
                        objPhone = inputObj;
                    }
                    if(inputObj.id === '<?php echo $prefixInput; ?>consentement'){
                        if($(inputObj).is(':checked')){
                            $(this).addClass('fieldCheckboxValid');
                        }else{
                            $(this).addClass('fieldCheckBoxInvalid');
                            $(this).next().next().show();
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
                    formSubmission = false;
                }

                if($(objPhone).val().length > 0){
                    if(!isPhone($(objPhone).val())){
                        $(objPhone).addClass('fieldInvalid');
                        $(objPhone).next().next().show();
                        formSubmission = false;
                    }else{
                        $(objPhone).addClass('fieldValid');
                    }
                }

                if($(objMail).val().length > 0){
                    if(!isEmail($(objMail).val())){
                        $(objMail).addClass('fieldInvalid');
                        $(objMail).next().next().show();
                        formSubmission = false;
                    }else{
                        $(objMail).addClass('fieldValid');
                    }
                }

                <?php if(isset($listLoc)){ ?>

                if($('select#<?php echo $prefixInput;?>idLoc') != undefined){
                    if(!$('select#<?php echo $prefixInput;?>idLoc').val()){
                        $('select#<?php echo $prefixInput;?>idLoc').addClass('fieldInvalid');
                        $('select#<?php echo $prefixInput;?>idLoc').next().show();
                        formSubmission = false;
                    }else{
                        $('select#<?php echo $prefixInput;?>idLoc').addClass('fieldValid');
                    }
                }

                <?php } ?>

                if(formSubmission){
                    $("#<?php echo $prefixInput;?>sendMessage").submit();
                }
            });

            $('.fermer').click(function(){
                $('#modalReservation #<?php echo $prefixInput; ?>startDate').val('');
                $('#modalReservation #<?php echo $prefixInput; ?>endDate').val('');
            });

            <?php
            if($prefixInput == ''){
            ?>
            $('#startDate').datepicker({ dateFormat: 'dd/mm/yy'});
            $('#endDate').datepicker({ dateFormat: 'dd/mm/yy'});
            <?php
            }else{
            ?>
            $('#<?php echo $prefixInput; ?>startDate').datepicker({ dateFormat: 'dd/mm/yy'});
            $('#<?php echo $prefixInput; ?>endDate').datepicker({ dateFormat: 'dd/mm/yy'});
            <?php
            }
            ?>

        });

    </script>

</div>