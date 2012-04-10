
<div id="contentAll" class="f-l">
<div class="f-l w90 ">
	
<form action="<?= base_url('panel/aviones/')?>" method="GET" class="frmfiltros corner-all8 btn-gray">
	
	<label for="fplacas">Placas </label>
	<input type="text" name="fplacas" value="<?= set_value_get('fplacas')?>">

	<input type="submit" name="enviar" value="enviar" class="btn-blue corner-all">	
</form>	

<table class="tblListados corner-all8">
		<tr class="header btn-gray">
			<td>Nombre</td>
			<td>Placas</td>
			<td>Modelo</td>
			<td>Año</td>
			<td class="a-c">Opc</td>
		</tr>
<?php foreach($empleados['empleados'] as $emplea): ?>
		<tr>
			<td><?= $emplea->e_nombre;?></td>
			<td><?= $emplea->telefono; ?></td>
			<td><?= ucfirst($emplea->tipo_usuario); ?></td>
			<td><?= ucfirst($emplea->status); ?></td>
			<td class="tdsmenu a-c" style="width: 90px;">
				<img alt="opc" src="<?php echo base_url('application/images/privilegios/gear.png'); ?>" width="16" height="16">
				<div class="submenul">
					<p class="corner-bottom8">
						<?= $this->empleados_model->getLinkPrivSm('vehiculo/modificar/', $emplea->id_empleado, '', 'rel="superbox[iframe][650x285]"'); ?>
						<?=$this->empleados_model->getLinkPrivSm('vehiculo/desactivar/', $emplea->id_empleado, 
								"msb.confirm('Estas seguro de eliminar el vehículo?', this); return false;");?>
						<?php ?>
					</p>
				</div>
			</td>
		</tr>
<?php endforeach;?>
	</table>
</div>
</div>

<!-- <div class="f-l w20"></div> -->
<!-- 	<form action="" class="frmsec-right" method="POST"> -->
<!-- 		<label for="fpadre">Padre</label> -->
<!-- 		<input type="checkbox" name="fpadre" value="fpadre"> -->
<!-- 	</form> -->
<!-- </div> -->