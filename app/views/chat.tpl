<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
    <?php //$this->widget('Notification');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <div>
            <?php $this->widget('Chats');?>
            <?php $this->widget('Rooms');?>
        </div>
        <?php $this->widget('Chat');?>
    </section>
</main>
