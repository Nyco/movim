<?php

use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Action\Pubsub\DiscoItems;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\Create;

class Groups extends WidgetCommon
{
    private $_list_server;

    function load()
    {
        $this->registerEvent('pubsub_discoitems_handle', 'onDisco');
        $this->registerEvent('pubsub_discoitems_error', 'onDiscoError');
        $this->registerEvent('pubsub_create_handle', 'onCreate');
        $this->registerEvent('pubsub_delete_handle', 'onDelete');
        $this->registerEvent('pubsub_delete_error', 'onDeleteError');
        $this->addjs('groups.js');
    }

    function onDisco($packet)
    {
        $server = $packet->content;
        $this->displayServer($server);
    }

    function onCreate($packet)
    {
        Notification::append(null, $this->__('groups.created'));

        list($server, $node) = array_values($packet->content);
        $this->ajaxDisco($server);
    }

    function onDelete($packet)
    {
        Notification::append(null, $this->__('groups.deleted'));

        list($server, $node) = array_values($packet->content);
        $this->displayServer($server);
    }

    function onDeleteError($packet)
    {
        Notification::append(null, $this->__('groups.deleted'));

        $m = new Rooms;
        $m->setBookmark();

        list($server, $node) = array_values($packet->content);
        $this->ajaxSubscriptions();
    }

    function onDiscoError($packet)
    {
        // Display a nice error
    }

    function ajaxHeader()
    {
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
        $header = $view->draw('_groups_header', true);

        Header::fill($header);
    }

    function ajaxSubscriptions()
    {
        $html = $this->prepareSubscriptions();

        RPC::call('movim_fill', 'groups_widget', $html);
        RPC::call('Groups.refresh');
    }

    function ajaxDisco($server)
    {
        if(!$this->validateServer($server)) return;

        $r = new DiscoItems;
        $r->setTo($server)->request();
    }

    function ajaxAdd($server)
    {
        if(!$this->validateServer($server)) return;

        $view = $this->tpl();
        $view->assign('server', $server);

        Dialog::fill($view->draw('_groups_add', true));
    }

    function ajaxAddConfirm($server, $form)
    {
        if(!$this->validateServer($server)) return;

        $validate_name = Validator::string()->length(6, 80);
        if(!$validate_name->validate($form->name->value)) return;

        $uri = stringToUri($form->name->value);

        $c = new Create;
        $c->setTo($server)->setNode($uri)->setData($form->name->value)
          ->request();
    }

    private function displayServer($server)
    {
        if(!$this->validateServer($server)) return;

        $html = $this->prepareServer($server);

        RPC::call('movim_fill', 'groups_widget', $html);
        RPC::call('Groups.refresh');
    }

    function checkNewServer($node) {
        $r = false;
        
        if($this->_list_server != $node->server)
            $r = true;

        $this->_list_server = $node->server;
        return $r;
    }

    function prepareSubscriptions() {
        $sd = new \modl\SubscriptionDAO();

        $view = $this->tpl();
        $view->assign('subscriptions', $sd->getSubscribed());
        $html = $view->draw('_groups_subscriptions', true);

        return $html;
    }

    private function prepareServer($server) {
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('nodes', $id->getItems($server));
        $view->assign('server', $server);
        $html = $view->draw('_groups_server', true);

        return $html;
    }

    private function cleanServers($servers) {
        $i = 0;
        foreach($servers as $c) {
            if(filter_var($c->server, FILTER_VALIDATE_EMAIL)) {
                unset($servers[$i]);
            } elseif(count(explode('.', $c->server))<3) {
                unset($servers[$i]);
            }
            $i++;
        }
        return $servers;
    }
    /**
     * @brief Validate the server
     *
     * @param string $server
     */
    private function validateServer($server)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        if(!$validate_server->validate($server)) return false;
        else return true;
    }

    function display()
    {
    }
}
