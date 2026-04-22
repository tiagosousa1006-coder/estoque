<?php
include("auth.php");

if($_SESSION['user_tipo'] != 'admin'){
    die("Acesso restrito ao administrador");
}