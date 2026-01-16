<?php
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function verify_csrf($token) {
    return hash_equals($_SESSION["csrf_token"], $token ?? "");
}
