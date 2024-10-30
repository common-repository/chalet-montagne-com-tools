var $ = jQuery.noConflict();
$(document).ready(function(){
	$('.single-featured-image-header').addClass('wrap');
});

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

function moveButton(){
	console.log('move');
	button = $('.ui-datepicker-next.ui-corner-all').get(0);
	console.log(button);
	allFirstMonthGroup = $('.ui-datepicker-header.ui-widget-header.ui-helper-clearfix.ui-corner-left');
	$(button).appendTo(allFirstMonthGroup[0]);
}

function isCourtSejours(date, courtsSejours){

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