<?php
                $attributes = array('id' => 'form');
                echo form_open('badge/delete',$attributes);
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
                            <?php if (isset($badges)) { ?>
                            <?php foreach ($badges as $badge) { ?>
                            <tr>
                                <td style="text-align: center;">
                                <?php if (!$client_id){?>
                                    <?php if ($badge['selected']) { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" checked="checked" />
                                    <?php } else { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" />
                                    <?php } ?>
                                <?php }else{?> 
                                    <?php if(!(isset($badge['sponsor']) && $badge['sponsor'])){?> 
                                    <?php if ($badge['selected']) { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" checked="checked" />
                                    <?php } else { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" />
                                    <?php } ?>
                                    <?php }?>
                                <?php }?>
                                </td>
                                <td class="left"><div class="image"><img src="<?php echo $badge['image']; ?>" alt="" id="thumb" onerror="$(this).attr('src','<?php echo base_url();?>image/default-image.png');" /></div></td>
                                <td class="left"><?php echo $badge['name']; ?></td>
                                <?php if(!$client_id){?>
                                    <td class="left"><?php echo ($badge['is_public'])?"Public":"Private"; ?></td>
                                <?php }?>
                                <td class="right"><?php echo $badge['quantity']; ?></td>
                                <td class="left"><?php echo ($badge['status'])? "Enabled" : "Disabled"; ?></td>
                                <td class="right"><?php echo $badge['sort_order']; ?></td>
                                <td class="right">
                                    <?php if(!$client_id){?>
                                        [ <?php echo anchor('badge/update/'.$badge['badge_id'], 'Edit'); ?> ]
                                    <?php }else{?>
                                        <?php if(!(isset($badge['sponsor']) && $badge['sponsor'])){?> 
                                            [ <?php echo anchor('badge/update/'.$badge['badge_id'], 'Edit'); ?> ]
                                        <?php }?>
                                    <?php }?>
                                    <?php echo anchor('badge/increase_order/'.$badge['badge_id'], '<i class="icon-chevron-down icon-large"></i>', array('class'=>'push_down', 'alt'=>$badge['badge_id'], 'style'=>'text-decoration:none'));?>
                                    <?php echo anchor('badge/decrease_order/'.$badge['badge_id'], '<i class="icon-chevron-up icon-large"></i>', array('class'=>'push_up', 'alt'=>$badge['badge_id'], 'style'=>'text-decoration:none' ));?>
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

<div class="pagination">
    <ul class='ul_rule_pagination_container'>
        <li class="page_index_number active"><a>Total Records:</a></li> <li class="page_index_number"><a><?php echo number_format($pagination_total_rows); ?></a></li>
        <li class="page_index_number active"><a>(<?php echo number_format($pagination_total_pages); ?> Pages)</a></li>
        <?php echo $pagination_links; ?>
    </ul>
</div>