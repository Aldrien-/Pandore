<!DOCTYPE html>
<html>
    <head>
        <title>Pandore</title>
        <meta charset='utf-8' />
        <meta name='description' content='<?php echo($this->view->description); ?>' />
        <link rel='stylesheet' href="<?php echo($this->url('assets/css/markdown.css')); ?>" />
    </head>
    <body>
        <div id='Header'>
            <img src="<?php echo($this->url('assets/img/pandore.png')); ?>" alt='pandore' />
            <h1>Pandore</h1>
        </div>
        <?php echo $this->view->content; ?>
    </body>
</html>