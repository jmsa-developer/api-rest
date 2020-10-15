<?php

$db='';
$username='';
$password='';

return [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=' . $db,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql' => [
                    'class' => 'yii\db\pgsql\Schema',
                    'defaultSchema' => 'public' //specify your schema here, public is the default schema
                ]
            ], // PostgreSQL

];
