<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

function temPermissao($modulo){

    // NÃO LOGADO
    if(!isset($_SESSION['user_id'])){
        return false;
    }

    // ADMIN LIBERADO
    if(isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'admin'){
        return true;
    }

    // 🔥 SE NÃO EXISTIR CONEXÃO, BLOQUEIA SEM QUEBRAR
    if(!isset($GLOBALS['conn'])){
        return false;
    }

    $conn = $GLOBALS['conn'];

    $user_id = intval($_SESSION['user_id']);
    $modulo = addslashes($modulo);

    $res = $conn->query("
        SELECT id FROM permissoes 
        WHERE user_id = $user_id 
        AND modulo = '$modulo'
        LIMIT 1
    ");

    return ($res && $res->num_rows > 0);
}