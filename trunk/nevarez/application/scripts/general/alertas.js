$(function(){
	//Alertas de form
	$container = $("#container").notify();
	
	//Ajustamos el tamaño del contenido
	var sw = parseInt(220*100/$("body").width());
	$("#menu_left, #bgmenuleft").css("width", sw + "%");
	$("#contentAll").css("width", (100 - sw - 1) + "%");
	
	//Se asigna eventos del menu izq y el treeview
	$("#menu_left").accordion({
		autoHeight: false,
		active: opcmenu_active
	});
	$("ul.treeview").treeview({
		collapsed: false,
		unique: true,
		persist: "location"
	});
	
	$("table td.tdsmenu").on('mouseenter', function(){
		$(".submenul", this).show();
	});
	$("table td.tdsmenu").on('mouseleave', function(){
		$(".submenul", this).hide();
	});
});


/*alertas de forms*/
function create( template, vars, opts ){
	return $container.notify("create", template, vars, opts);
}