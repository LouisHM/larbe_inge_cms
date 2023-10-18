<?php
//voir /.env
//DATABASE_URL=mysql://fahiioalarbre:"Bateau2387"@fahiioalarbre.mysql.db:3306/fahiioalarbre?serverVersion=5.7

include '../../vendor/php_inc/fonctions.php';
db_connect('fahiioalarbre.mysql.db', 'fahiioalarbre', 'Bateau2387', 'fahiioalarbre');

define('boltLocale', 'en');
include 'larbre_bolt.lib.php';


