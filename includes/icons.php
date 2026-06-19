<?php
// ============================================================
// includes/icons.php — Centralized icon library
//
// Every icon used across the site is defined once here as a set of
// inner SVG shapes (no outer <svg> tag), then rendered through icon()
// with a consistent viewBox, stroke style, and line caps. This keeps
// every icon visually harmonized and avoids the same shape being
// hand-copied at different sizes throughout the templates.
//
// Usage: icon('trash', 14) renders the trash icon at 14px
// ============================================================

$ICONS = [
    // --- Actions --------------------------------------------------
    'edit'  => ['stroke' => 2, 'paths' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>'],
    'trash' => ['stroke' => 2, 'paths' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>'],
    'chevron-right' => ['stroke' => 2, 'paths' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>'],
    'download' => ['stroke' => 1.8, 'paths' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'],

    // --- Navigation / chrome ----------------------------------------
    'eye'    => ['stroke' => 1.5, 'paths' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>'],
    'lock'   => ['stroke' => 1.5, 'paths' => '<rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
    'gear'   => ['stroke' => 1.5, 'paths' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    'logout' => ['stroke' => 1.5, 'paths' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>'],

    // --- Empty states ------------------------------------------------
    'bookmark' => ['stroke' => 1.5, 'paths' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>'],
    'folder'   => ['stroke' => 1.5, 'paths' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'],

    // --- Settings card headers ----------------------------------------
    'globe'       => ['stroke' => 1.8, 'paths' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
    'keypad'      => ['stroke' => 1.8, 'paths' => '<rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="7" y1="15" x2="7" y2="15.01"/><line x1="12" y1="15" x2="12" y2="15.01"/><line x1="17" y1="15" x2="17" y2="15.01"/>'],
    'info-circle' => ['stroke' => 1.8, 'paths' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="8.01"/>'],

    // --- Theme toggle ----------------------------------------------
    'sun'  => ['stroke' => 1.5, 'paths' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3.5"/><line x1="12" y1="5.5" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="18.5"/><line x1="5.5" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="18.5" y2="12"/><line x1="7.4" y1="7.4" x2="6.3" y2="6.3"/><line x1="17.7" y1="17.7" x2="16.6" y2="16.6"/><line x1="16.6" y1="7.4" x2="17.7" y2="6.3"/><line x1="6.3" y1="17.7" x2="7.4" y2="16.6"/>'],
    'moon' => ['stroke' => 1.5, 'paths' => '<circle cx="12" cy="12" r="10"/><path d="M14.5 9a5 5 0 0 1-5 8 5 5 0 0 0 5-8z"/>'],

    // --- Status / feedback ----------------------------------------
    'check'   => ['stroke' => 2, 'paths' => '<polyline points="20 6 9 17 4 12"/>'],
    'close'   => ['stroke' => 2, 'paths' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>'],
    'warning' => ['stroke' => 2, 'paths' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12" y2="17.01"/>'],

    // --- Drag handle (filled dots, not an outline icon) ---------------
    'grip' => ['fill' => true, 'paths' => '<circle cx="9" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="15" cy="18" r="1.4"/>'],
];

// Renders an icon by name at the given size. $extraAttrs is a raw string
// appended to the <svg> tag (e.g. 'class="icon-sun"' for the theme toggle).
function icon(string $name, int $size = 19, string $extraAttrs = ''): string {
    global $ICONS;
    if (!isset($ICONS[$name])) return '';
    $def = $ICONS[$name];
    if ($extraAttrs !== '') $extraAttrs = ' ' . $extraAttrs;

    if (!empty($def['fill'])) {
        return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"' . $extraAttrs . '>' . $def['paths'] . '</svg>';
    }

    $stroke = $def['stroke'] ?? 1.5;
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="' . $stroke . '" stroke-linecap="round" stroke-linejoin="round"' . $extraAttrs . '>' . $def['paths'] . '</svg>';
}
