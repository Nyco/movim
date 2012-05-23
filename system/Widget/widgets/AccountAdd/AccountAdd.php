<?php

/**
 * @package Widgets
 *
 * @file Account.php
 * This file is part of MOVIM.
 *
 * @brief The account adding widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class AccountAdd extends WidgetBase {
    function __construct() {
        parent::__construct(true);
    }
    
	function ajaxSubmit($data) {
	    foreach($data as $value) {
	        if($value == NULL || $value == '') {
	            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=datamissing"));
	            RPC::commit();
	            exit;
	        }
	    }

	    foreach($data as $value) {
            if(!filter_var($data['jid'], FILTER_VALIDATE_EMAIL)) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=jiderror"));
                RPC::commit();
                exit;
            } elseif($data['password'] != $data['passwordconf']) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=passworddiff"));
                RPC::commit();
 	            exit;
            } elseif(eregi('[^a-zA-Z0-9_]', $data['nick'])) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=nameerr"));
                RPC::commit();
 	            exit;
            }
	    }

	    unset($data['passwordconf']);

        $u = new UserConf();
        if($u->getConf($data['jid']) == false) {
            $host = end(explode('@', $data['jid']));
            $dns = dns_get_record('_xmpp-client._tcp.'.$host);
            
            if(isset($dns[0]['target']) && $dns[0]['target'] != null)
                $domain = $dns[0]['target'];
            else {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=dnsdomain"));
                RPC::commit();
 	            exit;
            }
            $confvar = Conf::getServerConf();

            global $sdb;
            $conf = new ConfVar();
            
            $conf
                ->set('login', $data['jid'])
                ->set('pass', sha1($data['password']))
                ->set('host', $host)
                ->set('domain', $domain)
                ->set('port', $confvar['port'])
                ->set('boshHost', $confvar['defBoshHost'])
                ->set('boshSuffix', $confvar['defBoshSuffix'])
                ->set('boshPort', $confvar['defBoshPort'])
                ->set('language', $confvar['defLang'])
                ->set('first', false);

            $sdb->save($conf);

            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=mainPage&err=acccreated"));
            RPC::commit();
            exit;
        } else {
            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountAdd&err=userconflict"));
            RPC::commit();
            exit; 
        }
	}
    
	function build()
	{
        switch ($_GET['err']) {
            case 'datamissing':
	            $warning = '
	                    <div class="error">
	                        '.t('Some data are missing !').'
	                    </div> ';
                break;
            case 'jiderror':
	            $warning = '
	                    <div class="error">
	                        '.t('Wrong ID').'
	                    </div> ';
                break;
            case 'passworddiff':
	            $warning = '
	                    <div class="error">
	                        '.t('You entered different passwords').'
	                    </div> ';
                break;
            case 'nameerr':
	            $warning = '
	                    <div class="error">
	                        '.t('Invalid name').'
	                    </div> ';
                break;
            case 'userconflict':
	            $warning = '
	                    <div class="error">
	                        '.t('Username already taken').'
	                    </div> ';
                break;
            case 'dnsdomain':
	            $warning = '
	                    <div class="error">
	                        '.t('XMPP Domain error, your account is not a correct Jabber ID').'
	                    </div> ';
                break;
        }
        
        $submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('accountAdd')");
        ?>
        <div id="account" style="width: 730px; margin: 0 auto;">
            <?php echo $warning; ?>
            <h1><?php echo t('Add your login informations'); ?></h1>
            <form  style="width: 500px; float: left;" name="accountAdd">
                
                <p style="margin-top: 20px;">
                    <input
                        type="email"
                        autofocus
                        placeholder="<?php echo t("My address"); ?>"
                        class="big"
                        style="width: 500px;"
                        name="jid"/>
                </p>

                <p>
                    <input
                        type="password"
                        placeholder="<?php echo t("Password"); ?>"
                        class="big"
                        style="width: 500px;"
                        name="password"
                    />
                </p>

                <p>
                    <input
                        type="password"
                        placeholder="<?php echo t("Retype"); ?>"
                        class="big"
                        style="width: 500px;"
                        name="passwordconf"
                    />
                </p>
                
                <p>
                    <input type="button" class="button big icon submit" style="float: right;" value="<?php echo t('Create'); ?>" onclick="<?php echo $submit;?> this.className='button big icon loading';">
                </p>
            </form>
        </div>
        <?php
    }
}