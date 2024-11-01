<?php
if (!function_exists('wpvc_overview_view')) {
    /**
     * Setting Page.
     */
    function wpvc_overview_view()
    {
        ?>
		<div id="wpvc-overview-page" data-url="<?php echo esc_url(site_url('index.php')); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Overview view', 'voting-contest') . '</h2>');
}

if (!function_exists('wpvc_settings_view')) {
    /**
     * Setting Page.
     */
    function wpvc_settings_view()
    {
        ?>
		<div id="wpvc-settings-page" data-url="<?php echo esc_url(site_url('index.php')); ?>" data-originurl="<?php echo esc_url(site_url()); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Settings view', 'voting-contest') . '</h2>');
}

if (!function_exists('wpvc_category_view')) {
    /**
     * Setting Page.
     */
    function wpvc_category_view()
    {
        ?>
		<div id="wpvc-category-page" data-url="<?php echo esc_url(site_url('index.php')); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Category view', 'voting-contest') . '</h2>');
}


if (!function_exists('wpvc_custom_fields_view')) {
    /**
     * Setting Page.
     */
    function wpvc_custom_fields_view()
    {
        ?>
		<div id="wpvc-custom-field-page" data-url="<?php echo esc_url(site_url('index.php')); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Custom fields', 'voting-contest') . '</h2>');
}


if (!function_exists('wpvc_voting_license')) {
    /**
     * Setting Page.
     *
     * @param mixed $license License code.
     * @param mixed $status Status.
     */
    function wpvc_voting_license($license, $status)
    {

        if (isset($_REQUEST['license']) && $_REQUEST['license'] == 'invalid') {
            echo '<div id="message" class="error"><p>Invalid License key</p></div>';
        }
        if (isset($_REQUEST['license']) && $_REQUEST['license'] == 'valid') {
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>License key validated</p></div>';
        }

        ?>
		<div id="wpvc-voting-license" data-url="<?php echo esc_url(site_url('index.php')); ?>" data-key="<?php echo esc_attr($license); ?>" data-status="<?php echo esc_attr($status); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting License', 'voting-contest') . '</h2>');
}

if (!function_exists('wpvc_voting_vote_logs_view')) {
    /**
     * Setting Page.
     */
    function wpvc_voting_vote_logs_view()
    {
        ?>
		<div id="wpvc-voting-votes-logs" data-url="<?php echo esc_url(site_url('index.php')); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Logs', 'voting-contest') . '</h2>');
}


if (!function_exists('wpvc_migration_view')) {
    /**
     * Setting Page.
     */
    function wpvc_migration_view()
    {
        ?>
		<div id="wpvc-migration-page" data-url="<?php echo esc_url(site_url('index.php')); ?>"></div>
	<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Migration view', 'voting-contest') . '</h2>');
}

if (!function_exists('wpvc_upgrade_text')) {
    /**
     * Setting Page.
     */
    function wpvc_upgrade_text()
    {
        ?>
		<div id="wpvc-upgrade-page" data-url="<?php echo esc_url(site_url('index.php')); ?>" data-update_url="<?php echo esc_url(admin_url('update-core.php')); ?>"></div>
<?php
    }
} else {
    die('<h2>' . esc_html_e('Failed to load Voting Upgrade view', 'voting-contest') . '</h2>');
}
