<?php
$secureItemParams = $this->Utilities->encodeParams($company['Empresa']['id']);
$salesperson_saved = Set::extract('/SalespersonShares/.', $company);
$territoriesTranslate = ['MX1'=>'MX A1', 'MX2'=>'MX C1'];
$regionCompany = array_key_exists($company['Empresa']['region'], $territoriesTranslate) ? $territoriesTranslate[$company['Empresa']['region']] : $company['Empresa']['region'];
?>
<?php if ($response['success']): ?>
    <form id="share_salesperson_form" class="fancy_form" action="<?php echo $this->Html->url(array('controller'=>'/','action'=>'setSalespersonToCompany',$secureItemParams['item_id'], $secureItemParams['digest'])); ?>">
        <div class=""><?php echo __('Select the salesperson with whom you want to share the company: %s, territory; %s', [$company['Empresa']['name'],$regionCompany]); ?></div>
        <div class="space"></div>
        <div class="fancy-content">
            <select multiple="multiple" id="salesperson_list" name="salespersons[]">
                <?php foreach($salesperson_list AS $salesperson):
                    //$x = array_search($salesperson['region'], array_column($territory_list, 'code'));
                    //var_dump($territory_list[$x]);
                    $region = array_key_exists($salesperson['region'], $territoriesTranslate) ? $territoriesTranslate[$salesperson['region']] : $salesperson['region'];
                ?>
                    <?php $selected = $company['Empresa']['region']==$salesperson['region'] ? 'selected="selected" disabled' : ''; ?>
                    <?php $selected_saved = array_search($salesperson['id'], array_column($salesperson_saved, 'user_sp_id'))===false ? '':'selected="selected"'; ?>
                    <option <?php echo $selected; ?> <?php echo $selected_saved; ?> value='<?php echo $salesperson['id'];?>'><?php echo $salesperson['name'];?> | <b><?php echo $region; ?></b></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dialog-buttons">
            <section>
                <button type="button" id="process_assoc_clients" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
            </section>
        </div>
    </form>

<?php
else: echo json_encode($response); endif;