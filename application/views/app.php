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
            <div class="buttons">
                <?php if( $plan_limit_app !== null &&  $total_app >= $plan_limit_app && $plan_limit_platform !== null && $total_platform >= $plan_limit_platform){ ?>
                    <button class="btn btn-default disabled" disabled type="button"><?php echo $this->lang->line('button_add_app'); ?></button>
                <?php }else{ ?>
                    <button class="btn btn-info" onclick="location = baseUrlPath+'app/add'" type="button"><?php echo $this->lang->line('button_add_app'); ?></button>
                <?php } ?>
                <button class="btn btn-info" onclick="$('#form').submit();" type="button"><?php echo $this->lang->line('button_delete'); ?></button>
            </div>
        </div>
        <div class="content">
            <?php if($this->session->flashdata('success')){ ?>
                <div class="content messages half-width">
                <div class="success"><?php echo $this->session->flashdata('success'); ?></div>
                </div>
            <?php }?>
            <?php if ($this->session->flashdata("fail")): ?>
                <div class="content messages half-width">
                    <div class="warning"><?php echo $this->session->flashdata("fail"); ?></div>
                </div>
            <?php endif; ?>
            <?php
            $attributes = array('id' => 'form');
            echo form_open('app/delete',$attributes);
            ?>
                <?php if (isset($domain_list)) { ?>
                    <?php foreach ($domain_list as $domain) { ?>
                    <table class="list app-table">
                        <thead>
                        <tr>
                            <td width="1" style="text-align: center;">
                                <input type="checkbox" name="app_selected[]" value="<?php echo $domain['site_id']; ?>" onclick="$(this).parent().parent().parent().parent().find('input[name*=\'selected\']').attr('checked', this.checked);">
                            </td>
                            <td class="left" colspan="5"><h3><?php echo $domain['domain_name']; ?></h3>
                                <?php if( $plan_limit_platform !== null && $total_platform >= $plan_limit_platform){ ?>
                                    <button class="btn btn-default btn-mini disabled" disabled type="button">Add Platform</button>
                                <?php }else{ ?>
                                    <button class="btn btn-info btn-mini" onclick="location='<?php echo site_url("app/add_platform/".$domain['site_id']); ?>'" type="button">Add Platform</button>
                                <?php } ?>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                            <tr class="app-table-label">
                                <td style="text-align: center;" width="10%">

                                </td>
                                <td class="left" width="15%">
                                    Platform
                                </td>
                                <td width="20%">
                                    Api Key
                                </td>
                                <td >
                                    Api Secret
                                </td>
                                <td class="right" width="10%">
                                    Status
                                </td>
                                <td class="right app-col-action">
                                    Action
                                </td>
                            </tr>
                            <?php
                            foreach($domain["apps"] as $app){
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="platform_selected[]" value="<?php echo $app['_id']; ?>">
                                </td>
                                <td class="left app-col-platform">
                                    <?php
                                    if($app['platform'] == 'web'){
                                        $aicon = 'fa-desktop';
                                        $aname = 'Web Site';
                                    }elseif($app['platform'] == 'ios'){
                                        $aicon = 'fa-apple';
                                        $aname = 'IOS';
                                    }elseif($app['platform'] == 'android'){
                                        $aicon = 'fa-android';
                                        $aname = 'Android';
                                    }
                                    ?>
                                    <i class="fa <?php echo $aicon; ?> fa-lg"></i> <?php echo $aname; ?>
                                </td>
                                <td >
                                    <?php echo $app['api_key']; ?>
                                </td>
                                <td >
                                    <?php echo $app['api_secret']; ?>
                                    <a href="javascript:void(0)" onclick="confirmationReset('<?php echo $app['_id']; ?>')" title="Reset Api Secret" class="tooltips" data-placement="right"><i class="fa fa-repeat fa-lg"></i></a>
                                </td>
                                <td class="right">
                                    <?php if ($app['status']==1) { ?>
                                        <?php echo $this->lang->line('text_enabled'); ?>
                                    <?php } else { ?>
                                        <?php echo $this->lang->line('text_disabled'); ?>
                                    <?php } ?>
                                </td>
                                <td class="right app-col-action">
                                    <a href="<?php echo site_url("app/platform_edit/".$app['_id']) ?>" title="Edit" class="tooltips" data-placement="top"><i class="fa fa-edit fa-lg"></i></a>
                                    <a href="javascript:void(0)" onclick="confirmationDelete('<?php echo $app['_id']; ?>')" title="Delete" class="tooltips" data-placement="top"><i class="fa fa-trash fa-lg"></i></a>
                                </td>

                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } ?>
                <?php }else{

                    } ?>
            <?php
            echo form_close();
            ?>
            <div class="pagination">
                <ul class='ul_rule_pagination_container'>
                    <?php echo $pagination_links; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function resetSecret(platform_id) {
        $.ajax({
            url: baseUrlPath+'app/reset',
            type: 'POST',
            data: 'platform_id=' + platform_id,
            dataType: 'json',
            success: function(json) {
                if(json.success){
                    location.href = baseUrlPath+'app';
                }
            }
        });

        return false;
    }

    function deletePlatform(platform_id) {
        var platform = new Array(platform_id);

        $.ajax({
            url: baseUrlPath+'app/delete',
            type: 'POST',
            data: {platform_selected: platform},
            dataType: 'json',
            success: function(json) {
                location.href = baseUrlPath+'app';
            }
        });

        return false;
    }
</script>

<script type="text/javascript">
    function confirmationReset(platform_id){
        var decision = confirm('Are you sure to reset the secret key ?');
        if (decision){
            resetSecret(platform_id);
        }
    }

    function confirmationDelete(platform_id){
        var decision = confirm('Are you sure to delete ?');
        if (decision){
            deletePlatform(platform_id);
        }
    }
</script>
