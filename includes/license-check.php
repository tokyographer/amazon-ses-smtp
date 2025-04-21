<?php

function ases_is_license_valid() {
    $license_data = get_option('ases_license_data');

    // Check if license data is complete
    if (
        !isset($license_data['token']) ||
        !isset($license_data['expires'])
    ) {
        return false;
    }

    // Check if license is expired
    if (time() > intval($license_data['expires'])) {
        return false;
    }

    // Optional: check if domain matches the one license is bound to
    /*
    if (!empty($license_data['domain']) && $_SERVER['HTTP_HOST'] !== $license_data['domain']) {
        return false;
    }
    */

    return true;
}
