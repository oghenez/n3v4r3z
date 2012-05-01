$(function(){
	$('#dfecha').datepicker($.datepicker.regional["es"]);
	
	$("#dcliente").autocomplete({
		source: base_url+'panel/clientes/ajax_get_clientes',
		minLength: 1,
		selectFirst: true,
		select: function( event, ui ) {
			$("#hcliente").val(ui.item.id);
			$("#dcliente_info").val(createInfoCliente(ui.item.item));
			$("#dcliente").css("background-color", "#B0FFB0");
			$('.addv').html('<a href="'+base_url+'panel/vuelos/vuelos_cliente/?id='+ui.item.id+'" id="btnAddVuelo" class="linksm f-r" style="margin: 10px 0 20px 0;" rel="superbox[iframe][700x500]"> <img src="'+base_url+'application/images/privilegios/add.png" width="16" height="16"> Agregar vuelos</a>');
			$.superbox();
		}
	});	
	
	$("input[type=text]").on("keydown", function(event){
		if(event.which == 8 || event == 46){
			var input = this.id;
			var hidde = 'h'+input.substr(1);
			$("#"+hidde).val("");
			$("#"+input+"_info").val("");
			$("#"+input).val("").css("background-color", "#FFD9B3");
			$('.addv').html('<a href="javascript:alerta();" id="btnAddVuelo" class="linksm f-r" style="margin: 10px 0 20px 0;"> <img src="'+base_url+'application/images/privilegios/add.png" width="16" height="16"> Agregar vuelos</a>');
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

function alerta(){
	create("withIcon", {
		title: 'Avizo !', 
		text: 'Seleccione un cliente', 
		icon: base_url+'application/images/alertas/info.png' });
}