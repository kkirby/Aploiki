<?php
$page_css = '
#readme {width: 800px; height: 200px;}
#readme textarea {width: 700px; height: 200px; font-family: monospace;}
#showhide {cursor: pointer;}
';

$page_title = "Welcome";
$page_text = 'This is the main page for your site, see the files:<br/> ' . $_AEYNIAS['config']['doc_root'] . '/pages/static_views/main.phtml<br/>' . $_AEYNIAS['config']['doc_root'] . '/pages/dynamic_views/main.php<br/>On how to change this page.';


$read_me = file_get_contents($_AEYNIAS['config']['doc_root'] . '/readme.txt');
$page_text .= <<<END
<br/><br/>
<a href="#" onclick="Effect.toggle('readme', 'slide'); return false;" id="showhide">Show/Hide Readme</a>
<div id="readme" style="display:none;">
	<textarea>
$read_me
	</textarea>
</div>
END;

?>