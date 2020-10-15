<?php

$paginationParams = [
    'pageParam',
    'pageSizeParam',
    'params',
    'totalCount',
    'defaultPageSize',
    'pageSizeLimit'
];

return [
    'frontendURL' => 'http://api-rest.com/',
    'supportEmail' => 'admin@example.com',
    'adminEmail' => 'admin@example.com',
    'jwtSecretCode' => 'jwtexamplesecret',
    'user.passwordResetTokenExpire' => 3600,
    'paginationParams' => $paginationParams,
];
