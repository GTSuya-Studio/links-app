<?php
// ============================================================
// includes/lang/fr.php — French translations
//
// To add a new language: copy this file to includes/lang/<code>.php
// (e.g. es.php), translate every value, and update '_meta.name'.
// The new language appears automatically in Settings > Customize > Display.
// ============================================================
return [
    '_meta' => ['name' => 'Français'],

    // --- Common -------------------------------------------------
    'search_placeholder'     => 'Rechercher un lien…',
    'clear'                  => 'Effacer',
    'cancel'                 => 'Annuler',
    'save'                   => 'Enregistrer',
    'add'                    => 'Ajouter',
    'choose'                 => '— Choisir —',
    'field_name'             => 'Nom *',
    'color_label'            => 'Couleur du trait',
    'theme_toggle_title'     => 'Changer le thème',
    'view_site'              => 'Voir le site',
    'administration'         => 'Administration',
    'settings'               => 'Paramètres',
    'logout'                 => 'Déconnexion',
    'confirm_logout'         => 'Se déconnecter ?',
    'search_results_for'     => 'Résultats pour « :query »',
    'search_results_count'   => ':count lien(s) trouvé(s)',
    'no_results'             => 'Aucun résultat.',
    'edit'                   => 'Modifier',

    // --- Public site ---------------------------------------------
    'no_links_yet'           => "Aucun lien pour l'instant.",
    'add_from_admin'         => "Ajoutez-en depuis l'administration.",
    'pin_protected_message'  => 'Cette catégorie est protégée. Entrez le code PIN pour y accéder.',
    'pin_incorrect'          => 'Code PIN incorrect.',
    'connection_error_retry' => 'Erreur de connexion. Réessayez.',
    'no_subcategories'       => 'Aucune sous-catégorie dans cette catégorie.',
    'no_links'                => 'Aucun lien.',

    // --- Admin: navigation & actions -------------------------------
    'go_to_category'         => 'Aller à la catégorie',
    'add_category'            => '+ Ajouter une catégorie',
    'add_subcategory'         => '+ Ajouter une sous-catégorie',
    'add_link'                => '+ Ajouter un lien',
    'start_add_category'      => 'Commencez par ajouter une catégorie dans le menu de gauche.',
    'no_subcategories_admin'  => 'Aucune sous-catégorie.',
    'order_saved'              => 'Ordre sauvegardé',
    'loading'                  => 'Chargement…',
    'confirm_delete_category'  => "Supprimer ':name' et tout son contenu ?",
    'confirm_delete_subcategory' => "Supprimer ':name' et ses liens ?",
    'confirm_delete_link'      => "Supprimer ':name' ?",
    'drag_reorder_category'    => 'Glisser pour réordonner',
    'drag_reorder'             => 'Réordonner',

    // --- Admin: modals -------------------------------------------
    'modal_new_category'       => 'Nouvelle catégorie',
    'modal_edit_category'      => 'Modifier la catégorie',
    'modal_new_subcategory'    => 'Nouvelle sous-catégorie',
    'modal_edit_subcategory'   => 'Modifier la sous-catégorie',
    'modal_new_link'           => 'Nouveau lien',
    'modal_edit_link'          => 'Modifier le lien',
    'field_category'           => 'Catégorie *',
    'field_subcategory'        => 'Sous-catégorie *',
    'field_url'                => 'URL *',
    'field_title'              => 'Titre *',
    'field_description'        => 'Description',
    'field_description_optional' => '(optionnel)',
    'hint_color_menu'           => 'Trait vertical dans le menu',
    'hint_color_subcat'         => 'Couleur du trait vertical gauche',
    'lock_pin_label'            => 'Verrouiller avec un code PIN',
    'lock_pin_hint'             => 'Le code PIN sera demandé pour accéder au contenu de cette catégorie sur le site public. Définissez le PIN dans Paramètres.',
    'auto_meta_hint'            => 'Le titre et la description sont récupérés automatiquement.',
    'placeholder_category_name'    => 'Ex : Développement',
    'placeholder_subcategory_name' => 'Ex : Outils en ligne',
    'placeholder_link_title'       => 'Ex : GitHub',
    'placeholder_link_desc'        => 'Courte description…',

    // --- Login -----------------------------------------------------
    'login_subtitle'          => "Interface d'administration",
    'password_label'          => 'Mot de passe',
    'login_button'            => 'Se connecter',
    'back_to_site'            => '← Retour au site',
    'error_wrong_password'    => 'Mot de passe incorrect.',

    // --- Settings: tabs ---------------------------------------------
    'tab_personalize'          => 'Personnaliser',
    'tab_personalize_sub'      => 'Apparence et informations du site',
    'tab_security'             => 'Sécurité',
    'tab_security_sub'         => 'Mot de passe et code PIN',
    'tab_system'               => 'Système',
    'tab_system_sub'           => 'Informations techniques',

    // --- Settings: site info card -----------------------------------
    'card_site_info'           => 'Informations du site',
    'field_site_icon'          => 'Icône du site',
    'site_icon_hint'           => 'PNG, JPG ou SVG, 2 Mo maximum. Utilisée dans le menu latéral, la page de connexion, et comme favicon.',
    'remove_icon'              => "Supprimer l'icône",
    'field_site_title'         => 'Titre du site',
    'site_title_hint'          => "Affiché dans le menu latéral et le titre de l'onglet.",
    'field_site_subtitle'      => 'Sous-titre',
    'placeholder_site_subtitle' => 'Mes marque-pages',

    // --- Settings: display card --------------------------------------
    'card_display'              => 'Affichage',
    'show_descriptions_label'   => 'Afficher les descriptions des liens',
    'show_descriptions_hint'    => 'Si décoché, seuls le favicon et le titre sont affichés.',
    'public_search_label'       => 'Afficher le champ de recherche sur le site public',
    'public_search_hint'        => 'Les catégories protégées par PIN sont toujours exclues des résultats, même si activé.',
    'language_label'             => "Langue de l'interface",
    'language_hint'              => 'Langue affichée pour l\'ensemble du site (public et administration).',

    // --- Settings: password card --------------------------------------
    'card_change_password'     => 'Changer le mot de passe',
    'current_password'         => 'Mot de passe actuel',
    'new_password'             => 'Nouveau mot de passe',
    'new_password_hint'        => 'Minimum 8 caractères.',
    'confirm_password'         => 'Confirmer',
    'change_password_button'   => 'Changer le mot de passe',

    // --- Settings: PIN card ---------------------------------------------
    'card_pin'                  => 'Code PIN (catégories protégées)',
    'pin_description'           => "Ce code à 4 chiffres est demandé sur le site public pour accéder aux catégories marquées comme verrouillées (option disponible dans le modal \"Modifier la catégorie\"). Il est commun à toutes les catégories protégées.",
    'pin_duration_label'        => 'Durée de validité après saisie du PIN',
    'pin_duration_hint'         => 'Passé ce délai, le PIN sera redemandé pour la catégorie concernée. Maximum 1440 (24h).',
    'minutes'                   => 'minutes',
    'pin_new_label'             => 'Nouveau code PIN',
    'pin_label'                 => 'Code PIN',
    'pin_hint_digits'           => 'Exactement 4 chiffres.',
    'change_pin_button'         => 'Changer le code PIN',
    'set_pin_button'            => 'Définir le code PIN',
    'pin_active'                => 'Code PIN actif',
    'pin_none'                  => 'Aucun code PIN défini',
    'remove_pin_button'         => 'Supprimer le code PIN',
    'confirm_remove_pin'        => 'Supprimer le code PIN ? Toutes les catégories verrouillées seront déverrouillées automatiquement.',

    // --- Settings: system card --------------------------------------------
    'card_system_info'          => 'Informations système',
    'sys_php'                   => 'PHP',
    'sys_base_url'              => 'Base URL',
    'sys_mysql'                 => 'MySQL',
    'sys_default_password'      => 'Mot de passe par défaut',
    'default_password_warning'  => 'Vous utilisez le mot de passe par défaut "admin". Changez-le immédiatement !',
    'custom_password_ok'        => 'Mot de passe personnalisé',
    'card_export'               => 'Export des données',
    'export_description'        => 'Téléchargez une copie de toutes vos catégories, sous-catégories et liens au format JSON. Ne contient aucune information sensible (mots de passe, code PIN).',
    'download_export'           => "Télécharger l'export JSON",

    // --- Flash messages (settings) -----------------------------------------
    'msg_settings_saved'        => 'Paramètres enregistrés.',
    'msg_icon_removed'          => 'Icône supprimée.',
    'msg_password_changed'      => 'Mot de passe modifié avec succès.',
    'msg_pin_saved'              => 'Code PIN enregistré.',
    'msg_pin_removed'            => 'Code PIN supprimé. Les catégories verrouillées ont été déverrouillées.',
    'msg_pin_duration_saved'     => 'Durée de validité enregistrée.',
    'err_file_too_large'        => 'Le fichier dépasse la taille maximale de 2 Mo.',
    'err_unsupported_format'    => 'Format non supporté. Utilisez PNG, JPG ou SVG.',
    'err_file_mismatch'         => 'Le contenu du fichier ne correspond pas à son extension.',
    'err_file_save'             => "Erreur lors de l'enregistrement du fichier.",
    'err_wrong_current_password' => 'Mot de passe actuel incorrect.',
    'err_password_too_short'     => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
    'err_passwords_mismatch'     => 'Les mots de passe ne correspondent pas.',
    'err_pin_format'             => 'Le code PIN doit contenir exactement 4 chiffres.',
    'err_pin_mismatch'           => 'Les deux codes PIN ne correspondent pas.',
    'err_pin_duration_range'     => 'La durée doit être comprise entre 1 et 1440 minutes (24h).',

    // --- Flash messages (admin CRUD) ----------------------------------------
    'msg_cat_added'    => 'Catégorie ajoutée.',
    'msg_cat_edited'   => 'Catégorie modifiée.',
    'msg_cat_deleted'  => 'Catégorie supprimée.',
    'msg_sub_added'    => 'Sous-catégorie ajoutée.',
    'msg_sub_edited'   => 'Sous-catégorie modifiée.',
    'msg_sub_deleted'  => 'Sous-catégorie supprimée.',
    'msg_link_added'   => 'Lien ajouté.',
    'msg_link_edited'  => 'Lien modifié.',
    'msg_link_deleted' => 'Lien supprimé.',
    'err_name_required'   => 'Le nom est requis.',
    'err_fields_required'  => 'Champs requis.',

    // --- verify-pin.php API messages -----------------------------------------
    'err_server_config'      => 'Configuration serveur incomplète (SESSION_SECRET manquant dans config.php)',
    'err_method_not_allowed' => 'Méthode non autorisée',
    'err_invalid_request'    => 'Requête invalide',
    'err_invalid_category'   => 'Catégorie invalide',

    // --- fetch-meta.php API messages -------------------------------------------
    'err_unauthorized'        => 'Non autorisé',
    'err_invalid_url'         => 'URL invalide',
    'err_protocol_not_allowed' => 'Protocole non autorisé',
    'err_cannot_resolve_domain' => 'Impossible de résoudre ce domaine',
    'err_address_not_allowed'  => "Cette adresse n'est pas autorisée",
    'err_cannot_fetch_page'    => 'Impossible de récupérer la page',
    'err_csrf_invalid'         => 'Jeton CSRF invalide.',
];
