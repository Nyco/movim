<?php

/**
 * @package Widgets
 *
 * @file Wall.php
 * This file is part of MOVIM.
 *
 * @brief The configuration form
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 28 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Config extends WidgetBase
{
    function WidgetLoad()
    {
		$this->addcss('config.css');
		$this->addjs('color/jscolor.js');
        $this->registerEvent('config', 'onConfig');
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
        Notification::appendNotification(t('Configuration updated'));
    }

	function ajaxSubmit($data) {
        $config = $this->user->getConfig();
        if(isset($config))
            $data = array_merge($config, $data);

        $s = new moxl\StorageSet();
        $s->setXmlns('movim:prefs')
          ->setData(serialize($data))
          ->request();
	}

	function ajaxGet() {
        $s = new moxl\StorageGet();
        $s->setXmlns('movim:prefs')
          ->request();
	}

	function build()
	{
            $languages = load_lang_array();
            /* We load the user configuration */
            $conf = $this->user->getConfig('language');
            $color = $this->user->getConfig('color');

            $submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('general')")
                . "this.className='button icon color orange loading'; setTimeout(function() {location.reload(false)}, 2000); this.onclick=null;";
    ?>
        <div class="tabelem padded" title="<?php echo t('Configuration'); ?>" id="config" >
            <form enctype="multipart/form-data" method="post" action="index.php" name="general">
                <fieldset>
                    <legend><?php echo t('General'); ?></legend>
                    <div class="element">
                        <label for="language"><?php echo t('Language'); ?></label>
                        <div class="select">
                            <select name="language" id="language">
                                <option value="en">English (default)</option>
                <?php
                              foreach($languages as $key => $value ) {
                                 if($key == $conf) { ?>
                                    <option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>
                <?php		       	 } else {?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php			     }
                              } ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?php echo t('Apparence'); ?></legend>
                    <div class="element">
                        <label for="color"><?php echo t('Background color'); ?></label>                        
                        <a 
                            type="button" 
                            onclick="
                                document.querySelector('input[name=color]').value = '082D50';
                                document.body.style.backgroundColor = '#082D50';"
                            style="width: 45%; float: right;" 
                            class="button icon color purple back">
                            <?php echo t('Reset');?>
                        </a>
                        <input 
                            style="box-shadow: none; width: 50%; float: left;"
                            name="color"
                            class="color" 
                            onchange="document.body.style.backgroundColor = '#'+this.value;"
                            value="
                            <?php 
                                if(isset($color))
                                    echo $color;
                                else
                                    echo "082D50";
                            ?>
                            ">
                    </div>
                    
                    <div class="element large">
                        <label for="pattern"><?php echo t('Pattern'); ?></label>
                        
                        <input type="radio" name="pattern" id="argyle" value="argyle"/>
                        <label for="argyle"><span></span>
                            <div class="preview argyle"
                                style="background-color: #6d695c;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="default" value="default"/>
                        <label for="default"><span></span>
                            <div class="preview default"
                                style="background-color: #082D50;;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="tableclothe" value="tableclothe"/>
                        <label for="tableclothe"><span></span>
                            <div class="preview tableclothe"
                                style="background-color: rgba(200, 0, 0, 1);"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="blueprint" value="blueprint"/>
                        <label for="blueprint"><span></span>
                            <div class="preview blueprint"
                                style="background-color:#269;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="cicada" value="cicada"/>
                        <label for="cicada"><span></span>
                            <div class="preview cicada"
                                style="background-color: #026873;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="stripes" value="stripes"/>
                        <label for="stripes"><span></span>
                            <div class="preview stripes"
                                style="background-color: orange;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="stars" value="stars"/>
                        <label for="stars"><span></span>
                            <div class="preview stars"
                                style="background-color:black; background-size: 100px 100px;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="paper" value="paper"/>
                        <label for="paper"><span></span>
                            <div class="preview paper"
                                style="background-color: #23343E;"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="tartan" value="tartan"/>
                        <label for="tartan"><span></span>
                            <div class="preview tartan"
                                style="background-color: hsl(2, 57%, 40%);"></div>
                        </label>
                        
                        <input type="radio" name="pattern" id="empty" value=""/>
                        <label for="empty"><span></span>
                            <div class="preview empty"
                                style="background-color: white;"></div>
                        </label>
                    </div>
                </fieldset>
                <br />
                
                <hr />
    <!--<label id="lock" for="soundnotif"><?php echo t('Enable Sound Notification:'); ?></label>
              <input type="checkbox" name="soundnotif" value="soundnotif" checked="checked" /><br /> -->
    <!--<input value="<?php echo t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
                <input type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->

                <br />
                <a onclick="<?php echo $submit; ?>" type="button" class="button icon yes color green" style="float: right;"><?php echo t('Submit'); ?></a>
                <!--<a type="reset" value="<?php echo t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->
                </p>
            </form>
            <br /><br />
            <div class="message info"><?php echo t("This configuration is shared wherever you are connected !");?></div>
        </div>
<?php
	}

}
