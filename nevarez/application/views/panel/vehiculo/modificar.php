<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title><?php echo $seo['titulo'];?></title>
	
<?php
	if(isset($this->carabiner)){
		$this->carabiner->display('css');
		$this->carabiner->display('js');
	}
?>
<script type="text/javascript" charset="UTF-8">
	var base_url = "<?php echo base_url();?>",
	opcmenu_active = '<?php echo isset($opcmenu_active)? $opcmenu_active: 0;?>';
</script>
</head>
<body>
<div>
	<div class="titulo ajus w100 am-c"><?= $seo['titulo']; ?></div>
	<form action="<?= base_url('panel/vehiculo/modificar');?>" method="post">
		<div class="frmsec-left w90 f-l">
			<p class="f-l w50">
				<label for="fnombre">*Nombre</label><br>
				<input type="text" name="fnombre" id="fnombre" value="<?= set_value('fnombre') ?>" size="40" autofocus>
			</p>
			<p class="f-l w50">
				<label for="fplacas">*Placas</label><br>
				<input type="text" name="fplacas" id="fplacas" value="<?= set_value('fplacas') ?>" size="40">
			</p>
			<p class="f-l w50">
				<label for="fcolor">Color</label><br>
				<input type="text" name="fcolor" id="fcolor" value="<?= set_value('fcolor') ?>" size="40">
			</p>
			<p class="f-l w50">
				<label for="fmodelo">Modelo</label><br>
				<input type="text" name="fmodelo" id="fmodelo" value="<?= set_value('fmodelo') ?>" size="40">
			</p>
			<p class="f-l w100">
				<label for="fano">Año</label><br>
				<input type=text name="fano" id="fano" value="<?= set_value('fano') ?>" size="30">
				
				<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all f-r">
			</p>
		</div>
	</form>
</div>


<!-- Bloque de alertas -->
<?php if(isset($frm_errors)){
	if($frm_errors['msg'] != ''){ 
?>
<div id="container" style="display:none">
	<div id="withIcon">
		<a class="ui-notify-close ui-notify-cross" href="#">x</a>
		<div style="float:left;margin:0 10px 0 0"><img src="#{icon}" alt="warning" width="64" height="64"></div>
		<h1>#{title}</h1>
		<p>#{text}</p>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript" charset="UTF-8">
$(function(){
	create("withIcon", {
		title: '<?php echo $frm_errors['title']; ?>', 
		text: '<?php echo $frm_errors['msg']; ?>', 
		icon: '<?php echo base_url('application/images/alertas/'.$frm_errors['ico'].'.png'); ?>' });
});
</script>
<?php }
}?>
<!-- Bloque de alertas -->

</body>
</html>