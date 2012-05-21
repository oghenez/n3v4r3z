<div id="contentAll" class="f-l">
	<form action="<?= base_url('panel/facturacion/modificar_serie_folio/?'.String::getVarsLink(array('msg')));?>" method="post" class="frm_addmod" enctype="multipart/form-data">
		<div class="frmsec-left w60 f-l">
			<p class="f-l w50">
				<label for="fserie">Serie</label><br>
				<input type="text" name="fserie" id="fserie" value="<?= (isset($serie_info[0]->serie)) ? $serie_info[0]->serie : set_value('fserie') ?>" size="30" autofocus maxlength="30">
			</p>
			<p class="f-l w50">
				<label for="fno_aprobacion">*No Aprobación</label><br>
				<input type="text" name="fno_aprobacion" id="fno_aprobacion" value="<?= (isset($serie_info[0]->no_aprobacion)) ? $serie_info[0]->no_aprobacion :set_value('fno_aprobacion') ?>" size="30">
			</p>
			<p class="f-l w50">
				<label for="ffolio_inicio">*Folio Inicio</label><br>
				<input type="text" name="ffolio_inicio" id="ffolio_inicio" value="<?= (isset($serie_info[0]->folio_inicio)) ? $serie_info[0]->folio_inicio :set_value('ffolio_inicio') ?>" size="30">
			</p>
			<p class="f-l w50">
				<label for="ffolio_fin">*Folio Fin</label><br>
				<input type="text" name="ffolio_fin" id="ffolio_fin" value="<?= (isset($serie_info[0]->folio_fin)) ? $serie_info[0]->folio_fin :set_value('ffolio_fin') ?>" size="30">
			</p>
			<p class="f-l w50">
				<label for="fano_aprobacion">*Año Aprobación</label><br>
				<input type="text" name="fano_aprobacion" id="fano_aprobacion" value="<?= (isset($serie_info[0]->ano_aprobacion)) ? $serie_info[0]->ano_aprobacion :set_value('fano_aprobacion') ?>" size="30" maxlength="4">
			</p>
			<p class="f-l w50">
				<label for="durl_img">*Imagen</label><br>
				<input type="file" name="durl_img" id="durl_img" value="<?= set_value('durl_img') ?>" size="30">
			</p>
			<p class="f-l w100">
				<label for="fleyenda">Leyenda</label><br>
				<input type="text" name="fleyenda" id="fleyenda" value="<?= (isset($serie_info[0]->leyenda)) ? $serie_info[0]->leyenda :set_value('fleyenda') ?>" size="72">
			</p>
			<p class="f-l w100">
				<label for="fleyenda1">Leyenda 1</label><br>
				<input type="text" name="fleyenda1" id="fleyenda1" value="<?= (isset($serie_info[0]->leyenda1)) ? $serie_info[0]->leyenda1 :set_value('fleyenda1') ?>" size="72">
			</p>
			<p class="f-l w100">
				<label for="fleyenda2">Leyenda 2</label><br>
				<input type="text" name="fleyenda2" id="fleyenda2" value="<?= (isset($serie_info[0]->leyenda2)) ? $serie_info[0]->leyenda2 :set_value('fleyenda2') ?>" size="72">
			</p>
			<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all f-r" style="margin-right:55px;">
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