<!DOCTYPE html>
<html>
    <head>
        <title>Pandore debug view</title>
        <meta charset='utf-8' />
        <link rel='stylesheet' href="<?php echo $this->url('../Kernel/Plugins/ExceptionsHandler/Resources/markdown.css'); ?>" />
    </head>
    <body>
        <div id='Header'>
            <img src="<?php echo $this->url('../Kernel/Plugins/ExceptionsHandler/Resources/pandore.png'); ?>" alt='pandoreDebug' />
            <h1>Pandore debug view</h1>
        </div>
        <h2>Exception</h2>
        <h3>Name</h3>
        <pre><?php echo get_class($this->view->exception); ?></pre>
        <h3>Message</h3>
        <pre><?php echo $this->view->exception->getMessage(); ?></pre>
        <h3>File and line</h3>
        <pre><?php echo $this->view->exception->getFile().'('.$this->view->exception->getLine().')'; ?></pre>
        <h2>Trace</h2>
        <pre><?php echo $this->view->exception->getTraceAsString(); ?></pre>
        <pre><?php print_r($this->view->exception->getTrace()); ?></pre>
    </body>
</html>