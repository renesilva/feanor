<?php
/**
 * Template que genera el código javascript necesario 
 * 
 * @package		Luthien
 * @author		Rene Silva <rsilva@eresseasolutions.com>
 * @copyright	Copyright (c) 2009 - 2012 Eressea Solutions
 * @version		0.1
 */
foreach($functions as $function_name => $function_attributes): ?>
	<?php
    if($function_attributes['type'] == 'options_table'):?>
        function <?=$function_name?>(tr_id,id) {
            bloquear();
            if(id == undefined) {
                id = 0;
            }
            <?php
        if (isset($function_attributes['before'])) {
            echo $function_attributes['before'];
        }
            ?>
            ajaxFW_normal("<?=BASE_URL?><?=$function_attributes['url']?>","id="+id,
                function(server_response) {
                    var variables_address = {
                        'id': id
                    }
                    <?php
        if (isset($function_attributes['after'])) {
            echo $function_attributes['after'];
        }
                    ?>
                    desbloquear();
                    <?php
        if (isset($function_attributes['address'])):?>
                    jQuery.address.path(sprintf("<?=$function_attributes['address']?>",variables_address));
                    <?php
        endif;?>
                    <?php
        if (isset($function_attributes['breadcrumbs'])):?>
                    decorate_breadcrumb(<?=json_encode($function_attributes['breadcrumbs'])?>);
                    <?php
        endif;?>
                }
            );
        }
	<?php
    elseif($function_attributes['type'] == 'save_changes_table'):?>
        function <?=$function_name?>(tr_id, id, boton) {
            bloquear();
            if (id == undefined)
                id = 0;
            if (waitOther('<?=$function_name?>', tr_id, id) == true) {
                c = new Array();
                $("#" + tr_id + ' :input').each(function() {
                    c.push($(this).attr('name') + "=" + $(this).val());
                });
                paraPasar = c.join("&");
            <?php
        if (isset($function_attributes['before'])) {
            echo $function_attributes['before'];
        }
        ?>
            ajaxFWTable('<?=BASE_URL?><?=$function_attributes['url']?>', paraPasar, function()	{
                var variables_address = {
                    'id': id
                }
                <?php
        if (isset($function_attributes['after'])) {
            echo $function_attributes['after'];
        }
            ?>
                desbloquear();
                <?php
        if(isset($function_attributes['address'])):?>
                jQuery.address.path(sprintf("<?=$function_attributes['address']?>",variables_address));
                <?php
        endif;?>
                }, tr_id);
            }
        }
	<?php
    elseif($function_attributes['type'] == 'save_changes'):?>
        function <?=$function_name?>(form,boton) {
            bloquear();
            if(boton != undefined)
            if(waitOtherSubmit("<?=$function_name?>",form) == true)
            {
            <?php
        if (isset($function_attributes['before'])) {
            echo $function_attributes['before'];
        }
        ?>
                ajaxFW("<?=BASE_URL?><?=$function_attributes['url']?>",$("#"+form).serialize(),
                    function(server_response) {
                        var variables_address = {}
                    <?php
        if (isset($function_attributes['after'])) {
            echo $function_attributes['after'];
        }
        ?>
                        desbloquear();
                    <?php
        if (isset($function_attributes['address'])):?>
                        jQuery.address.path(sprintf("<?=$function_attributes['address']?>",variables_address));
                    <?php
        endif;?>
                    }
                ,form);
            }
        }
	<?php
    elseif($function_attributes['type'] == 'show_table'):?>
function <?=$function_name?>() {
        <?php
        if (isset($function_attributes['before'])) {
            echo $function_attributes['before'];
        }
        ?>
        bloquear();
        ajaxFW_normal("<?=BASE_URL?><?=$function_attributes['url']?>","",
            function(server_response) {
                var variables_address = {}
                    <?php
        if (isset($function_attributes['after'])) {
            echo $function_attributes['after'];
        }
                 ?>
                desbloquear();
                <?php
        if(isset($function_attributes['address'])):?>
            jQuery.address.path(sprintf("<?=$function_attributes['address']?>",variables_address));
                <?php
        endif;?>
                }
            );
        }
    <?php
    endif;
endforeach;
