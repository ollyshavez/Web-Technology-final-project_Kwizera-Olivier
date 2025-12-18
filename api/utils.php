<?php
// XSS Protection Helper
function sanitize_output($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_output($value);
        }
    } elseif (is_string($data)) {
        // Convert special chars to HTML entities to prevent script execution
        // ENT_QUOTES handles both single and double quotes
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

