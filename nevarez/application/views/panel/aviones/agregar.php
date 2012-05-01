<div id="contentAll" class="f-l">
	<form action="<?= base_url('panel/aviones/agregar');?>" method="post" class="frm_addmod">
		<div class="frmsec-left w60 f-l">
			<p class="f-l w50">
				<label for="fmatricula">*Matricula</label><br>
				<input type="text" name="fmatricula" id="fnombre" value="<?= set_value('fmatricula') ?>" size="30" autofocus maxlength="20">
			</p>
			<p class="f-l w50">
				<label for="fmodelo">Modelo</label><br>
				<input type="text" name="fmodelo" id="fmodelo" value="<?= set_value('fmodelo') ?>" size="30" maxlength="10">
			</p>
			<p class="f-l w100">
				<label for="ftipo">Tipo</label><br>
				<input type="text" name="ftipo" id="ftipo" value="<?= set_value('ftipo') ?>" size="30" maxlength="10">
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