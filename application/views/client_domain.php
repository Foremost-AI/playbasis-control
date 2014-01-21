<style type="text/css">
    #domain-list input{
        width: 80%;
    }
</style>
<table id="domain-list" class="list">
    <thead>
    <tr>
        <td class="left" style="width:10%;"><?php echo $this->lang->line('column_domain_name'); ?></td>
        <td class="left" style="width:10%;"><?php echo $this->lang->line('column_domain_limit_users'); ?></td>
        <td class="left" style="width:15%"><?php echo $this->lang->line('column_domain_date_start'); ?></td>
        <td class="left" style="width:15%;"><?php echo $this->lang->line('column_domain_date_expire'); ?></td>
        <td class="left" style="width:10%"><?php echo $this->lang->line('column_domain_plan'); ?></td>
        <td class="right" style="width:10%"><?php echo $this->lang->line('column_domain_status'); ?></td>
        <td style="width:10%;"></td>
    </tr>
    </thead>
    <?php $domain_row = 0; ?>
    <?php if ($domains_data) { ?>
        <?php foreach ($domains_data as $domain) { ?>
            <tbody id="domain-row<?php echo $domain_row; ?>">
            <tr>
                <td class="left"><?php echo $domain['domain_name']; ?> [ <a href="#" class="button_reset_token" onclick="return resetToken('<?php echo $domain['site_id']; ?>');" ><?php echo $this->lang->line('text_reset_token'); ?></a> ]
                    <br /><span class="help">Keys:</span> <?php echo $domain['keys']; ?>
                    <br /><span class="help">Secret:</span> <?php echo $domain['secret']; ?>
                </td>
                <td class="left">
                    <input type="text" name="domain_value[<?php echo $domain_row; ?>][limit_users]" value="<?php echo $domain['limit_users']; ?>" size="50" />
                </td>
                <td class="left">
                    <input type="text" class="date" name="domain_value[<?php echo $domain_row; ?>][domain_start_date]" value="<?php if (strtotime(datetimeMongotoReadable($domain['date_start']))) { ?><?php echo date('Y-m-d', strtotime(datetimeMongotoReadable($domain['date_start']))); ?><?php } else { ?>-<?php } ?>" size="50" />
                </td>
                <td class="left">
                    <input type="text" class="date" name="domain_value[<?php echo $domain_row; ?>][domain_expire_date]" value="<?php if (strtotime(datetimeMongotoReadable($domain['date_expire']))) { ?><?php echo date('Y-m-d', strtotime(datetimeMongotoReadable($domain['date_expire']))); ?><?php } else { ?>-<?php } ?>" size="50" />
                </td>
                <td class="left">
                    <select name="domain_value[<?php echo $domain_row; ?>][plan_id]">
                        <!-- <option value="0" selected="selected"><?php //echo $this->lang->line('text_select'); ?></option> -->
                        <?php if ($plan_data) { ?>
                            <?php foreach ($plan_data as $plan) { ?>
                                <?php if ($domain['plan_id']==$plan['_id']) { ?>
                                    <option value="<?php echo $plan['_id']; ?>" selected="selected"><?php echo $plan['name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $plan['_id']; ?>"><?php echo $plan['name']; ?></option>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
                <td class="right"><select name="domain_value[<?php echo $domain_row; ?>][status]">
                        <?php if ($domain['status']==1) { ?>
                            <option value="1" selected="selected"><?php echo $this->lang->line('text_enabled'); ?></option>
                            <option value="0"><?php echo $this->lang->line('text_disabled'); ?></option>
                        <?php } else { ?>
                            <option value="1"><?php echo $this->lang->line('text_enabled'); ?></option>
                            <option value="0" selected="selected"><?php echo $this->lang->line('text_disabled'); ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <a onclick="deleteDomain('<?php echo $domain['client_id']; ?>', '<?php echo $domain['site_id']; ?>');$('#domain-row<?php echo $domain_row; ?>').remove();" class="button"><span><?php echo $this->lang->line('button_remove'); ?></span></a>
                    <input type="hidden" name="domain_value[<?php echo $domain_row; ?>][client_id]" value="<?php echo $domain['client_id']; ?>" />
                    <input type="hidden" name="domain_value[<?php echo $domain_row; ?>][site_id]" value="<?php echo $domain['site_id']; ?>" />
                </td>
            </tr>
            </tbody>
            <?php $domain_row++; ?>
        <?php } ?>
    <?php } ?>
</table>

<div class="pagination"><?php if(isset($pagination_links)){echo $pagination_links;} ?></div>
<script type="text/javascript">
    $(function(){

        $('.date').datepicker({dateFormat: 'yy-mm-dd'});

    })
</script>
<script type="text/javascript">

    function deleteDomain(clientId , siteId) {
        var client_id = clientId;
        var site_id = siteId;

        $.ajax({
            // url: baseUrlPath+'domain/delete',
            url: baseUrlPath+'domain/deleteAjax',
            type: 'POST',
            dataType: 'json',
            data: ({'client_id' : client_id, 'site_id' : site_id}),
            success: function(json) {
                var notification = $('#notification');

                if (json['error']) {
                    $('#notification').html(json['error']).addClass('warning').show();
                } else {

                    $('#notification').html(json['success']).addClass('success').show();
                    //location.reload(true);
                    $('#domains').load(baseUrlPath+'client/domain?client_id='+client_id);
                }
            }

        });

        return false;

    }

    function resetToken(site_id) {

        $.ajax({
            url: baseUrlPath+'domain/reset',
            type: 'post',
            data: 'site_id=' + site_id,
            dataType: 'json',
            success: function(json) {
                location.reload(true);
            }
        });

        return false;

    }
</script>