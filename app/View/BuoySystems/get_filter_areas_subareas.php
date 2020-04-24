<?php
$secureItemParams = $this->Utilities->encodeParams($conveyor['id']);
$clearTagsUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'clearTags', $secureItemParams['item_id'], $secureItemParams['digest']));
?>
<div style="position:relative;">
    <select id="area_select" class="select-sidebar" data-placeholder="<?php echo __('Add area', true); ?>">
        <option></option>
        <option value="new"><?php echo __('New', true); ?></option>
        <?php foreach($areas AS $area):
            $selected = $conveyor['area'] == $area['id'] ? 'selected' : '';
            ?>
            <option <?php echo $selected; ?> value="<?php echo $area['id']?>"><?php echo $area['name']?></option>
        <?php endforeach; ?>
    </select>

    <a href="#" class="clear-tag close-stick <?php if($conveyor['area']==0): ?>hidden<?php endif; ?>" data-url="<?php echo $clearTagsUrl; ?>" data-type="area_select"></a>

</div>

<div style="position:relative;">
    <select id="subarea_select" class="select-sidebar" data-placeholder="<?php echo __('Add sub area', true); ?>">
        <option></option>
        <option value="new"><?php echo __('New', true); ?></option>
        <?php foreach($subareas AS $subarea):
            $selected = $conveyor['subarea'] == $subarea['id'] ? 'selected' : '';
            ?>
            <option <?php echo $selected; ?> value="<?php echo $subarea['id']?>"><?php echo $subarea['name']?></option>
        <?php endforeach; ?>
    </select>
    <a href="#" class="clear-tag close-stick <?php if($conveyor['subarea']==0): ?>hidden<?php endif; ?>" data-url="<?php echo $clearTagsUrl; ?>" data-type="subarea_select"></a>
</div>
