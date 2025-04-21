<?php

add_action('phpmailer_init', function ($phpmailer) {
    $host = get_option('ases_smtp_host');
    $user = get_option('ases_smtp_user');
    $pass = get_option('ases_smtp_pass');

    // Exit if required settings are missing
    if (empty($host) || empty($user) || empty($pass)) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 587;
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->SMTPSecure = 'tls';

    // Optional: set default From address
    if (empty($phpmailer->From)) {
        $phpmailer->From = $user;
    }
    if (empty($phpmailer->FromName)) {
        $phpmailer->FromName = 'Amazon SES SMTP Plugin';
    }
});
