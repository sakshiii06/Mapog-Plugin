<?php
function render_mapog_block($attributes) {
    // Ensure a selected map URL exists
    if (isset($attributes['selectedMap']) && !empty($attributes['selectedMap'])) {
        $selected_map = esc_url($attributes['selectedMap']);
        
        // Generate a unique ID for each block instance
        $unique_id = uniqid('mapog-iframe-container-');

        return '<div class="mapog-embed" id="' . $unique_id . '" data-selected-map="' . $selected_map . '">
                    <iframe src="' . $selected_map . '" width="100%" height="500px" style="border:none;"></iframe>
                </div>';
    }

    // If no map is selected, return a message
    return '<div class="mapog-embed"><p>No map selected.</p></div>';
}
