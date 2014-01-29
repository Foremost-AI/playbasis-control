<?php
$attributes = array('id' => 'form');
echo form_open('goods/delete',$attributes);
?>
    <table class="list">
        <thead>
        <tr>
            <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
            <td class="left" style="width:72px;"><?php echo $this->lang->line('column_image'); ?></td>
            <td class="left"><?php echo $this->lang->line('column_name'); ?></td>
            <?php if(!$client_id){?>
                <td class="left"><?php echo $this->lang->line('column_owner'); ?></td>
            <?php }?>
            <td class="left" style="width:50px;"><?php echo $this->lang->line('column_quantity'); ?></td>
            <td class="left" style="width:50px;"><?php echo $this->lang->line('column_status'); ?></td>
            <td class="right" style="width:100px;"><?php echo $this->lang->line('column_sort_order'); ?></td>
            <td class="right" style="width:100px;"><?php echo $this->lang->line('column_action'); ?></td>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($goods_list)) { ?>
            <?php foreach ($goods_list as $goods) { ?>
                <tr>
                    <td style="text-align: center;">
                        <?php if($client_id){?>
                            <?php if(!(isset($goods['sponsor']) && $goods['sponsor'])){?>
                                <?php if ($goods['selected']) { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $goods['goods_id']; ?>" checked="checked" />
                                <?php } else { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $goods['goods_id']; ?>" />
                                <?php } ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if ($goods['selected']) { ?>
                                <input type="checkbox" name="selected[]" value="<?php echo $goods['goods_id']; ?>" checked="checked" />
                            <?php } else { ?>
                                <input type="checkbox" name="selected[]" value="<?php echo $goods['goods_id']; ?>" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td class="left"><div class="image"><img src="<?php echo $goods['image']; ?>" alt="" id="thumb" /></div></td>
                    <td class="left"><?php echo $goods['name']; ?></td>
                    <?php if(!$client_id){?>
                        <td class="left"><?php echo ($goods['is_public'])?"Public":"Private"; ?></td>
                    <?php }?>
                    <td class="right"><?php echo $goods['quantity']; ?></td>
                    <td class="left"><?php echo ($goods['status'])? "Enabled" : "Disabled"; ?></td>
                    <td class="right"><?php echo $goods['sort_order']; ?></td>
                    <td class="right">
                        <?php if($client_id){?>
                            <?php if(!(isset($goods['sponsor']) && $goods['sponsor'])){?>
                                [ <?php echo anchor('goods/update/'.$goods['goods_id'], 'Edit'); ?> ]
                            <?php } ?>
                        <?php }else{ ?>
                            [ <?php echo anchor('goods/update/'.$goods['goods_id'], 'Edit'); ?> ]
                        <?php } ?>
                        <?php echo anchor('goods/increase_order/'.$goods['goods_id'], '<i class="icon-chevron-down icon-large"></i>', array('class'=>'push_down', 'alt'=>$goods['goods_id'], 'style'=>'text-decoration:none'));?>
                        <?php echo anchor('goods/decrease_order/'.$goods['goods_id'], '<i class="icon-chevron-up icon-large"></i>', array('class'=>'push_up', 'alt'=>$goods['goods_id'], 'style'=>'text-decoration:none' ));?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td class="center" colspan="8"><?php echo $this->lang->line('text_no_results'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php
echo form_close();?>