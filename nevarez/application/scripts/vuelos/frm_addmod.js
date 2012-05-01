$(function(){
	$('#dfecha').datepicker($.datepicker.regional["es"]);
	
	$("#dcliente").autocomplete({
		source: base_url+'panel/clientes/ajax_get_clientes',
		minLength: 1,
		selectFirst: true,
		select: function( event, ui ) {
			$("#hcliente").val(ui.item.id);
			$("#dcliente_info").val(createInfoCliente(ui.item.item));
//			$("#dplazo_credito").val(ui.item.item.dias_credito);
			$("#dcliente").css("background-color", "#B0FFB0");
		}
	});
	
	$("#davion").autocomplete({
		source: base_url+'panel/aviones/ajax_get_aviones',
		minLength: 1,
		selectFirst: true,
		select: function( event, ui ) {
			$("#havion").val(ui.item.id);
			$("#davion_info").val(createInfoAvion(ui.item.item));
//			$("#dplazo_credito").val(ui.item.item.dias_credito);
			$("#davion").css("background-color", "#B0FFB0");
		}
	});
	
	$("#dpiloto").autocomplete({
		source: base_url+'panel/pilotos/ajax_get_pilotos',
		minLength: 1,
		selectFirst: true,
		select: function( event, ui ) {
			$("#hpiloto").val(ui.item.id);
			$("#dpiloto_info").val(createInfoPiloto(ui.item.item));
//			$("#dplazo_credito").val(ui.item.item.dias_credito);
			$("#dPiloto").css("background-color", "#B0FFB0");
		}
	});
	
	
	$("input[type=text]").on("keydown", function(event){
		if(event.which == 8 || event == 46){
			var input = this.id;
			var hidde = 'h'+input.substr(1);
			$("#"+hidde).val("");
			$("#"+input+"_info").val("");
			$("#"+input).val("").css("background-color", "#FFD9B3");
		}
	});
	
});

/**
 * Crea una cadena con la informacion del proveedor para mostrarla
 * cuando se seleccione
 * @param item
 * @returns {String}
 */
function createInfoCliente(item){
	var info = '';
	info += item.calle!=''? item.calle: '';
	info += item.no_exterior!=''? ' #'+item.no_exterior: '';
	info += item.no_interior!=''? '-'+item.no_interior: '';
	info += item.colonia!=''? ', '+item.colonia: '';
	info += "\n"+(item.localidad!=''? item.localidad: '');
	info += item.municipio!=''? ', '+item.municipio: '';
	info += item.estado!=''? ', '+item.estado: '';
	return info;
}

/**
 * Crea una cadena con la informacion del avion para mostrarla
 * cuando se seleccione
 * @param item
 * @returns {String}
 */
function createInfoAvion(item){
	var info = '';
	info += item.matricula!=''? 'Matricula:'+item.matricula: '';
	info += "\n"+(item.modelo!=''? 'Modelo:'+item.modelo: '');
	info += item.tipo!=''? ', Tipo:'+item.tipo: '';
	return info;
}

/**
 * Crea una cadena con la informacion del proveedor para mostrarla
 * cuando se seleccione
 * @param item
 * @returns {String}
 */
function createInfoPiloto(item){
	var info = '';
	info += item.licencia_avion!=''? "Licencia: "+item.licencia_avion: '';
	info += item.vencimiento_licencia_a!=''? ", Fecha Venc: "+item.vencimiento_licencia_a: '';
	info += "\n"+(item.calle!=''? ', '+item.calle: '');
	info += item.no_exterior!=''? ' #'+item.no_exterior: '';
	info += item.no_interior!=''? '-'+item.no_interior: '';
	info += item.colonia!=''? ', '+item.colonia: '';
	info += "\n"+(item.localidad!=''? item.localidad: '');
	info += item.municipio!=''? ', '+item.municipio: '';
	info += item.estado!=''? ', '+item.estado: '';
	return info;
}

