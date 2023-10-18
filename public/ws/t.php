<?php
$inis = ini_set("default_charset","utf-8");

include './main.inc.php';
echo '<pre>';

$test = "* Chauffage/refroidissement :
* Plomberie/Sanitaire :
* VRD (565 000 €) : 12 000 m2 de terrain d'assiette
10 000 m3 de déblais
1670 m2 de parking
1250 m2 de béton désactivé
2010 ml de réseaux divers

* Electricité :";

$val2ins = '["'. nl2br($test).'"]';
//echo db_escape_string($val2ins);
//echo addslashes($val2ins);
//echo json_encode([nl2br($test)]);
//echo chr(92).", ".chr(92).chr(92);

//$valChpJS = str_replace("\r", chr(92).chr(92).'r', nl2br($test));
//$valChpJS = str_replace("\n", chr(92).chr(92).'n', $valChpJS);
//$valChpJS = db_escape_string( '["'. $valChpJS.'"]');
$valChpJS = '[\"'.str_replace(chr(92),chr(92).chr(92),db_escape_string( nl2br($test))).'\"]';
echo $valChpJS;

/*
 ci dessus db_escape_string
 [\"* Chauffage/refroidissement :<br />\n* Plomberie/Sanitaire :<br />\n* VRD (565 000 €) : 12 000 m2 de terrain d\'assiette<br />\n10 000 m3 de déblais<br />\n1670 m2 de parking<br />\n1250 m2 de béton désactivé<br />\n2010 ml de réseaux divers<br />\n<br />\n* Electricité :\"]
 
 *  ci dessus addslashes
 <pre>[\"* Chauffage/refroidissement :<br />
* Plomberie/Sanitaire :<br />
* VRD (565 000 €) : 12 000 m2 de terrain d\'assiette<br />
10 000 m3 de déblais<br />
1670 m2 de parking<br />
1250 m2 de béton désactivé<br />
2010 ml de réseaux divers<br />
<br />
* Electricité :\"]
 * 
 ci dessus json_encode
<pre>["* Chauffage\/refroidissement :<br \/>\n* Plomberie\/Sanitaire :<br \/>\n* VRD (565 000 \u20ac) : 12 000 m2 de terrain d'assiette<br \/>\n10 000 m3 de d\u00e9blais<br \/>\n1670 m2 de parking<br \/>\n1250 m2 de b\u00e9ton d\u00e9sactiv\u00e9<br \/>\n2010 ml de r\u00e9seaux divers<br \/>\n<br \/>\n* Electricit\u00e9 :"]


<pre>[\\"* Chauffage/refroidissement :<br />\\n* Plomberie/Sanitaire :<br />\\n* VRD (565 000 €) : 12 000 m2 de terrain d\\'assiette<br />\\n10 000 m3 de déblais<br />\\n1670 m2 de parking<br />\\n1250 m2 de béton désactivé<br />\\n2010 ml de réseaux divers<br />\\n<br />\\n* Electricité :\\"]

<pre>[\"* Chauffage/refroidissement :<br />\n* Plomberie/Sanitaire :<br />\n* VRD (565 000 €) : 12 000 m2 de terrain d\'assiette<br />\n10 000 m3 de déblais<br />\n1670 m2 de parking<br />\n1250 m2 de béton désactivé<br />\n2010 ml de réseaux divers<br />\n<br />\n* Electricité :\"]

 le bon
 '[\"<p>* Chauffage/refroidissement : <br></p>\\r\\n<p> * Plomberie/Sanitaire : <br></p>\\r\\n<p> * VRD (565 000 €) : 12 000 m2 de terrain d\'assiette<br></p>\\r\\n<p>10 000 m3 de déblais <br></p>\\r\\n<p>1670 m2 de parking<br></p>\\r\\n<p>1250 m2 de béton désactivé <br></p>\\r\\n<p>2010 ml de réseaux divers<br></p>\\r\\n<p><br></p>\\r\\n<p> * Electricité :</p>\"]' 
 
 */


die();

$idC = crupChantier(410, []);
echo "res idc $idC";
phpinfo();
die();
include './main.inc.php';

$tbt = db_qr_rass2("select * from rcej1_hikashop_product order by product_id desc limit 1");

print_r($tbt);
include '../configuration.php';
$conf = new JConfig();
echo $conf->dbtype;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

