<?php
// logging interface
// Provides a single-call interface for writing to the `logs` table.
// provides: TBD
//


//The Logging function. BE AFRAID!!!!!

function LogSimeckAction( $user_action, $extra_data, $project_target){
    $pdo = DBConnect();
    $stmt = $pdo->prepare("INSERT INTO logs (username, time, user_action, ip_address, extra_data, project_target, impersonated_by) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if(!isset($_SESSION['_imp_orig_username'])){ //Make sure that if the user is impersonating admin, we log who they are impersonating as well as who they actually are.
        $stmt->execute([$_SESSION['username'], $user_action, $_SERVER['REMOTE_ADDR'], $extra_data, $project_target, NULL]);
    } else {
        $stmt->execute([$_SESSION['username'], $user_action, $_SERVER['REMOTE_ADDR'], $extra_data, $project_target, $_SESSION['_imp_orig_username']]);
    }

}
?>