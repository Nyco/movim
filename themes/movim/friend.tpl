<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Notifs');?>
<?php $this->widget('Chat');?>
<?php $this->widget('ChatExt');?>

<div id="head">
    <?php $this->widget('ContactSummary');?>
</div>

<div id="main">
    <div id="left">
        <?php $this->widget('ContactInfo');?>
        <?php $this->widget('ContactManage');?>
    </div>

    <div id="center">
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Wall');?>
        <?php $this->widget('ContactCard');?>
        <?php $this->widget('ContactPubsubSubscription');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>
