$(document).ready(function(){
	if ($(window).width()>700) {
		$('#c_preference ul').append("<li><form id='yagp_form'><span><input id='goto' type='number' placeholder='id'><button type='submit'><i class='fas fa-external-link-alt'></i></button></span></form></li>");
		var color=$('#champRecherche input').css("color");
		var background=$('#champRecherche input').css("background-color");
		var border=$('#champRecherche input').css("border");
		var height=$('#champRecherche input').css("height");
		var width=$('#champRecherche input').css("width");
		$('#goto').css({"color":color,"background-color":background,"border":border,"height":height,"width":width});
		var radius=$('#champRecherche button').css("border-radius");
		var background=$('#champRecherche button').css("background-color");
		var border=$('#champRecherche button').css("border");
		var height=$('#champRecherche button').css("height");
		var align=$('#champRecherche button').css("vertical-align");
		$('#yagp_form button').css({"border-radius":radius,"background-color":background,"border":border,"height":height,"vertical-align":align});
		$('#yagp_form').submit(function(e){
			e.preventDefault(e);
			var id=$('#goto').val();
			window.location.href=CFG_GLPI.url_base+"/index.php?redirect=ticket_"+id+"&noAUTO=1";
		});
	}
});