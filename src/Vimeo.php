<?php
/**
 * vimeoAPI plugin for Craft CMS 3.x
 *
 * Use the Vimeo API to pull in videos
 *
 * @link      https://iamdangavin.com
 * @copyright Copyright (c) 2020 Dan Gavin
 */

namespace iamdangavin\vimeo;


use Craft;
use craft\base\Plugin;
use craft\services\Path;
use craft\web\twig\variables\CraftVariable;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;
use yii\base\Event;
use iamdangavin\vimeo\services\getVimeo as getVimeoService;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    BIG Communications
 * @package   VimeoAPI
 * @since     1.0.0
 *
 */
class Vimeo extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * VimeoAPI::$plugin
     *
     * @var VimeoAPI
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '2.0.2';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = false; // Using Cp::EVENT_REGISTER_CP_NAV_ITEMS below

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * VimeoAPI::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Do something after we're installed
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('vimeo', getVimeoService::class);
            }
        );
        
/*
	    Event::on(
	        Cp::class,
	        Cp::EVENT_REGISTER_CP_NAV_ITEMS,
	        function(RegisterCpNavItemsEvent $event) {
	            $event->navItems[] = [
	                'url' => 'vimeo-api',
	                'label' => 'Vimeo API'
	            ];
	        }
	    );

	    Event::on(
	        UrlManager::class,
	        UrlManager::EVENT_REGISTER_CP_URL_RULES,
	        function(RegisterUrlRulesEvent $event) {
	            $event->rules['vimeo-api'] = 'vimeo-api/settings';
	        }
	    );
*/

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'vimeo',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
	protected function createSettingsModel()
    {
        return new \iamdangavin\vimeo\models\Settings();
    }
    
	protected function settingsHtml()
    {
        return \Craft::$app->getView()->renderTemplate('vimeo/settings', [
            'settings' => $this->getSettings()
        ]);
    }

}
