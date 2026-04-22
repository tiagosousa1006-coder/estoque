<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acesso negado');
}

echo password_hash('123456', PASSWORD_DEFAULT);