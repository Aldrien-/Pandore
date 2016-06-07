# ![Logo Pandore](https://raw.githubusercontent.com/Aldrien-/Pandore/master/Kernel/Plugins/ExceptionsHandler/Resources/pandore.png "Logo Pandore") Pandore

Pandore is a PHP framework targeting small and medium projects which need to start quickly.

Pandore was originally made to power a new version of an online medieval game called Kalteria (ex Zordania) and has also been used to develop prototypes at HPC-SA (a French SMB) and at [VTREEM](http://www.vtreem.com) (a French startup) for some years.

## How to download it

Get sources and put them into your server directory.

## How it works

### Routing

Routes represent the most important information received from the original request and they are where the requests dispatch process start. Each URI is composed of a module name, an action name, can be completed with some `GET` variables and have to be formatted like this:

    /moduleName/actionName/key1/value1/key2/value2/...

As you can imagine, a route leads to a module, an action and allows to build a `Request` object from `GET` values. This `Request` object will be also filled with other request values like `POST`, `COOKIE`, ... .

### Requests dispatch process

Several components are involved in the requests dispatch process but you have to consider only few of them: modules and plugins.

The dispatch process is based on the following steps: 
1. Plugins pre dispatch
2. Module pre execution
3. Action execution
4. Module post execution
5. Module finalization
6. Plugins post dispatch
7. Response sending

Define your own actions or override some methods to make them do what you want. Modules and plugins also have an `init` method in order to let you personalize their construction.

The response sending is the action of outputing the response content attribute.

### Data saving mechanism

Pandore has a data saving mechanism to make models manipulation easier. CRUD functions have already been implemented and work with all valid data sources.

To do more complex data manipulations you should get the `DataSourceProxy` object which allows to get your data sources wherever they are necessary. 

    Kernel\Core\DataSourceProxy::get();

### Rendering process

By default, a rendering process is defined. In general, an action view is firstly produced as the content of the response and then this is included into a more global view called layout. This mechanism allows to separate regular parts of your website from what depends on the action.

You can set values to action views and layouts from modules using :

    $this->view->key = value;
    $this->layout->key = value;

and get them into view files using :

    $this->view->key;

In real applications, this process could be more complex or totally different. Pandore allows to reach these two goals by two different means.

On one hand, you can use composed views both in action and layout view. Views can be composed of partial ones which are like inclusions and action ones which are results of full action execution without plugins pre/post dispatch operations.

    <?php echo $this->partial('partialViewName', 'viewName', array()); ?>

    <?php echo $this->action('actionViewName', 'moduleName', 'actionName', array()); ?>

On the other hand, you are free to control, stop or redefine the rendering process. It's particularly useful in case of full REST applications, JSON only servers, etc. More information is available in the modules section below.

### Automatic files inclusion

As long as you use namespaces based on path, and the right way to name your file and some classic file extensions, you don't have to include any files yourself. The `Loader` object will automatically do it. It manages `.class.php`, `.interface.php`, `.lib.php`, `.trait.php`, `.tasks.php` and `.php` as valid extensions.

### Tools

Helpers, models, modules, plugins and view files natively use `Tools` which allow them to use its methods as if their are one of them.

For example, `url` which computes an absolute url from a relative one, is particularly useful in view files to get assets: 

    <?php echo $this->url('assets/css/main.css'); ?>

### Last words

Be careful with the casing of URIs, folders, files, class and method names because all Pandore mechanism are case-sentive, so be consistent!

For example :

An URI gives you a module `ModuleName` and an action `ActionName`. So, you have to define a folder named `ModuleName` within a module named `ModuleName` in it. This module has to define an action named `ActionNameAction`. Also, a view file named `ActionName` has to exist in the `Views` folder of the module folder.  

## How to use it

You only have to focus on what is in your `Project` and `www` folders. Each folder content is described below but let's start with a global view of a typical folder hierarchy.

    Project/
        Config/
            default.ini
        Datasources/
            Mercure/
        Exceptions/
            Exception1.class.php
            Exception2.class.php
        Models/
            Model1.class.php
            Model2.class.php
        Modules/
            Module1/
                Helpers/
                    Helper1.class.php
                    Helper2.class.php
                Views/
                    Action1.php
                    Action2.php
                Module1.class.php
            Module2/
                Helpers/
                    Helper3.class.php
                    Helper4.class.php
                Views/
                    Action3.php
                    Action4.php
                Module2.class.php
        Plugins/
            Plugin1/
                Plugin1.class.php
            Plugin2/
                Config/
                Views/
                Misc/
                Plugin2.class.php
        Views/
            Common/
            Layouts/
                Layout1.php
                Layout2.php
            Misc/
        www/
            assets/
                css/
                img/
                js/
                pugins/
            .htaccess
            index.php

### Config

The `Config` folder is where you put your configuration files. Pandore considers `default.ini` as the default configuration file. However, Pandore will load `$_SERVER['HTTP_HOST'].ini` if it exists. This allows you to share code between different domains.

A configuration file is composed of :
- an environment section which defines some general options such as whether the application is running in debug or whether the application has to produce log entries,
- a general section which defines the default layout name and the default module name,
- a data source section which allows you to add your data sources and their DSN information,
- a plugin section which defines some system plugins and which allows you to add your own ones.

You should name your main data source `Default` to make it directly gettable in your project files.

Plugins ordering is very important and you should take care of it. The first plugin declared will be the first plugin considered in the dispatch process. Please note that system plugins are all considered after project plugins. That means that the last considered plugin is always the exceptions handler as long as you let plugins system in the same way that it is described in the default configuration file below.

    [Environment]

    debug = On
    log = On
    logHttpCode = Off

    [General]

    layout = Index
    module = Home

    [Datasource]

    ; sources[Default] = DATASOURCE_NAME_1
    ; dsns[Default] = dbms:__DBMS__+host:__HOST__+dbname:__DB_NAME__+username:__USERNAME__+password:__PASSWORD__

    ; sources[NAME_2] = DATASOURCE_NAME_2
    ; dsns[Default] = dbms:__DBMS__+host:__HOST__+dbname:__DB_NAME__+username:__USERNAME__+password:__PASSWORD__

    ; sources[NAME_3] = DATASOURCE_NAME_3
    ; dsns[Default] = dbms:__DBMS__+host:__HOST__+dbname:__DB_NAME__+username:__USERNAME__+password:__PASSWORD__

    [Plugins]

    ; plugins[] = PLUGIN_NAME_1
    ; plugins[] = PLUGIN_NAME_2
    ; plugins[] = PLUGIN_NAME_3

    systemPlugins[] = LayoutRenderer
    systemPlugins[] = ExceptionsHandler

### DataSources

The `DataSources` folder is where you put your own data sources. [Mercure](https://github.com/Aldrien-/Mercure) can be one of those you use. You can also use one of your own. The minimal requirement you need to respect is to implement the `IDataSource` interface.

### Exceptions

The `Exceptions` folder is where you put your own exceptions.
    
    namespace Kernel\Exceptions;

    class ExampleException extends \Exception
    {
      public function __construct($message = null, $code = 0)
      {
        parent::__construct($message, $code);
      }
    }

### Models

The `Models` folder is where you put your models. Generally speaking, a model is an object representation of a table of your database but you are free to do whatever you want with them. A model inherited from the `Model` class is ensured to be compliant with the data saving mechanism.
    
    namespace Project\Models;

    use Kernel\Core as Core;

    class Example extends Core\Model
    {
    }

### Modules

The `Modules` folder is where you put your modules. Modules are objects which implement the main part of the requests dispatch process. They are designed to be mainly composed of actions and let you modify the dispatch process by overriding some methods such as `init`, `preExecute`, `postExecute` and `finalize`.

The suffix `Action` must be added to each method you want to define as an action as long as you don't override the `formatActionName` method.

One action has already been defined : `defaultAction`. This action leads to an error 404 but you can, of course, override it. The default action is called by Pandore mechanism when no action is defined in the URI.

You can access to different kind of objects from a module method:
- the configuration object to get configuration values,
- the plugins manager which allows to execute plugins' methods using `notify`,
- the request which provides a secured access to the request's data (`GET`, `POST`, `COOKIE`, ...),
- the response which mainly allows to manage HTTP status code,
- the action view to set some values used in the associated view file

It's also possible to notify plugins with the `notify` module's method.

Modules have a method called `helper` which allows them to be dynamically loaded. These `Helpers` have to extend the `Helper` abstract class and are designed to help you organize your code.

The last but not least feature is the control modules give you to manage the rendering process whether at module or action level. You are able to:
- use another layout, 
- change how actions and view files are linked,
- set values to partial and action (sub)views,
- disable an action rendering,
- disable the layout rendering,
- define your own actions rendering process.

    namespace Project\Modules\Example;

    use Kernel\Core as Core;

    class Example extends Core\Module
    {
    }

### Plugins

The `Plugins` folder is where you put your plugins. Plugins are usually created to be used during the requests dispatch process through their `preDispatch` and `postDispatch` methods. However, you can create plugins which are not, or not only, part of the requests dispatch process and notify them yourself when it's necessary.
    
    namespace Project\Plugins\Example;

    use Kernel\Core as Core;

    class Example extends Core\Plugin
    {
    }

### Views

The `Views` folder is where you put your layouts and views components not related to actions. These files are mainly composed of HTML. From them, you can get previously set values and include other (sub)views.

### www

The `www` folder is where you put your assets. This folder also includes `index.php` which initializes, starts and runs your application and an `.htaccess` file which is the bridge between urls (through url rewriting) and the routing mechanism. Be careful if you need to modify these two files.

## License

Copyright 2011-2013 [Alexandre Lemire](https://github.com/Aldrien-) & [Yannick Cladière](https://github.com/Yannz)

Licensed under the MIT license.