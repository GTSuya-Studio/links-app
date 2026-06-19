<?php
// ============================================================
// includes/lang/en.php — English translations
//
// To add a new language: copy this file to includes/lang/<code>.php
// (e.g. es.php), translate every value, and update '_meta.name'.
// The new language appears automatically in Settings > Customize > Display.
// ============================================================
return [
    '_meta' => ['name' => 'English'],

    // --- Common -------------------------------------------------
    'search_placeholder'     => 'Search for a link…',
    'clear'                  => 'Clear',
    'cancel'                 => 'Cancel',
    'save'                   => 'Save',
    'add'                    => 'Add',
    'choose'                 => '— Choose —',
    'field_name'             => 'Name *',
    'color_label'            => 'Line color',
    'theme_toggle_title'     => 'Toggle theme',
    'view_site'              => 'View site',
    'administration'         => 'Administration',
    'settings'               => 'Settings',
    'logout'                 => 'Log out',
    'confirm_logout'         => 'Log out?',
    'search_results_for'     => 'Results for ":query"',
    'search_results_count'   => ':count link(s) found',
    'no_results'             => 'No results.',
    'edit'                   => 'Edit',

    // --- Public site ---------------------------------------------
    'no_links_yet'           => 'No links yet.',
    'add_from_admin'         => 'Add some from the admin panel.',
    'pin_protected_message'  => 'This category is protected. Enter the PIN code to access it.',
    'pin_incorrect'          => 'Incorrect PIN code.',
    'connection_error_retry' => 'Connection error. Please try again.',
    'no_subcategories'       => 'No subcategories in this category.',
    'no_links'                => 'No links.',

    // --- Admin: navigation & actions -------------------------------
    'go_to_category'         => 'Go to category',
    'add_category'            => '+ Add category',
    'add_subcategory'         => '+ Add subcategory',
    'add_link'                => '+ Add link',
    'start_add_category'      => 'Start by adding a category from the menu on the left.',
    'no_subcategories_admin'  => 'No subcategories.',
    'order_saved'              => 'Order saved',
    'loading'                  => 'Loading…',
    'confirm_delete_category'  => "Delete ':name' and all its content?",
    'confirm_delete_subcategory' => "Delete ':name' and its links?",
    'confirm_delete_link'      => "Delete ':name'?",
    'drag_reorder_category'    => 'Drag to reorder',
    'drag_reorder'             => 'Reorder',

    // --- Admin: modals -------------------------------------------
    'modal_new_category'       => 'New category',
    'modal_edit_category'      => 'Edit category',
    'modal_new_subcategory'    => 'New subcategory',
    'modal_edit_subcategory'   => 'Edit subcategory',
    'modal_new_link'           => 'New link',
    'modal_edit_link'          => 'Edit link',
    'field_category'           => 'Category *',
    'field_subcategory'        => 'Subcategory *',
    'field_url'                => 'URL *',
    'field_title'              => 'Title *',
    'field_description'        => 'Description',
    'field_description_optional' => '(optional)',
    'hint_color_menu'           => 'Vertical bar in the menu',
    'hint_color_subcat'         => 'Color of the left vertical bar',
    'lock_pin_label'            => 'Lock with a PIN code',
    'lock_pin_hint'             => "The PIN code will be required to access this category's content on the public site. Set the PIN in Settings.",
    'auto_meta_hint'            => 'The title and description are fetched automatically.',
    'placeholder_category_name'    => 'E.g.: Development',
    'placeholder_subcategory_name' => 'E.g.: Online tools',
    'placeholder_link_title'       => 'E.g.: GitHub',
    'placeholder_link_desc'        => 'Short description…',

    // --- Login -----------------------------------------------------
    'login_subtitle'          => 'Admin interface',
    'password_label'          => 'Password',
    'login_button'            => 'Log in',
    'back_to_site'            => '← Back to site',
    'error_wrong_password'    => 'Incorrect password.',

    // --- Settings: tabs ---------------------------------------------
    'tab_personalize'          => 'Customize',
    'tab_personalize_sub'      => 'Site appearance and information',
    'tab_security'             => 'Security',
    'tab_security_sub'         => 'Password and PIN code',
    'tab_system'               => 'System',
    'tab_system_sub'           => 'Technical information',

    // --- Settings: site info card -----------------------------------
    'card_site_info'           => 'Site information',
    'field_site_icon'          => 'Site icon',
    'site_icon_hint'           => 'PNG, JPG or SVG, 2 MB max. Used in the sidebar, the login page, and as the favicon.',
    'remove_icon'              => 'Remove icon',
    'field_site_title'         => 'Site title',
    'site_title_hint'          => 'Shown in the sidebar and the tab title.',
    'field_site_subtitle'      => 'Subtitle',
    'placeholder_site_subtitle' => 'My bookmarks',

    // --- Settings: display card --------------------------------------
    'card_display'              => 'Display',
    'show_descriptions_label'   => 'Show link descriptions',
    'show_descriptions_hint'    => 'If unchecked, only the favicon and title are shown.',
    'public_search_label'       => 'Show the search field on the public site',
    'public_search_hint'        => 'PIN-protected categories are always excluded from results, even when enabled.',
    'language_label'             => 'Interface language',
    'language_hint'              => 'Language used across the whole site (public and admin).',

    // --- Settings: password card --------------------------------------
    'card_change_password'     => 'Change password',
    'current_password'         => 'Current password',
    'new_password'             => 'New password',
    'new_password_hint'        => 'At least 8 characters.',
    'confirm_password'         => 'Confirm',
    'change_password_button'   => 'Change password',

    // --- Settings: PIN card ---------------------------------------------
    'card_pin'                  => 'PIN code (protected categories)',
    'pin_description'           => 'This 4-digit code is requested on the public site to access categories marked as locked (option available in the "Edit category" modal). It is shared by all protected categories.',
    'pin_duration_label'        => 'Validity duration after entering the PIN',
    'pin_duration_hint'         => 'After this delay, the PIN will be requested again for that category. Maximum 1440 (24h).',
    'minutes'                   => 'minutes',
    'pin_new_label'             => 'New PIN code',
    'pin_label'                 => 'PIN code',
    'pin_hint_digits'           => 'Exactly 4 digits.',
    'change_pin_button'         => 'Change PIN code',
    'set_pin_button'            => 'Set PIN code',
    'pin_active'                => 'PIN code active',
    'pin_none'                  => 'No PIN code set',
    'remove_pin_button'         => 'Remove PIN code',
    'confirm_remove_pin'        => 'Remove the PIN code? All locked categories will be automatically unlocked.',

    // --- Settings: system card --------------------------------------------
    'card_system_info'          => 'System information',
    'sys_php'                   => 'PHP',
    'sys_base_url'              => 'Base URL',
    'sys_mysql'                 => 'MySQL',
    'sys_default_password'      => 'Default password',
    'default_password_warning'  => 'You are using the default password "admin". Change it immediately!',
    'custom_password_ok'        => 'Custom password',
    'card_export'               => 'Data export',
    'export_description'        => 'Download a copy of all your categories, subcategories, and links in JSON format. Contains no sensitive information (passwords, PIN code).',
    'download_export'           => 'Download JSON export',

    // --- Flash messages (settings) -----------------------------------------
    'msg_settings_saved'        => 'Settings saved.',
    'msg_icon_removed'          => 'Icon removed.',
    'msg_password_changed'      => 'Password changed successfully.',
    'msg_pin_saved'              => 'PIN code saved.',
    'msg_pin_removed'            => 'PIN code removed. Locked categories have been unlocked.',
    'msg_pin_duration_saved'     => 'Validity duration saved.',
    'err_file_too_large'        => 'The file exceeds the 2 MB size limit.',
    'err_unsupported_format'    => 'Unsupported format. Use PNG, JPG, or SVG.',
    'err_file_mismatch'         => "The file's content doesn't match its extension.",
    'err_file_save'             => 'Error while saving the file.',
    'err_wrong_current_password' => 'Current password is incorrect.',
    'err_password_too_short'     => 'The new password must be at least 8 characters long.',
    'err_passwords_mismatch'     => "The passwords don't match.",
    'err_pin_format'             => 'The PIN code must be exactly 4 digits.',
    'err_pin_mismatch'           => "The two PIN codes don't match.",
    'err_pin_duration_range'     => 'The duration must be between 1 and 1440 minutes (24h).',

    // --- Flash messages (admin CRUD) ----------------------------------------
    'msg_cat_added'    => 'Category added.',
    'msg_cat_edited'   => 'Category updated.',
    'msg_cat_deleted'  => 'Category deleted.',
    'msg_sub_added'    => 'Subcategory added.',
    'msg_sub_edited'   => 'Subcategory updated.',
    'msg_sub_deleted'  => 'Subcategory deleted.',
    'msg_link_added'   => 'Link added.',
    'msg_link_edited'  => 'Link updated.',
    'msg_link_deleted' => 'Link deleted.',
    'err_name_required'   => 'Name is required.',
    'err_fields_required'  => 'Required fields.',

    // --- verify-pin.php API messages -----------------------------------------
    'err_server_config'      => 'Incomplete server configuration (missing SESSION_SECRET in config.php)',
    'err_method_not_allowed' => 'Method not allowed',
    'err_invalid_request'    => 'Invalid request',
    'err_invalid_category'   => 'Invalid category',

    // --- fetch-meta.php API messages -------------------------------------------
    'err_unauthorized'        => 'Unauthorized',
    'err_invalid_url'         => 'Invalid URL',
    'err_protocol_not_allowed' => 'Protocol not allowed',
    'err_cannot_resolve_domain' => 'Unable to resolve this domain',
    'err_address_not_allowed'  => 'This address is not allowed',
    'err_cannot_fetch_page'    => 'Unable to fetch the page',
    'err_csrf_invalid'         => 'Invalid CSRF token.',
];
