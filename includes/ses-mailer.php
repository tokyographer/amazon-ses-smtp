<?php

add_action('phpmailer_init', function ($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'email-smtp.us-east-1.amazonaws.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 587;
    $phpmailer->Username   = get_option('ases_smtp_user');
    $phpmailer->Password   = get_option('ases_smtp_pass');
    $phpmailer->SMTPSecure = 'tls';
});