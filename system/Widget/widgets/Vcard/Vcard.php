<?php

/**
 * @package Widgets
 *
 * @file Friendinfos.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display all the infos of a contact
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Vcard extends WidgetBase
{
    function WidgetLoad()
    {
		$this->registerEvent('myvcardreceived', 'onMyVcardReceived');
    	$this->addcss('vcard.css');
    	$this->addjs('vcard.js');
    }
    
    function onMyVcardReceived($vcard)
    {
		$html = $this->prepareInfos($vcard);
        RPC::call('movim_fill', 'vcard', RPC::cdata($html));
    }
    
/*    private function displayIf($element, $title, $html = false) {
        if(!$html) $html = $element;
        if(isset($element)) 
                return '<div class="element"><span>'.$title.'</span><div class="content">'.$html.'</div></div>';
    }*/
    
	function ajaxVcardSubmit($vcard) {
		$xmpp = Jabber::getInstance();
		$xmpp->updateVcard($vcard);
	}
    
    function prepareInfos($vcard) {
		$submit = $this->genCallAjax('ajaxVcardSubmit', "movim_parse_form('vcard')");
        $html ='
        <form name="vcard"><br />
            <fieldset>
                <legend>'.t('General Informations').'</legend>';
                
        $html .= '<div class="element"><span>'.t('Name').'</span>
                    <input type="text" name="vCardFN" class="content" value="'.$vcard["vCardFN"].'">
                  </div>';
        $html .= '<div class="element"><span>'.t('Nickname').'</span>
                    <input type="text" name ="vCardNickname" class="content" value="'.$vcard["vCardNickname"].'">
                  </div>';
        $html .= '<div class="element"><span>'.t('Date of Birth').' YYYY-MM-DD</span>
                    <input type="text" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" name ="vCardBDay" class="content" value="'.$vcard["vCardBDay"].'">
                  </div>';
                  
        $html .= '<br />
                  <div class="element"><span>'.t('Website').'</span>
                    <input type="url" name ="vCardUrl" class="content" value="'.$vcard["vCardUrl"].'">
                  </div>';
                  
        $html .= '<br />
                  <div class="element"><span>'.t('About Me').'</span>
                    <textarea name ="vCardDesc" class="content" >'.$vcard["vCardDesc"].'</textarea>
                  </div>';
                  
        $html .= '</fieldset>';                  
        $html .= '<br />
            <fieldset>
                <legend>'.t('Geographic Position').'</legend>';
		$html .= '<div class="warning">'.t('Renseigner votre position géographique peut fortement porter atteinte à votre vie privé, n\'utilisez toujours cette option qu\'en cas de nécessité').'<a class="button tiny" style="float: right;" onclick="getPos(this);">Récupérer ma position</a></div>';
		$html .= '<div id="geolocation"></div>';
        $html .= '<div class="element"><span>'.t('Latitude').'</span>
                    <input type="text" name="vCardLat" class="content" value="Latitude" readonly>
                  </div>';
        $html .= '<div class="element"><span>'.t('Longitude').'</span>
                    <input type="text" name="vCardLong" class="content" value="Longitude" readonly>
                  </div>';
        /*
        $html .= $this->displayIf($vcard["vCardFN"], t('Name'));
        $html .= $this->displayIf($vcard["vCardNickname"], t('Nickname'));
        $html .= $this->displayIf($vcard["from"], t('Adress'));
        $html .= $this->displayIf($vcard["vCardBDay"], t('Date of Birth'), date('j F Y',strtotime($vcard["vCardBDay"])));
        
        $html .= '<br />';
        
        $html .= $this->displayIf($vcard["vCardUrl"], t('Website'), '<a href="'.$vcard["vCardUrl"].'">'.$vcard["vCardUrl"].'</a>');
        $html .= $this->displayIf($vcard["vCardPhotoType"], t('Avatar'), '<img src="data:'.$vcard["vCardPhotoType"].';base64,'.$vcard["vCardPhotoBinVal"].'">');
        
        $html .= '<br />';
        $html .= $this->displayIf($vcard["vCardDesc"], t('About Me'));*/
        $html .= '<hr />';
		$html .= ' <input value="'.t('Submit').'" onclick="'.$submit.'" id="right" type="button"> ';
        $html .= '
            </fieldset>
        </form>';
        
        return $html;
    }

    function build()
    {
        ?>
		<div class="tabelem" title="<?php echo t('Profile'); ?>" id="vcard">
				<?php 
					echo $this->prepareInfos(Cache::c('myvcard'));
				?>
                
		</div>
        <?php
    }
}