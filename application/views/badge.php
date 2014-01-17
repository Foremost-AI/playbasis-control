<div id="content" class="span10">
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="image/category.png" alt="" /> <?php echo $heading_title; ?></h1>
            <?php //if($user_group_id != $setting_group_id){ ?>
            <div class="buttons">
                <button class="btn btn-info" onclick="location = baseUrlPath+'badge/insert'" type="button"><?php echo $this->lang->line('button_insert'); ?></button>
                <button class="btn btn-info" onclick="$('#form').submit();" type="button"><?php echo $this->lang->line('button_delete'); ?></button>
            </div>
            <?php //}?>
        </div>
        <div class="content">
            <?php if($this->session->flashdata('success')){ ?>
                <div class="content messages half-width">
                <div class="success"><?php echo $this->session->flashdata('success'); ?></div>
                </div>
            <?php }?>
            <div id="actions">
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
                                <td style="text-align: center;"><?php if ($badge['selected']) { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" checked="checked" />
                                    <?php } else { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $badge['badge_id']; ?>" />
                                    <?php } ?></td>
                                <td class="left"><div class="image"><img src="<?php echo $badge['image']; ?>" alt="" id="thumb" /></div></td>
                                <td class="left"><?php echo $badge['name']; ?></td>
                                <?php if(!$client_id){?>
                                    <td class="left"><?php echo ($badge['is_public'])?"Public":"Private"; ?></td>
                                <?php }?>
                                <td class="right"><?php echo $badge['quantity']; ?></td>
                                <td class="left"><?php echo ($badge['status'])? "Enabled" : "Disabled"; ?></td>
                                <td class="right"><?php echo $badge['sort_order']; ?></td>
                                <td class="right">
                                    [ <?php echo anchor('badge/update/'.$badge['badge_id'], 'Edit'); ?> ]
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
            </div><!-- #actions -->
                <?php
                    if($pagination_links != ''){
                        echo $pagination_links;
                    }
                ?>
        </div>
    </div>
</div>

<script type="text/javascript">

$('.push_down').live("click", function(){

    $.ajax({
        url : baseUrlPath+'badge/increase_order/'+ $(this).attr('alt'),
        dataType: "json"
    }).done(function(data) {
        console.log("Testing");
        var getListForAjax = 'badge/getListForAjax/';
        var getNum = '<?php echo $this->uri->segment(3);?>';
        if(!getNum){
            getNum = 0;
        }
        $('#actions').load(baseUrlPath+getListForAjax+getNum);
    });


  return false;

});
</script>


<script type="text/javascript">
$('.push_up').live("click", function(){
    $.ajax({
        url : baseUrlPath+'badge/decrease_order/'+ $(this).attr('alt'),
        dataType: "json"
    }).done(function(data) {
        console.log("Testing");
        var getListForAjax = 'badge/getListForAjax/';
        var getNum = '<?php echo $this->uri->segment(3);?>';
        if(!getNum){
            getNum = 0;
        }
        $('#actions').load(baseUrlPath+getListForAjax+getNum);
    });


  return false;
});

</script>

