
// Ready and Load event

function preloader(){
	if(pl_quote_value.trim() !== ''){
		pl_quote_value = '"'+pl_quote_value+'"';
	}

	var html = '<div class="preloader"><div class="preloader-ripple"><div></div><div></div></div><div class="preloader-text">'+pl_quote_value+'<div class="preloader-info">'+pl_author_value+'</div></dv></div>';

	if(pl_template_value == '3'){
		var html = '<div class="preloader"><div class="preloader-ripple"><div></div><div></div><div></div><div></div></div><div class="preloader-text">'+pl_quote_value+'<div class="preloader-info">'+pl_author_value+'</div></dv></div>';
	}
	else if(pl_template_value == '4'){
		var html = '<div class="preloader"><div class="preloader-ripple"><div></div><div></div><div></div></div><div class="preloader-text">'+pl_quote_value+'<div class="preloader-info">'+pl_author_value+'</div></dv></div>';
	}
	else if(pl_template_value == '5'){
		var html = '<div class="preloader"><div class="preloader-ripple"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div><div class="preloader-text">'+pl_quote_value+'<div class="preloader-info">'+pl_author_value+'</div></dv></div>';
	}
	
	jQuery('body').append(html);
}

jQuery(function () { 
	//console.log("adding")
	preloader();
});


jQuery(window).on('load', function(){
	//destroy loader
	//console.log("closing");
	jQuery(".preloader").fadeOut();
	//jQuery('body').find('.preloader').remove();

});