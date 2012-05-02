
var subtotal = 0;
var iva = 0;
var total = 0;

var vuelos_selec = {}; // almacena los vuelos que han sido agregados
var vuelos_data = {}; //almacena la informacion de los vuelos que sera enviada por POST
var indice = 0; // indice para controlar los vuelos q han sido agregados

var post = {}; // Contiene todos los valores del ticket q se pasaran por POST


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
			$('.addv').html('<a href="javascript:void(0);" id="btnAddVuelo" class="linksm f-r" style="margin: 10px 0 20px 0;" onclick="alerta(\'Seleccione un Cliente !\');"> <img src="'+base_url+'application/images/privilegios/add.png" width="16" height="16"> Agregar vuelos</a>');
		}
	});
	
	$('#submit').on('click',function(){
		ajax_submit_form();
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

function ajax_get_total_vuelos(data){
	loader.create();
	$.post(base_url+'panel/tickets/ajax_get_total_vuelos/', data, function(resp){

		if(resp.vuelos){
			var opc_elimi = '';
			
			vuelos_data[indice] = {};
			for(var i in resp.vuelos){
				vuelos_data[indice]['vuelo'+i] = {};
				vuelos_data[indice]['vuelo'+i].id_vuelo = resp.vuelos[i].id_vuelo;
				vuelos_data[indice]['vuelo'+i].cantidad = resp.tabla.cantidad;
				vuelos_data[indice]['vuelo'+i].taza_iva = parseFloat(0.16,2);
				vuelos_data[indice]['vuelo'+i].precio_unitario = resp.tabla.p_uni;
				vuelos_data[indice]['vuelo'+i].importe = parseFloat(resp.tabla.importe,2);
				vuelos_data[indice]['vuelo'+i].importe_iva = parseFloat(resp.tabla.importe*0.16, 2);
				vuelos_data[indice]['vuelo'+i].total = parseFloat(resp.tabla.importe,2) +  parseFloat(resp.tabla.importe*0.16, 2);
			}
			
			subtotal	+= parseFloat(resp.tabla.importe, 2);
			iva			= parseFloat(subtotal*0.16, 2);
			total		= parseFloat(subtotal+iva, 2);
			vals= '{indice:'+indice+',importe:'+parseFloat(resp.tabla.importe, 2)+'}';
			
			$('#hdias_credito').val(resp.tabla.dias_credito);
			
			
			opc_elimi = '<a href="javascript:void(0);" class="linksm"'+ 
				'onclick="msb.confirm(\'Estas seguro de eliminar el vuelo?\', '+vals+', eliminaVuelos); return false;">'+
				'<img src="'+base_url+'application/images/privilegios/delete.png" width="10" height="10">Eliminar</a>';
			
			//Agrego el tr con la informacion del contacto agregado
			$("#tbl_vuelos tr.header:last").after(
			'<tr id="e'+indice+'">'+
			'	<td>'+resp.tabla.cantidad+'</td>'+
			'	<td>'+resp.tabla.codigo+'</td>'+
			'	<td>'+resp.tabla.descripcion+'</td>'+
			'	<td>$'+resp.tabla.p_uni+'</td>'+
			'	<td>$'+resp.tabla.importe+'</td>'+
			'	<td class="tdsmenu a-c" style="width: 90px;">'+
			'		<img alt="opc" src="'+base_url+'application/images/privilegios/gear.png" width="16" height="16">'+
			'		<div class="submenul">'+
			'			<p class="corner-bottom8">'+
								opc_elimi+
			'			</p>'+
			'		</div>'+
			'	</td>'+
			'</tr>');
			
			updateTablaPrecios();
			
			indice++;
		}
	}, "json").complete(function(){ 
    	loader.close();
    });
}

function ajax_submit_form(){

	post.tcliente	= $('#hcliente').val();
	post.tfolio		= $('#dfolio').val();
	post.tfecha		= $('#dfecha').val();
	post.tipo_pago	= $('#dtipo_pago').val();
	post.tdias_credito = $('#hdias_credito').val();
	post.subtotal		= parseFloat(subtotal,2);
	post.iva			= parseFloat(iva,2);
	post.total			= parseFloat(total,2);
	
	var count=0;
	for(var i in vuelos_selec)
		for(var x in vuelos_selec[i])
			count++;
	if(count>0)
		post.vuelos	= count;
	
	cont=1;
	for(var i in vuelos_data){
		for(var x in vuelos_data[i]){
			post['pvuelo'+cont]	= {};
			post['pvuelo'+cont]	= vuelos_data[i][x];
			cont++;
		}
	}
	
	loader.create();
	$.post(base_url+'panel/tickets/ajax_submit_form/', post, function(resp){
		
		create("withIcon", {
			title: resp.msg.title, 
			text: resp.msg.msg, 
			icon: base_url+'application/images/alertas/'+resp.msg.ico+'.png' });
		if(resp.msg.ico == 'ok'){
			//si es OK se elimina el row form
			$('#tbl_vuelos tr').not('.header').remove();
		}
		if(resp[0]){
			$('#dfolio').val(resp.folio);
			limpia_campos();
			updateTablaPrecios();
		}
	}, "json").complete(function(){ 
    	loader.close();
    });
}

function limpia_campos(){
	$('#dcliente').val('').css('background','#FFF');
	$('#dcliente_info').val('');
	$('#hcliente').val('');
	$('#hdias_credito').val('');
	$('#dfecha').val('');
	
	subtotal = 0;
	iva = 0;
	total = 0;
	vuelos_selec = {};
	vuelos_data = {};
	post = {};
	indice = 0;
	
}


function eliminaVuelos(vals){
	delete vuelos_selec[vals.indice];
	delete vuelos_data[vals.indice];
	$('#e'+vals.indice).remove();
	
	subtotal -= parseFloat(vals.importe,2);
	iva			= parseFloat(subtotal*0.16, 2);
	total		= parseFloat(subtotal+iva, 2);
	
	updateTablaPrecios();
	
//	alert(vuelos_data.toSource());
}

function updateTablaPrecios(){
	$('#ta_subtotal').text(util.darFormatoNum(subtotal));
	$('#ta_iva').text(util.darFormatoNum(iva));
	$('#ta_total').text(util.darFormatoNum(total));
}


function alerta(msg){
	create("withIcon", {
		title: 'Avizo !',
		text: msg, 
		icon: base_url+'application/images/alertas/info.png' });
}