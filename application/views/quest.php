<div id="content" class="span10">
    <div class="box">
        <div class="heading">
            <h1><img src="image/category.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons">
            <button class="btn btn-info" onclick="location =  baseUrlPath+'quest/insert'" type="button"><?php echo $this->lang->line('button_insert'); ?></button>
                <button class="btn btn-info" onclick="$('#form').submit();" type="button"><?php echo $this->lang->line('button_delete'); ?></button>
            </div>
        </div><!-- .heading -->
        <div class="content">
        <?php if($this->session->flashdata('success')){ ?>
                <div class="content messages half-width">
                <div class="success"><?php echo $this->session->flashdata('success'); ?></div>
                </div>
            <?php }?>
            <div id="actions">
        <?php $attributes = array('id'=>'form');?>
        <?php echo form_open('quest/delete', $attributes);?>
            <table class="list">
                <thead>
                    <tr>
                    <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
                    <td class="left" style="width:72px;"><?php echo $this->lang->line('column_image'); ?></td>
                    <td class="right" style="width:100px;"><?php echo $this->lang->line('column_quest_name'); ?></td>
                    <td class="right" style="width:100px;"><?php echo $this->lang->line('column_quest_status'); ?></td>
                    <td class="right" style="width:100px;"><?php echo $this->lang->line('column_quest_sort_order'); ?></td>
                    <td class="right" style="width:140px;"><?php echo $this->lang->line('column_action'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr class="filter">
                        <td></td>
                        <td></td>
                        <td><input type="text" name="filter_name" value="" style="width:50%;" /></td>
                        <?php if(!$client_id){?>
                            <td></td>
                        <?php }?>
                        <td></td>
                        <td></td>
                        <td class="right">
                            <a onclick="clear_filter();" class="button" id="clear_filter"><?php echo $this->lang->line('button_clear_filter'); ?></a>
                            <a onclick="filter();" class="button"><?php echo $this->lang->line('button_filter'); ?></a>
                        </td>
                    </tr>
                    
                        <?php if(isset($quests)){?>
                            <?php foreach($quests as $quest){?>
                                <tr>
                                    <td style="text-align: center;"><?php if (isset($quest['selected'])) { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $quest['_id']; ?>" checked="checked" />
                                        <?php } else { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $quest['_id']; ?>" />
                                        <?php } ?></td>
                                    <td class="left"><img src="<?php echo $quest['image']; ?>"/></td>
                                    <td class="right"><?php echo $quest['quest_name']; ?></td>   
                                    <!-- <td class="right"><?php //echo datetimeMongotoReadable($quest['condition']['datetime_start']); ?></td> -->
                                    <!-- <td class="right"><?php //echo datetimeMongotoReadable($quest['condition']['datetime_end']); ?></td> -->
                                    <td class="right"><?php echo ($quest['status'])?'Active':'Inactive';?></td>
                                    <td class="right"><?php echo $quest['sort_order'];?></td>
                                    <td class="right">[ <?php if($client_id){
                                            // echo anchor('quest/update/'.$quest['action_id'], 'Edit');
                                            echo anchor('quest/edit/'.$quest['_id'], 'Edit');
                                        }else{
                                            echo anchor('action/edit/'.$quest['_id'], 'Edit');
                                        }
                                        ?> ]

                                        <?php if($client_id){
                                            // echo anchor('action/increase_order/'.$quest['action_id'], '<i class="icon-chevron-down icon-large"></i>', array('class'=>'push_down', 'alt'=>$quest['action_id'], 'style'=>'text-decoration:none'));
                                            echo anchor('action/increase_order/'.$quest['_id'], '<i class="icon-chevron-down icon-large"></i>', array('class'=>'push_down', 'alt'=>$quest['_id'], 'style'=>'text-decoration:none'));
                                        }else{
                                            echo anchor('action/increase_order/'.$quest['_id'], '<i class="icon-chevron-down icon-large"></i>', array('class'=>'push_down', 'alt'=>$quest['_id'], 'style'=>'text-decoration:none'));
                                        }
                                        ?>
                                        <?php if($client_id){
                                            // echo anchor('action/decrease_order/'.$quest['action_id'], '<i class="icon-chevron-up icon-large"></i>', array('class'=>'push_up', 'alt'=>$quest['action_id'], 'style'=>'text-decoration:none'));
                                            echo anchor('action/decrease_order/'.$quest['_id'], '<i class="icon-chevron-up icon-large"></i>', array('class'=>'push_up', 'alt'=>$quest['_id'], 'style'=>'text-decoration:none'));
                                        }else{
                                            echo anchor('action/decrease_order/'.$quest['_id'], '<i class="icon-chevron-up icon-large"></i>', array('class'=>'push_up', 'alt'=>$quest['_id'], 'style'=>'text-decoration:none'));
                                        }
                                        ?>
                                        </td>   
                                </tr>
                            <?php }?>
                        <?php }?>
                    
                </tbody>
            </table>
        <?php echo form_close();?>
        </div>
            <div class="pagination">
                <ul class='ul_rule_pagination_container'>
                    <?php if(!isset($_GET['filter_name'])){?>
                        <?php echo $this->pagination->create_links();?>
                    <?php } ?>
                </ul>
            </div>
        </div><!-- .content -->
    </div><!-- .box -->
</div><!-- #content .span10 -->


<script type="text/javascript"><!--
function filter() {
    url = baseUrlPath+'quest';

    var filter_name = $('input[name=\'filter_name\']').attr('value');

    if (filter_name) {
        url += '?filter_name=' + encodeURIComponent(filter_name);
    }

    location = url;
}
//--></script>

<script type="text/javascript">
    $('input[name=\'filter_name\']').live("focus", function (event) {
        $(this).autocomplete({
            delay: 0,
            source: function(request, response) {
                $.ajax({
                    url: baseUrlPath+'quest/autocomplete?filter_name=' +  encodeURIComponent(request.term),
                    dataType: 'json',
                    success: function(json) {
                        console.log(json);
                        response($.map(json, function(item) {
                            return {
                                label: item.name,
                                name: item.name
                            }
                        }));
                        console.log(response);
                    }
                });
            },
            select: function(event, ui) {
                $('input[name=\'filter_name\']').val(ui.item.name);

                return false;
            },
            focus: function(event, ui) {
                return false;
            }
        });
    });
</script>


<script type = "text/javascript">

$( ".push_down" ).live( "click", function() {
  
    $.ajax({
        url: baseUrlPath+'quest/increase_order/'+$(this).attr('alt'),
        dataType: "json"
    }).done(function(data) {
        var getListForAjax = 'quest/getListForAjax/';
        var getNum = '<?php echo $this->uri->segment(3);?>';
        if(!getNum){
            getNum = 0;
        }
        $('#actions').load(baseUrlPath+getListForAjax+getNum);
        console.log(baseUrlPath+getListForAjax+getNum);
    });


  return false;
});

</script>

<script type = "text/javascript">

$( ".push_up" ).live( "click", function() {
  
    $.ajax({
        url: baseUrlPath+'quest/decrease_order/'+$(this).attr('alt'),
        dataType: "json"
    }).done(function(data) {
        var getListForAjax = 'quest/getListForAjax/';
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
    <?php if (!isset($_GET['filter_name'])){?>
        $("#clear_filter").hide();
    <?php }else{?>
        $("#clear_filter").show();
    <?php }?>

    function clear_filter(){
        window.location.replace(baseUrlPath+'quest');
    }
</script>