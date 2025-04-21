<?php
function ases_is_license_valid() {
    $license_data = get_option('ases_license_data');

    if (!isset($license_data['token']) || !isset($license_data['expires'])) {
        return false;
    }

    if (time() > intval($license_data['expires'])) {
        return false;
    }

    return true;
}