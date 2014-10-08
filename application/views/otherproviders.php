<?php $this->load->view('includes/header.php');?>
<?php if(isset($this->session->userdata['username'])){ ?>
	<section id="main">
<?php $this->load->view('includes/nav.php');?>
	</section>
	<form method="post" class="form-horizontal addusuario" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/providers/other_providers">
		<fieldset>
		
		<legend><a href="#" id="add_prov"><?= lang('other_provider.add_provider');?></a>&nbsp;&nbsp;&nbsp;<a href="#" id="close_prov"><?= lang('general.ocultar');?></a></legend>
		<div id="capa-sup">
			<div class="control-group">
			  <label class="control-label" for="textinput"><?= lang('adduser.name');?></label>
			  <div class="controls">
			    <input id="textinput" name="name" placeholder="<?= lang('adduser.name');?>" class="input-xlarge" required="" type="text">
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic"><?= lang('adduser.country');?></label>
			  <div class="controls">
			    <select id="selectbasic" name="country" class="input-xlarge">
			    	<?php
			    	foreach ($countries->result() as $country){
			    		echo '<option value="'.$country->id.'">'.$country->name.'</option>'."\n";
			    	}
			    	?>
			    </select>
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic"><?= lang('other_provider.fichero');?></label>
			  <div class="controls">
			  	<select id="selectbasic" name="prov_files" class="input-xlarge">
			  		<?php
			  		foreach ($prov_files->result() as $prov){  
			  			echo '<option value="'.$prov->id.'">'.$prov->name.' -> '.$prov->country_name.'</option>'."\n";
			  		}
			  		?>
			  	</select>&nbsp;&nbsp;&nbsp;
			  	<small><a href="<?= base_url().$this->session->userdata['lang'];?>/administration/load/list_providers/add"><?= lang('other_provider.add_file_provider');?></a></small>
			  	</div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic">MCH</label>
			  <div class="controls">
			  	<select id="selectbasic" name="mch_tables" class="input-xlarge">
			  		<?php
			  		foreach ($list_mch as $mch){  
			  			echo '<option value="'.$mch.'"'.($mch == 'NCOM_PRD_PRO_BU' ? ' selected="selected"' : '').'>'.$mch.'</option>'."\n";
			  		}
			  		?>
			  	</select>
			  	</div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="textinput">Query</label>
			  <div class="controls">
			    <input id="textinput" name="query" placeholder="IDEPROD = 8" class="input-xlarge" type="text">
			  </div>
			</div>


			<div class="control-group">
			  <label class="control-label" for="selectbasic">Delay</label>
			  <div class="controls">
			  	<input id="textinput" name="delay" min="0" pattern="\d*" placeholder="Delay" class="input-xlarge" required="" type="text">
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic">Force stock</label>
			  <div class="controls">
			  	<input type="checkbox" name="active_fstock" rel="new" class="active_fstock" value="1">
			  </div>
			</div>

			<div class="control-group" id="stock-forced-new" style="display:none;">
			  <label class="control-label" for="selectbasic">Stock</label>
			  <div class="controls">
			  	<input id="textinput" name="stock" min="0" pattern="\d*" placeholder="Stock" class="input-xlarge" type="text">
			  </div>
			</div>

			<div class="control-group" id="correction-stock-new">
			  <label class="control-label" for="selectbasic">Correction Stock</label>
			  <div class="controls">
			  	<input id="textinput" name="correction_stock" min="0" pattern="\d*" placeholder="Correction Stock" class="input-xlarge" type="text">
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic"><?= lang('other_provider.campos');?></label>
			  <div class="controls active-field" id="campos">
			  		<?php
			  		$i = 0;
			  		foreach ($fields->result() as $field){  
			  			echo '<div class="drag-field" id="field_'.$field->id.'">'.$field->value.'</div>';
			  			$i++;
			  		}
			  		?>
			  		<small><a href="<?= base_url().$this->session->userdata['lang'];?>/administration/load/custom_fields/add"><?= lang('other_provider.add_field');?></a></small>
			  	</div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic"><?= lang('other_provider.posicion');?></label>
			  <div class="controls">
			  	<?php for ($x=1; $x<=$i; $x++){ ?>
			    <div class="cuadro" id="new" rel="<?= $x;?>" title="position_<?= $x;?>"></div>
			    <input type="hidden" id="position_<?= $x;?>" name="position_<?= $x;?>" value="">
			    <?php } ?>
			    <input type="hidden" name="link_position" id="link_position_new" value="">
			    <input type="hidden" name="total_reg" value="<?= $i;?>">
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="selectbasic"><?= lang('other_provider.activo');?></label>
			  <div class="controls">
			  	<input type="checkbox" name="activo" class="input-xlarge" value="1">
			  </div>
			</div>

			<div class="control-group">
			  <label class="control-label" for="singlebutton"></label>
			  <div class="controls">
			    <button id="singlebutton" name="singlebutton" class="btn"><?= lang('other_provider.add_provider');?></button>&nbsp;&nbsp;&nbsp;
			    <a id="cancel-prov" href="#" class="btn"><?= lang('adduser.cancel');?></a> 
			  </div>
			</div>
		</div>
		<?php if (isset($estado)){ ?>
			<div class="err-prov" style="<?= (isset($_POST['edit_prov_id']) && isset($estado) ? '' : 'display:none;');?>">
				<ul>
				<?php
				foreach ($estado as $err){
					echo '<li>'.$err.'</li>'."\n";
				}
				?>
				</ul>
			</div>
		<?php } ?>
		</fieldset>
		</form>
		<form class="form-horizontal addusuario">
			<fieldset>
		<div class="panel-other-prov" style="<?= (isset($_POST['edit_prov_id']) && isset($estado) ? 'margin-top:95px;' : '');?>">
		<?php if (isset($other_prov) && $other_prov != false){
			foreach($other_prov->result() as $prov){
				$prov_id = $prov->id;
				echo '<div class="row-other-prov" rel="'.$prov_id.'">'.$prov->sap_name.' '.$prov->country_name.'<div style="float:right;"><a href="javascript:details('.$prov_id.')">'.lang('other_provider.editar').'</a> - <a href="javascript:delete_provider('.$prov->id.')">'.lang('other_provider.borrar').'</a></div></div>';?>
				<div class="details" id="detalles_<?= $prov->id;?>" style="display:none;">
					<input type="hidden" id="prov_id_<?= $prov_id;?>" value="<?= $prov_id;?>">
					<div class="control-group">
					  <label class="control-label" for="textinput"><?= lang('adduser.name');?></label>
					  <div class="controls">
					    <input id="textinput" name="name_edit" placeholder="<?= lang('adduser.name');?>" class="input-xlarge prov_name_<?= $prov_id;?>" required="" type="text" value="<?= $prov->sap_name;?>">
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic"><?= lang('adduser.country');?><br/></label>
					  <div class="controls">
					    <select id="selectbasic" name="country_edit" class="input-xlarge prov_country_<?= $prov_id;?>">
					    	<?php
					    	foreach ($countries->result() as $country){
					    		echo '<option value="'.$country->id.'"'.($prov->countries_id == $country->id ? ' selected' : '').'>'.$country->name.'</option>'."\n";
					    	}
					    	?>
					    </select>
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic"><?= lang('other_provider.fichero');?></label>
					  <div class="controls">
					  	<select id="selectbasic" name="prov_files_edit" class="input-xlarge prov_files_<?= $prov_id;?>">
					  		<?php
					  		foreach ($prov_files->result() as $prov_file){  
					  			echo '<option value="'.$prov_file->id.'"'.($prov->id_files_providers == $prov_file->id ? ' selected' : '').'>'.$prov_file->name.' -> '.$prov_file->country_name.'</option>'."\n";
					  		}
					  		?>
					  	</select>&nbsp;&nbsp;&nbsp;
					  	<small><a href="<?= base_url().$this->session->userdata['lang'];?>/administration/load/list_providers/add"><?= lang('other_provider.add_file_provider');?></a></small>
					  	</div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic">MCH</label>
					  <div class="controls">
					  	<select id="selectbasic" name="mch_tables" class="input-xlarge prov_mch_<?= $prov_id;?>">
					  		<?php
					  		foreach ($list_mch as $mch){  
					  			echo '<option value="'.$mch.'"'.($mch == $prov->table_db ? ' selected="selected"' : '').'>'.$mch.'</option>'."\n";
					  		}
					  		?>
					  	</select>
					  	</div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="textinput">Query</label>
					  <div class="controls">
					    <input id="textinput" name="query" placeholder="IDEPROD = 8" class="input-xlarge prov_query_<?= $prov_id;?>" type="text" value="<?= $prov->query;?>">
					  </div>
					</div>


					<div class="control-group">
					  <label class="control-label" for="selectbasic">Delay</label>
					  <div class="controls">
					  	<input id="textinput" name="delay" min="0" pattern="\d*" placeholder="Delay" class="input-xlarge prov_delay_<?= $prov_id;?>" required="" type="text" value="<?= $prov->delay;?>">
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic">Force stock</label>
					  <div class="controls">
					  	<input type="checkbox" name="active_fstock" rel="<?= $prov_id;?>" class="active_fstock prov_afstock_<?= $prov_id;?>" value="1"<?= ($prov->force_stock == 1 ? ' checked' : '');?>>
					  </div>
					</div>

					<div class="control-group" id="stock-forced-<?= $prov_id;?>" style="<?= ($prov->force_stock != 1 ? 'display:none;' : '');?>">
					  <label class="control-label" for="selectbasic">Stock</label>
					  <div class="controls">
					  	<input id="textinput" name="stock" min="0" pattern="\d*" placeholder="Stock" class="input-xlarge prov_stock_<?= $prov_id;?>" type="text" value="<?= $prov->stock_forced;?>">
					  </div>
					</div>

					<div class="control-group" id="correction-stock-<?= $prov_id;?>">
					  <label class="control-label" for="selectbasic">Correction Stock</label>
					  <div class="controls">
					  	<input id="textinput" name="correction_stock" min="0" pattern="\d*" placeholder="Correction Stock" class="input-xlarge prov_correction_stock_<?= $prov_id;?>" type="text" value="<?= $prov->correctionstock;?>">
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic"><?= lang('other_provider.campos');?></label>
					  <div class="controls active-field" id="campos">
					  		<?php
					  		$i = 0;
					  		foreach ($fields->result() as $field){
					  			$is_present = false;  
					  			if (is_array($fields_saved[$prov_id])) $is_present = array_key_exists($field->id, $fields_saved[$prov_id]);
					  			if (!$is_present){
						  			echo '<div class="drag-field" id="field_'.$field->id.'">'.$field->value.'</div>';
					  			}
					  			$i++;
					  		}
					  		?>
					  		<small><a href="<?= base_url().$this->session->userdata['lang'];?>/administration/load/custom_fields/add"><?= lang('other_provider.add_field');?></a></small>
					  	</div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic"><?= lang('other_provider.posicion');?></label>
					  <div class="controls">
					  	<?php for ($x=1; $x<=$i; $x++){ ?>
					    <div class="cuadro<?= ($x==$prov->key_fields ? ' active-link' : '');?> line_<?= $prov_id;?>" id="<?= $prov_id;?>" rel="<?= $x;?>" title="position_<?= $x;?>">
					    	<?php if (isset($positions_saved[$prov_id][$x]['name'])){ ?>
					    		<div class="drag-field edited-field" id="field_<?= $positions_saved[$prov_id][$x]['id'];?>">
					    			<?= $positions_saved[$prov_id][$x]['name'];?>
					    		</div>
					    	<?php } ?> </div>
					    <input type="hidden" id="prov_position_<?= $x;?>_id_<?= $prov_id;?>" value="<?= (isset($positions_saved[$prov_id][$x]['id']) ? 'field_'.$positions_saved[$prov_id][$x]['id'] : '');?>">
					    <?php } ?>
					    <input type="hidden" name="link_position" id="link_position_<?= $prov_id;?>" value="">
					    <input type="hidden" id="total_reg" name="total_reg" value="<?= $i;?>">
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="selectbasic"><?= lang('other_provider.activo');?></label>
					  <div class="controls">
					  	<input type="checkbox" id="prov_activo_<?= $prov_id;?>" name="activo_edit" class="input-xlarge" value="1"<?= ($prov->active == 1 ? ' checked' : '');?>>
					  </div>
					</div>

					<div class="control-group">
					  <label class="control-label" for="singlebutton"></label>
					  <div class="controls">
					    <button id="singlebutton" name="singlebutton" class="btn" onclick="javascript:save_other_provider();"><?= lang('other_provider.guardar');?></button>&nbsp;&nbsp;&nbsp;
					    <a href="javascript:close_details(<?= $prov_id;?>);" class="btn"><?= lang('adduser.cancel');?></a> 
					  </div>
					</div>
				</div>
			<?php }
		}else{
			echo lang('other_provider.no_hay');
		}?>	
		</div>
		</fieldset>
	</form>
	<form method="POST" id="borrar" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/providers/delete_other_provider">
		<input type="hidden" name="edit_prov_id" id="borrar_id" value="">
		<input type="hidden" id="confirmar" value="<?= lang('other_provider.seguro');?>">
	</form>

	<form method="POST" id="editar" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/providers/other_providers">
		<input type="hidden" id="edit_prov_id" name="edit_prov_id" value="">
		<input type="hidden" id="edit_name" name="edit_name" value="">
		<input type="hidden" id="edit_country" name="edit_country" value="">
		<input type="hidden" id="edit_prov_files" name="edit_prov_files" value="">
		<input type="hidden" id="edit_activo" name="edit_activo" value="">
		<input type="hidden" name="edit_total_reg" value="<?= $i;?>">
		<input type="hidden" id="edit_link_position" name="edit_link_position" value="">
		<input type="hidden" id="edit_query" name="edit_query" value="">
		<input type="hidden" id="edit_delay" name="edit_delay" value="">
		<input type="hidden" id="edit_mch_tables" name="edit_mch_tables" value="">
		<input type="hidden" id="edit_active_fstock" name="edit_active_fstock" value="">
		<input type="hidden" id="edit_stock" name="edit_stock" value="">
		<input type="hidden" id="edit_correction_stock" name="edit_correction_stock" value="">
		<?php for ($x=1; $x<=$i; $x++){ ?>
		<input type="hidden" id="edit_position_<?= $x;?>" name="edit_position_<?= $x;?>" value="">
		<?php } ?>
	</form>
	
<?php }else{
	$this->load->view('login');
} ?>

<?php $this->load->view('includes/footer.php');?>