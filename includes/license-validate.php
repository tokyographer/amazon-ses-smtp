<?php

function ases_validate_license($email, $license_key, $product_id = 'easy-smtp') {
    $domain = $_SERVER['SERVER_NAME'] ?? parse_url(home_url(), PHP_URL_HOST);
    
    $response = wp_remote_post('https://licenses.mediared.es/validate.php', [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'email' => $email,
            'license_key' => $license_key,
            'product_id' => $product_id,
            'domain' => $domain,
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return ['success' => false, 'error' => $response->get_error_message()];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['valid']) || !$body['valid']) {
        return ['success' => false, 'error' => $body['error'] ?? 'Invalid license'];
    }

    $token = $body['token'];
    $payload = ases_decode_jwt($token);

    if (!$payload || !isset($payload['exp'])) {
        return ['success' => false, 'error' => 'Token invalid or expired'];
    }

    update_option('ases_license_data', [
        'token'       => $token,
        'expires'     => $payload['exp'],
        'email'       => $email,
        'license_key' => $license_key,
        'domain'      => $payload['domain'] ?? '',
        'product_id'  => $payload['product_id'] ?? '',
        'payload'     => $payload, // for future-proofing
    ]);
    do_action('ases_license_validated', $payload);
    
    return ['success' => true, 'expires' => $payload['exp']];
}

function ases_decode_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    $payload = base64_decode(strtr($parts[1], '-_', '+/'));
    return json_decode($payload, true);
}