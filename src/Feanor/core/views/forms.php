<?php
/**
 * Los forms están dentro de un DIV único. El caracter random es para que
 * no tengamos 2 forms iguales. Lo mismo con el div y el form
 *
 * Necesitamos el id en caso de que exista (para la edición, etc)
 *
 * @package		Luthien
 * @author		Rene Silva <rsilva@eresseasolutions.com>
 * @copyright	Copyright (c) 2009 - 2012 Eressea Solutions
 * @version		0.1
 */
?>
<div id="<?=$table_name?>_div_<?=$random?>">
    <div id="error_<?=$table_name?>_form_<?=$random?>" class="ui-state-error ui-corner-all" style="display:none;"></div>
    <fieldset>
        <?php	if($title != ''):?>
        <legend class="legend"><?=$title?></legend>
        <?php
        endif;?>
        <form class="form parent form-horizontal form-<?=$table_name?>"
                id="<?=$table_name?>_form_<?=$random?>" action="<?=$form_action?>" method="post">
            <?php if(isset($help) && $help != ''):?>
            <div class="help"><?=$help?></div>
            <?php endif;?>
            <input type="hidden" name="id" value="<?=$id?>" />
            <?php
            if(isset($nonce)){
                ?>
            <input type="hidden" name="nonce_value" value="<?=$nonce?>"/>
            <?php
            }
            ?>
            <?=$html_before_table?>
                <?php
                foreach($campos as $myID=>$i){
                    if($i != ''){//evitando mostrar a sw :)
                ?>
                <div class="control-group">
                        <?php
                        /*$i ya viene generado por la clase FileType dependiendo de su forma*/
                        echo $i;?>
                </div>
                <?php
                    }
                }
                ?>
            </table>
            <?=$html_before_submit?>
            <div class="form-actions">
                <?php
                if(!empty($ajax_function)):
                foreach($ajax_function as $key=>$func){
                    //para desplegar información o no de la función (this, balblalba)
                    $display_function_info = true;
                    if(isset($func['button_information']) && $func['button_information'] == false){
                        $display_function_info = false;
                    }
                    if($func['function'] === 'submit'){
                        $display_function_info = false;
                        $func['function'] = 'jQuery(\'#'.$table_name.'_form_'.$random.'\').submit()';
                    }
                ?>
                <a class="boton_a btn <?=$func['class']?>"
                    onclick="<?=$func['function']?><?php if($display_function_info):?>('<?=$table_name?>_form_<?=$random?>',this)<?php endif;?>;">
                    <?php if(isset($func['icon'])):?><i class="<?=$func['icon']?>"></i><?php endif;?>
                    &nbsp;<?=str_replace(' ', '&nbsp;', $key)?>
                </a>
                <?php
                }
                endif;?>
            </div>
            <?php foreach($hidden_values as $name=>$value):?>
            <input type="hidden" name="<?=$name?>" value="<?=$value?>"/>
            <?php endforeach;?>
        </form>
    </fieldset>
</div>
