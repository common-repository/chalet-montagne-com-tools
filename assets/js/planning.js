var $ = jQuery.noConflict();

$(document).ready(function() {

    /*if(disabledDays != undefined && startDays != undefined && endDays != undefined && today != undefined ) {

        $(".calendar").datepicker({
            numberOfMonths: [3, 2],
            minDate: today,
            firstDay: 1,
            dateFormat: 'dd/mm/yy',
            onSelect: function (date) {
                arrayDate = date.split('/');
                var currentDate = new Date(arrayDate[1] + '/' + arrayDate[0] + '/' + arrayDate[2]);
                currentDate.setDate(currentDate.getDate() + 1);
                $('#endDate').datepicker({minDate: currentDate, dateFormat: 'dd/mm/yy', altFormat: 'dd/mm/yy'});
                $("#modalReservation #startDate").val(date);
                $('#modalReservation').modal('show');
            },
            onChangeMonthYear: function () {
                moveButton()
            },
            beforeShowDay: function (date) {
                var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();

                if (date > today) {

                    for (i = 0; i < disabledDays.length; i++) {
                        if ($.inArray(y + '-' + (m + 1) + '-' + d, disabledDays) != -1) {
                            //return [false];
                            return [false, 'loc-disable', ''];
                        }
                    }

                    for (i = 0; i < startDays.length; i++) {
                        if ($.inArray(y + '-' + (m + 1) + '-' + d, startDays) != -1) {
                            //return [false];
                            return [true, 'loc-start', ''];
                        }
                    }

                    for (i = 0; i < endDays.length; i++) {
                        if ($.inArray(y + '-' + (m + 1) + '-' + d, endDays) != -1) {
                            //return [false];
                            return [true, 'loc-end', ''];
                        }
                    }

                    return [true, 'loc-available', ''];

                } else {
                    return [false];
                }
            },
        });
    }*/
    $('.sendMail').click(function () {
        form = $(this).closest('form');
        inputs = $(form).find("input");
        var formSubmission = true;
        $('.invalid-feedback').hide();
        $.each(inputs, function(key, inputObj){
            $(this).removeClass('fieldInvalid');
            $(this).removeClass('fieldValid');

            if(inputObj.id === 'mail'){
                objMail = inputObj;
            }
            if(inputObj.id === 'phone'){
                objPhone = inputObj;
            }

            if($(inputObj).hasClass('form-control') && (inputObj.id != 'mail' || inputObj.id != 'phone')){
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
                console.log('submit');
                $.post( ajaxurl, $(this).serialize(), function(result){
                });
            });
        }
    });

    if($(document).width() < 580){
        moveButton();
    }

    $('body').on('DOMNodeInserted', '#calendar', function(e) {
        if ($(e.target).hasClass('ui-datepicker-group-first') && $(document).width() < 580 ) {
            moveButton();
        }
    });
});

function moveButton(){
    console.log('move');
    button = $('.ui-datepicker-next.ui-corner-all').get(0);
    console.log(button);
    allFirstMonthGroup = $('.ui-datepicker-header.ui-widget-header.ui-helper-clearfix.ui-corner-left');
    $(button).appendTo(allFirstMonthGroup[0]);
}

function isPhone(phone) {
    var regex = /^([0-9])|\s/;
    return regex.test(phone);
}

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function clean_code(elem){
    var regex_cell = /[^[0-9 +]]*/gi;
    elem.value = elem.value.replace(regex_cell, '');
}

function checkDiffDays(tabDateArrivee, tabDateDepart, nbNuitMini){
    // Format YYYY-mm-dd
    dateDepart = new Date(tabDateDepart[2]+'-'+tabDateDepart[1]+'-'+tabDateDepart[0]);
    dateArrivee = new Date(tabDateArrivee[2]+'-'+tabDateArrivee[1]+'-'+tabDateArrivee[0]);
    diffDays = diffDate(dateDepart, dateArrivee);
    if(parseInt(diffDays) < parseInt(nbNuitMini)){
        $('.errorDate').text('Il faut un minimum de '+parseInt(nbNuitMini)+' nuit(s)');
        $('.sendMail').attr('disabled', 'disabled');
    }else{
        $('.errorDate').empty();
        $('.sendMail').removeAttr('disabled');
    }
}

function diffDate(objDateMax, objDateMin){
    var timeDiff = objDateMax.getTime() - objDateMin.getTime();
    var diffDays = Math.round(timeDiff / (1000 * 3600 * 24));
    return diffDays;
}


function initCalendrier(classCalendrier, prefix, varDisableDays, varStartDays, varEndDays) {

    $.datepicker.setDefaults($.datepicker.regional['fr']);

    $("."+classCalendrier).datepicker({
        numberOfMonths: [ 3, 2 ],
        minDate: today,
        firstDay : 1,
        dateFormat: 'dd/mm/yy',
        onSelect: function(date) {

            $('#modalReservation #'+prefix+'startDate').datepicker('destroy');
            $('#modalReservation #'+prefix+'endDate').datepicker('destroy');

            arrayDate = date.split('/');
            minDateArrivee = new Date(arrayDate[1]+'/'+arrayDate[0]+'/'+arrayDate[2]);
            minDateDepart = new Date(arrayDate[1]+'/'+arrayDate[0]+'/'+arrayDate[2]);
            //Vérification si la date sélectionnée est présente dans le tableau des courts séjours
            index = getIndexCourtsSejours(date.replace(new RegExp('/', 'g'),'-'), courtsSejours);
            nbNuitMini = 1;
            if(index != 0){
                $('#modalReservation #'+prefix+'startDate').removeAttr('disabled');
                $('#modalReservation #'+prefix+'startDate').removeClass('inputDisabled');
                nbNuitMini = tabNbNuitMini[index];
                tabIndex = index.split('/');

                minDateIndex = tabIndex[0];
                tabMinDate = minDateIndex.split('-');
                minDateArrivee = new Date(tabMinDate[1]+'/'+tabMinDate[2]+'/'+tabMinDate[0]);
                minDateDepart = new Date(tabMinDate[1]+'/'+tabMinDate[2]+'/'+tabMinDate[0]);
                minDateDepart.setDate(minDateDepart.getDate() + parseInt(nbNuitMini));

                maxDateIndex = tabIndex[1];
                tabMaxDate = maxDateIndex.split('-');
                maxDateDepart = new Date(tabMaxDate[1]+'/'+tabMaxDate[2]+'/'+tabMaxDate[0]);
                maxDateArrivee = new Date(tabMaxDate[1]+'/'+tabMaxDate[2]+'/'+tabMaxDate[0]);
                maxDateArrivee.setDate(maxDateArrivee.getDate() - parseInt(nbNuitMini));

                $('#modalReservation #'+prefix+'endDate').datepicker({
                    minDate: minDateDepart,
                    maxDate: maxDateDepart,
                    dateFormat: 'dd/mm/yy',
                    onSelect: function(date) {
                        tabDateDepart = date.split('/');
                        dateArrivee = $('#modalReservation #'+prefix+'startDate').val();
                        tabDateArrivee = dateArrivee.split('/');
                        checkDiffDays(tabDateArrivee, tabDateDepart, nbNuitMini);
                    }
                });
                $('#modalReservation #'+prefix+'startDate').datepicker({
                    minDate: minDateArrivee,
                    maxDate: maxDateArrivee,
                    dateFormat: 'dd/mm/yy',
                    onSelect: function(date) {
                        dateDepart = $('#modalReservation #'+prefix+'endDate').val();
                        tabDateDepart = dateDepart.split('/');
                        tabDateArrivee = date.split('/');
                        checkDiffDays(tabDateArrivee, tabDateDepart, nbNuitMini);
                    }
                });

            }else {
                minDateDepart.setDate(minDateDepart.getDate() + parseInt(nbNuitMini));
                maxDateArrivee = new Date(arrayDate[1]+'/'+arrayDate[0]+'/'+arrayDate[2]);
                maxDateArrivee.setDate(maxDateArrivee.getFullYear()+1);
                maxDateDepart = new Date(arrayDate[1]+'/'+arrayDate[0]+'/'+arrayDate[2]);
                maxDateDepart.setDate(maxDateDepart.getFullYear()+1);

                $("#modalReservation #"+prefix+"startDate").val(date);
                $('#modalReservation #'+prefix+'endDate').datepicker({minDate: minDateDepart, maxDate: maxDateDepart, dateFormat: 'dd/mm/yy'});
                $('#modalReservation #'+prefix+'startDate').datepicker({minDate: minDateArrivee, maxDate: maxDateArrivee, dateFormat: 'dd/mm/yy'});
            }


            $('#modalReservation').modal('show');
        },
        onChangeMonthYear : function(){moveButton()},
        beforeShowDay: function(date){
            var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();

            if(date > today){

                for (i = 0; i < varDisableDays.length; i++) {
                    if($.inArray(y + '-' + (m+1) + '-' + d,varDisableDays) != -1) {
                        //return [false];
                        return [false, 'loc-disable', ''];
                    }
                }

                for (i = 0; i < varStartDays.length; i++) {
                    if($.inArray(y + '-' + (m+1) + '-' + d,varStartDays) != -1) {
                        //return [false];
                        return [true, 'loc-start', ''];
                    }
                }

                for (i = 0; i < varEndDays.length; i++) {
                    if($.inArray(y + '-' + (m+1) + '-' + d,varEndDays) != -1) {
                        //return [false];
                        return [true, 'loc-end', ''];
                    }
                }

                return [true, 'loc-available', ''];

            }else {
                return [false];
            }
        },
    });

}