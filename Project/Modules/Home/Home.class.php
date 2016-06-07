<?php

namespace Project\Modules\Home;

use Kernel\Core as Core;

/**
 * @brief The Home module class.
 */
class Home extends Core\Module
{
    /**
     * @brief The default action.
     */
    public function defaultAction()
    {
        // Set the head description.
        $this->layout->description = 'A PHP framework targeting small and medium projects';

        // Set the view message.
        $this->view->message = 'Welcome to your new project.';
    }
}

?>