
<div id="contentAll" class="f-l">
	<form action="<?php echo base_url('panel/compras/agregar'); ?>" method="post" class="frm_addmod">
		<div class="frmsec-left w80 f-l b-r">
			<p class="w50 f-l">
				<label for="dserie">Serie:</label> <span class="ej-info">Ej. A, NV, F, etc</span> <br>
				<input type="text" name="dserie" id="dserie" value="<?php echo set_value('dserie'); ?>" size="15" maxlength="4" autofocus>
			</p>
			<p class="w50 f-l">
				<label for="dfolio">*Folio:</label> <span class="ej-info">Ej. 1, 8, 12, 103, etc</span> <br>
				<input type="number" name="dfolio" id="dfolio" value="<?php echo set_value('dfolio'); ?>" class="vpos-int" size="15" min="0" max="2147483647">
			</p>
			<div class="clear"></div>
			
			<p class="w80">
				<label for="dconcepto">Concepto:</label> <br>
				<textarea name="dconcepto" id="dconcepto" rows="2" cols="70"><?php echo set_value('dconcepto'); ?></textarea>
			</p>
			<div class="clear"></div>
			
			<p class="w100">
				<label for="dproveedor">*Proveedor:</label> <br>
				<input type="text" name="dproveedor" id="dproveedor" class="f-l" 
					value="<?php echo set_value('dproveedor'); ?>" size="35">
				<input type="hidden" name="did_proveedor" id="did_proveedor" value="<?php echo set_value('did_proveedor'); ?>">
				
				<textarea name="dproveedor_info" id="dproveedor_info" class="m10-l" rows="3" cols="55" readonly><?php echo set_value('dproveedor_info'); ?></textarea>
			</p>
			<div class="clear"></div>
			
			
			<fieldset class="w100">
				<legend>Productos</legend>
				<input type="hidden" name="a_id_producto" id="a_id_producto" value="">
				<p class="w15 f-l">
					<label for="a_codigo">Código:</label> <br>
					<input type="text" name="a_codigo" id="a_codigo" size="10">
				</p>
				<p class="w40 f-l">
					<label for="a_nombre">Nombre:</label> <br>
					<input type="text" name="a_nombre" id="a_nombre" size="37">
				</p>
				<p class="f-l">
					<label for="a_cantidad">Cantidad:</label> <br>
					<input type="text" name="a_cantidad" value="1" id="a_cantidad" class="vpositive" size="8">
				</p>
				<p class="f-l">
					<label for="a_pu">P.U.:</label> <br>
					<input type="text" name="a_pu" value="0" id="a_pu" class="vpositive" size="8">
				</p>
				<p class="f-l">
					<label for="a_iva">IVA:</label> <br>
					<select name="a_iva" id="a_iva">
						<option value="0.16">16%</option>
						<option value="0.1">10%</option>
						<option value="0">0%</option>
					</select>
				</p>
				<a href="javascript:void(0);" id="btnAddProducto" class="linksm f-l" style="margin: 30px 0 0 20px;">
					<img src="<?php echo base_url('application/images/privilegios/add.png'); ?>" width="16" height="16"> Agregar</a>
					
				<div class="clear"></div>
			</fieldset>
			<table class="tblListados corner-all8" id="tbl_productos">
				<tr class="header btn-gray">
					<td>Cantidad</td>
					<td>Código</td>
					<td>Nombre</td>
					<td>P. Unitario</td>
					<td>Importe</td>
					<td>Opc</td>
				</tr>
				
		<?php
		if(isset($_POST['dpid_producto'])){
			foreach($_POST['dpid_producto'] as $key => $itm){
				echo '<tr id="trp-'.str_replace('.', '_', $itm).'">
						<td>
							<input type="hidden" name="dpid_producto[]" value="'.$itm.'">
							<input type="hidden" name="dpcantidad[]" value="'.$_POST['dpcantidad'][$key].'">
							<input type="hidden" name="dpprecio_unitario[]" value="'.$_POST['dpprecio_unitario'][$key].'">
							<input type="hidden" name="dpimporte[]" value="'.$_POST['dpimporte'][$key].'" class="dpimporte">
							<input type="hidden" name="dptaza_iva[]" value="'.$_POST['dptaza_iva'][$key].'">
							<input type="hidden" name="dpimporte_iva[]" value="'.$_POST['dpimporte_iva'][$key].'" class="dpimporte_iva">
							
							<input type="hidden" name="dpcodigo[]" value="'.$_POST['dpcodigo'][$key].'">
							<input type="hidden" name="dpnombre[]" value="'.$_POST['dpnombre'][$key].'">
							'.$_POST['dpcantidad'][$key].'</td>
						<td>'.$_POST['dpcodigo'][$key].'</td>
						<td>'.$_POST['dpnombre'][$key].'</td>
						<td>'.String::formatoNumero($_POST['dpprecio_unitario'][$key]).'</td>
						<td>'.String::formatoNumero($_POST['dpimporte'][$key]).'</td>
						<td class="tdsmenu a-c" style="width: 90px;">
							<a href="javascript:void(0);" class="linksm" 
								onclick="quitarProducto(\''.$itm.'\');return false;">
								<img src="'.base_url().'application/images/privilegios/delete.png" width="10" height="10"> Quitar</a>
						</td>
					</tr>';
			}
		} 
		?>
			</table>
			
			<table class="tblListados corner-all8" style="text-align:center;">
				<tr>
					<td rowspan="3">
						<label for="cp" class="lbl-gris">Importe con letra</label>
						<textarea name="dttotal_letra" id="dttotal_letra" rows="3" readonly="readonly" style="width:98%;"><?php echo set_value('dttotal_letra'); ?></textarea>
						<input type="hidden" id="dtsubtotal" name="dtsubtotal" value="<?php echo set_value('dtsubtotal', 0); ?>" />
						<input type="hidden" id="dtiva" name="dtiva" value="<?php echo set_value('dtiva', 0); ?>" />
						<input type="hidden" id="dttotal" name="dttotal" value="<?php echo set_value('dttotal', 0); ?>">
					</td>
					<td style="text-align:right;">SubTotal</td>
					<td id="ta_subtotal" class="w20 a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dtsubtotal', 0)); ?></td>
				</tr>
				<tr>
					<td style="text-align:right;">IVA</td>
					<td id="ta_iva" class="a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dtiva', 0)); ?></td>
				</tr>
				<tr>
					<td style="text-align:right;">Total</td>
					<td id="ta_total" class="a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dttotal', 0)); ?></td>
				</tr>
			</table>
		</div>
		
		<div class="frmsec-right w20 f-l">
			<div class="frmbox-r p5-tb corner-right8">
				<label for="dfecha">*Fecha:</label> <br>
				<input type="date" name="dfecha" id="dfecha" value="<?php echo set_value('dfecha', $fecha); ?>"> <br><br>
				
				<label for="dcondicion_pago">*Condición de pago:</label> <br>
				<select name="dcondicion_pago" id="dcondicion_pago">
					<option value="co" <?php echo set_select('dcondicion_pago', 'co'); ?>>Contado</option>
					<option value="cr" <?php echo set_select('dcondicion_pago', 'cr'); ?>>Credito</option>
				</select>
				
				<p id="vplazo_credito" class="<?php echo ($this->input->post('dcondicion_pago')=='cr'? '': 'no-show'); ?>">
					<label for="dplazo_credito">*Plazo de crédito:</label> <br>
					<input type="number" name="dplazo_credito" id="dplazo_credito" class="vpositive"
						value="<?php echo set_value('dplazo_credito', $plazo_credito); ?>" size="15" min="0" max="120"> días
				</p>
			</div>
			
			<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all">
		</div>
	</form>
</div>


<!-- Bloque de alertas -->
<div id="container" style="display:none">
	<div id="withIcon">
		<a class="ui-notify-close ui-notify-cross" href="#">x</a>
		<div style="float:left;margin:0 10px 0 0"><img src="#{icon}" alt="warning" width="64" height="64"></div>
		<h1>#{title}</h1>
		<p>#{text}</p>
		<div class="clear"></div>
	</div>
</div>
<?php if(isset($frm_errors)){
	if($frm_errors['msg'] != ''){ 
?>
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
