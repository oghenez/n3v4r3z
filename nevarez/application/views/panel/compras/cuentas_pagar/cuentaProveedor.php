
<div id="contentAll" class="f-l">
	<form action="<?php echo base_url('panel/cuentas_pagar/cuenta_proveedor'); ?>" method="get" class="frmfiltros corner-all8 btn-gray">
		<label for="ffecha1">Del:</label> 
		<input type="text" name="ffecha1" id="ffecha1" value="<?php echo $this->input->get('ffecha1'); ?>" size="10">
		
		<label for="ffecha2">Al:</label> 
		<input type="text" name="ffecha2" id="ffecha2" value="<?php echo $this->input->get('ffecha2'); ?>" size="10">
		
		<label for="ftipo">Pagos:</label>
		<select name="ftipo" id="ftipo">
			<option value="pp" <?php echo set_select_get('ftipo', 'pp'); ?>>Pendientes por pagar</option>
			<option value="pv" <?php echo set_select_get('ftipo', 'pv'); ?>>Plazo vencido</option>
		</select>
		<input type="hidden" name="id_proveedor" value="<?php echo $this->input->get('id_proveedor'); ?>">
		
		<input type="submit" name="enviar" value="Enviar" class="btn-blue corner-all">
	</form>
	
	<div class="w30 m10-all f-l">
		<a href="<?php echo base_url('panel/cuentas_pagar/?'.String::getVarsLink()); ?>" class="linksm">
			<img src="<?php echo base_url('application/images/privilegios/atras.png'); ?>" width="16" height="16"> Atras</a>
		<a href="<?php echo base_url('panel/cuentas_pagar/cdp_pdf/?'.String::getVarsLink()); ?>" class="linksm" target="_blank">
			<img src="<?php echo base_url('application/images/privilegios/pdf.png'); ?>" width="20" height="20"> Imprimir</a>
		<a href="<?php echo base_url('panel/cuentas_pagar/cdp_xls/?'.String::getVarsLink()); ?>" class="linksm" target="_blank">
			<img src="<?php echo base_url('application/images/privilegios/xls.png'); ?>" width="20" height="20"> Excel</a>
	</div>
	
	<fieldset class="w60 f-r" style="color: #555; font-size: .9em;">
		<legend>Datos del proveedor</legend>
		<strong>Nombre:</strong> <?php echo $cuentasp['proveedor']->nombre; ?> <br>
		<strong>Dirección: </strong> 
				<?php
					$info = $cuentasp['proveedor']->calle!=''? $cuentasp['proveedor']->calle: '';
					$info .= $cuentasp['proveedor']->no_exterior!=''? ' #'.$cuentasp['proveedor']->no_exterior: '';
					$info .= $cuentasp['proveedor']->no_interior!=''? '-'.$cuentasp['proveedor']->no_interior: '';
					$info .= $cuentasp['proveedor']->colonia!=''? ', '.$cuentasp['proveedor']->colonia: '';
					$info .= "\n".($cuentasp['proveedor']->localidad!=''? $cuentasp['proveedor']->localidad: '');
					$info .= $cuentasp['proveedor']->municipio!=''? ', '.$cuentasp['proveedor']->municipio: '';
					$info .= $cuentasp['proveedor']->estado!=''? ', '.$cuentasp['proveedor']->estado: '';
					echo $info;
				?> <br>
		<strong>Teléfono: </strong> <?php echo $cuentasp['proveedor']->telefono; ?> 
		<strong>Email: </strong> <?php echo $cuentasp['proveedor']->email; ?>
	</fieldset>
	<div class="clear"></div>
	
	<table class="tblListados corner-all8">
		<tr class="header btn-gray">
			<td>Fecha</td>
			<td>Serie</td>
			<td>Folio</td>
			<td>Concepto</td>
			<td>Cargo</td>
			<td>Abono</td>
			<td>Saldo</td>
			<td>Estado</td>
			<td>F. Vencimiento</td>
			<td>D. Transcurridos</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td>Saldo anterior a <?php echo $cuentasp['fecha1']; ?></td>
			<td><?php echo String::formatoNumero(
					(isset($cuentasp['anterior']->total)? $cuentasp['anterior']->total: 0) ); ?></td>
			<td><?php echo String::formatoNumero(
					(isset($cuentasp['anterior']->abonos)? $cuentasp['anterior']->abonos: 0) ); ?></td>
			<td><?php echo String::formatoNumero(
					(isset($cuentasp['anterior']->saldo)? $cuentasp['anterior']->saldo: 0) ); ?></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
<?php
$total_cargo = 0;
$total_abono = 0;
$total_saldo = 0;
if(isset($cuentasp['anterior']->saldo)){ //se suma a los totales saldo anterior
	$total_cargo += $cuentasp['anterior']->total;
	$total_abono += $cuentasp['anterior']->abonos;
	$total_saldo += $cuentasp['anterior']->saldo;
}
foreach($cuentasp['cuentas'] as $cuenta){
	$ver = true;
	//verifica q no sea negativo o exponencial el saldo
	$cuenta->saldo = floatval(String::float($cuenta->saldo));
	if($cuenta->saldo == 0){
		$cuenta->estado = 'Pagada';
		$cuenta->fecha_vencimiento = $cuenta->dias_transc = '';
		if($this->input->get('ftipo')=='pv')
			$ver = false;
	}
	
	if($ver){
		$total_cargo += $cuenta->cargo;
		$total_abono += $cuenta->abono;
		$total_saldo += $cuenta->saldo;
?>
		<tr>
			<td><?php echo $cuenta->fecha; ?></td>
			<td><?php echo $cuenta->serie; ?></td>
			<td><a href="<?php echo base_url('panel/cuentas_pagar/detalle').
					'?id_compra='.$cuenta->id_compra.'&id_proveedor='.$cuentasp['proveedor']->id_proveedor.'&'.
					String::getVarsLink(array('id_compra', 'id_proveedor')); ?>" class="linksm lkzoom"><?php echo $cuenta->folio; ?></a></td>
			<td><?php echo $cuenta->concepto; ?></td>
			<td><?php echo String::formatoNumero($cuenta->cargo); ?></td>
			<td><?php echo String::formatoNumero($cuenta->abono); ?></td>
			<td><?php echo String::formatoNumero($cuenta->saldo); ?></td>
			<td><?php echo $cuenta->estado; ?></td>
			<td><?php echo $cuenta->fecha_vencimiento; ?></td>
			<td><?php echo $cuenta->dias_transc; ?></td>
		</tr>
<?php }
} ?>
		<tr style="background-color:#ccc;font-weight: bold;">
			<td colspan="4" class="a-r">Totales:</td>
			<td><?php echo String::formatoNumero($total_cargo); ?></td>
			<td><?php echo String::formatoNumero($total_abono); ?></td>
			<td><?php echo String::formatoNumero($total_saldo); ?></td>
			<td colspan="3"></td>
		</tr>
	</table>
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
