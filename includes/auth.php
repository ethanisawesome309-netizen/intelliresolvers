<?php
function login($conn, $email, $password) {
    $stmt = $conn->prepare(
        "SELECT id, password_hash FROM users WHERE email = :email"
    );
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        return true;
    }
    return false;
}
