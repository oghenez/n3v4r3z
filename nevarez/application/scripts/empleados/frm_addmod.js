$(function(){
	
	$("#frmsec-acordion").accordion({
		autoHeight: false
	});
	
	fecha_hoy = new Date();
	$("#dfecha_nacimiento").datepicker({
		 dateFormat: 'yy-mm-dd', //formato de la fecha - dd,mm,yy=dia,mes,año numericos  DD,MM=dia,mes en texto
		 //minDate: '-2Y', maxDate: '+1M +10D', //restringen a un rango el calendario - ej. +10D,-2M,+1Y,-3W(W=semanas) o alguna fecha
		 changeMonth: true, //permite modificar los meses (true o false)
		 changeYear: true, //permite modificar los años (true o false)
		 yearRange: (fecha_hoy.getFullYear()-70)+':'+fecha_hoy.getFullYear(),
		 numberOfMonths: 1 //muestra mas de un mes en el calendario, depende del numero
	 });
	$("#dfecha_entrada").datepicker({
		 dateFormat: 'yy-mm-dd', //formato de la fecha - dd,mm,yy=dia,mes,año numericos  DD,MM=dia,mes en texto
		 //minDate: '-2Y', maxDate: '+1M +10D', //restringen a un rango el calendario - ej. +10D,-2M,+1Y,-3W(W=semanas) o alguna fecha
		 changeMonth: true, //permite modificar los meses (true o false)
		 changeYear: true, //permite modificar los años (true o false)
		 yearRange: (fecha_hoy.getFullYear()-15)+':'+(fecha_hoy.getFullYear()+10),
		 numberOfMonths: 1 //muestra mas de un mes en el calendario, depende del numero
	 });
	$("#dfecha_salida").datepicker({
		 dateFormat: 'yy-mm-dd', //formato de la fecha - dd,mm,yy=dia,mes,año numericos  DD,MM=dia,mes en texto
		 //minDate: '-2Y', maxDate: '+1M +10D', //restringen a un rango el calendario - ej. +10D,-2M,+1Y,-3W(W=semanas) o alguna fecha
		 changeMonth: true, //permite modificar los meses (true o false)
		 changeYear: true, //permite modificar los años (true o false)
		 yearRange: (fecha_hoy.getFullYear()-8)+':'+(fecha_hoy.getFullYear()+1),
		 numberOfMonths: 1 //muestra mas de un mes en el calendario, depende del numero
	 });
	 
	//marcar y desmarcar los checks box
	$(".frmbox-r.priv .treeview input:checkbox").click(function (){
		var elemento_padre = $($(this).parent().get(0)).parent().get(0);
		var numero_hijos = $("ul", elemento_padre).length;
		
		if($("#dmod_privilegios").length > 0)
			$("#dmod_privilegios").val('si');
		
		if(numero_hijos > 0){
			$("input:checkbox", elemento_padre).attr("checked", ($(this).attr("checked")? true: false));
		}
	});
});


var contador_contacto = 0;
function addContacto(id_tbl, obj){
	if(contador_contacto == 0){
		$("#"+id_tbl).append(
			'<tr id="tr_addcontacto'+contador_contacto+'">'+
			'	<td><input type="text" name="dcnombre" value="" size="25"></td>'+
			'	<td><input type="text" name="dcdomicilio" value="" size="25"></td>'+
			'	<td><input type="text" name="dcmunicipio" value="" size="9"></td>'+
			'	<td><input type="text" name="dcestado" value="" size="9"></td>'+
			'	<td><input type="text" name="dctelefono" value="" size="9"></td>'+
			'	<td><input type="text" name="dccelular" value="" size="9"></td>'+
			'	<td><a href="'+obj.href+'" class="linksm" onclick="agregarContacto(\'tr_addcontacto'+contador_contacto+'\', this); return false;">'+
			'		<img src="'+base_url+'application/images/privilegios/add.png" width="10" height="10"> Agregar</a></td>'+
			'</tr>');
		$("#tr_addcontacto"+contador_contacto+" input[name=dcnombre]").focus();
	}
	contador_contacto++;
}

function agregarContacto(id_tr, obj){
	var data = "";
	$("#"+id_tr+" input").each(function(){
		data += this.name+"="+this.value+"&";
	});
	
	$.post(obj.href, data, function(resp){
		create("withIcon", {
			title: resp.msg.title, 
			text: resp.msg.msg, 
			icon: base_url+'application/images/alertas/'+resp.msg.ico+'.png' });
		$("#"+id_tr).remove();
	}, "json");
	contador_contacto = 0;
	/*$("#"+id_tbl+" tr.header:last").after(
		'<tr id="tr_addcontacto'+contador_contacto+'">'+
		'	<td><input type="text" name="dcnombre" value="" size="25"></td>'+
		'	<td><input type="text" name="dcdomicilio" value="" size="25"></td>'+
		'	<td><input type="text" name="dcmunicipio" value="" size="9"></td>'+
		'	<td><input type="text" name="dcestado" value="" size="9"></td>'+
		'	<td><input type="text" name="dctelefono" value="" size="9"></td>'+
		'	<td><input type="text" name="dccelular" value="" size="9"></td>'+
		'	<td><a href="'+obj.href+'" class="linksm" onclick="agregarContacto(\'tr_addcontacto'+contador_contacto+'\', this); return false;">'+
		'		<img src="'+base_url+'application/images/privilegios/add.png" width="10" height="10"> Agregar</a></td>'+
		'</tr>');*/
}

function eliminaContacto(obj){
	alert(obj.href);
}

