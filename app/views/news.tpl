<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('VisioExt');?>

<div id="container">
    <div id="left">
        <?php $this->widget('Profile');?>
        <?php $this->widget('Notifs');?>
        <?php $this->widget('Bookmark'); ?>
    </div>
    <?php $this->widget('Menu');?>
    <?php $this->widget('Post');?>
</div>
<!--<div id="main">    -->
    <!--<div id="left">
        <?php //$this->widget('Profile');?>
        <?php //$this->widget('Notifs');?>
        <?php //$this->widget('Bookmark');?>
    </div>-->

    <!--<div id="center">-->

        <?php //$this->widget('News');?>
    <!--</div>-->

<!--</div>-->

<div id="right">
    <?php $this->widget('Roster');?>
</div>

