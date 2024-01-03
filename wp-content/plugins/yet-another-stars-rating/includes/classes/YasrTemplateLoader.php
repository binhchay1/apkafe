<?php

/**
 * @author Dario Curvino <@dudo>
 * @since  3.3.0
 */
class YasrTemplateLoader extends Gamajo_Template_Loader {
    /**
     * Prefix for filter names.
     *
     * @since 1.0.0
     * @type string
     */
    protected $filter_prefix = 'yasr_';

    /**
     * Directory name where custom templates for this plugin should be found in the theme.
     *
     * @since 3.3.0
     * @type string
     */
    protected $theme_template_directory = 'yasr';

    /**
     * Reference to the root directory path of this plugin.
     *
     * @since 3.3.0
     * @type string
     */
    protected $plugin_directory = YASR_ABSOLUTE_PATH;

    /**
     * Directory name of where the templates are stored into the plugin.
     *
     * @since 3.3.0
     * @var string
     */
    protected $plugin_template_directory = 'templates';
}