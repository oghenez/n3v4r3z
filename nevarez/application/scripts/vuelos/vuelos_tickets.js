var json_data = {};
json_data['vuelos'] = {};
var aux_inc = 0;

$(function(){
	$('#CgrVuelos').on('click',function(){
		cargar_vuelos();
	});
});

function cargar_vuelos(){
	var is_ok = false;
	var all_ok = true;
	
	var indice = window.parent.indice;
	
	$(':checkbox:checked').each(function(){
		var vuelos_selecc = window.parent.vuelos_selec;
		for(var i in vuelos_selecc)
			for(var x in vuelos_selecc[i])
				if(vuelos_selecc[i][x]== $(this).val()){
					all_ok = false;break;
				}
	});
	
	if(all_ok){
		is_ok=true;
		window.parent.vuelos_selec[indice] = [];
		$(':checkbox:checked').each(function(){
			var data = $(this).val().split('&');
			json_data['vuelos']['v'+aux_inc] = {};
			json_data['vuelos']['v'+aux_inc].id_cliente	= data[0];
			json_data['vuelos']['v'+aux_inc].id_piloto	= data[1];
			json_data['vuelos']['v'+aux_inc].id_avion	= data[2];
			json_data['vuelos']['v'+aux_inc].fecha		= data[3];
			json_data['vuelos']['v'+aux_inc].cantidad	= data[4];
			aux_inc++;
			
			window.parent.vuelos_selec[indice].push($(this).val());
		});
	}
	
	if(is_ok){
		window.parent.ajax_get_total_vuelos(json_data['vuelos']);
		window.parent.$("p.close a").click();
	}else{alerta();}
}


function alerta(){
	create("withIcon", {
		title: 'Avizo !',
		text: 'Un vuelo seleccionado ya existe', 
		icon: base_url+'application/images/alertas/info.png' });
}