<?php
/*
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+
| Made by Kyle Kirby                                       |
+----------------------------------------------------------+
*/
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['password']);

header('LOCATION: '. $_AEYNIAS['config']['doc_url']);


?>