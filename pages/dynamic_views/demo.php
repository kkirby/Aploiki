<?php
$page_title = "Simple Demo - dataModel.class";
$page_css = '
pre {font-size:14px; background-color: #FFF9C9; border-left: solid 1px #76997F; overflow: auto; width:700px; margin-left:20px; padding:10px}
#nextpage {font-size:20px; float:right; clear:both;}
';
if(!isset($_GET['section']))$_GET['section'] = 0;
$page_text = '';
if($_GET['section'] != 3)$page_text .= '<a href="' . $_AEYNIAS['config']['doc_url'] . '/index.php?page=demo&section=' . ($_GET['section'] + 1) . '" id="nextpage">Next Page</a><div style="clear:both;">&nbsp;</span>';
switch($_GET['section']){
    case 1:
        $mySQLTable = new mySQLTable('demo');
        $mySQLTable->setWhere(
                new mySQLClause('id',new mySQLOperatorIsEqualTo,'4')
            );
        $output = print_r($mySQLTable->getRows(true),true);
        
$page_text .= <<<END
Code:
<pre>
    \$mySQLTable = new mySQLTable('demo');
    \$mySQLTable->setWhere(
        new mySQLClause('id',new mySQLOperatorIsEqualTo,'4')
    );
    print_r(\$mySQLTable->getRows(true));
</pre>
Output:
<pre>
$output
</pre>
END;
        
    break;
    case 2:
        $mySQLTable = new mySQLTable('demo');
        $mySQLTable->setWhere(
                new mySQLClause(
                    $mySQLTable->fetchColumn('id'),
                    new mySQLOperatorIsEqualTo,
                    '4')
            );
        $output = print_r($mySQLTable->grabRows(),true);
        $page_text .= <<<END
Code:
<pre>
    \$mySQLTable = new mySQLTable('demo');
    \$mySQLTable->setWhere(
            new mySQLClause(
                \$mySQLTable->fetchColumn('id'),
                new mySQLOperatorIsEqualTo,
                '4')
            );
    print_r(\$mySQLTable->grabRows(),true);
</pre>
Output:
<pre>
$output
</pre>
END;
    break;
    case 3:
    $mySQLTable = new mySQLTable('demo');
    $mySQLTable->setWhere(
            new mySQLClause(
                $mySQLTable->fetchColumn('id'),
                new mySQLOperatorIsEqualTo,
                '4')
        );
    $row = current($mySQLTable->grabRows());
    if(isset($_POST['first_name'])){
        $row->setFirst_name($_POST['first_name']);
        $row->updateData();
    }
    $output = print_r($row->getData(),true);
    $page_text .= <<<END
Code:
<pre>
    \$mySQLTable = new mySQLTable('demo');
    \$mySQLTable->setWhere(
            new mySQLClause(
                \$mySQLTable->fetchColumn('id'),
                new mySQLOperatorIsEqualTo,
                '4')
        );
    \$row = current(\$mySQLTable->grabRows());
    if(isset(\$_POST['first_name'])){
        \$row->setFirst_name(\$_POST['first_name']);
        \$row->updateData();
    }
    print_r(\$row->getData());
</pre>
Output:
<pre>
$output
</pre>
<form method="post" action="{$_AEYNIAS['config']['doc_url']}/index.php?page=demo&section=3">
First Name: <input type="text" name="first_name"> <input type="submit" value="Update">
</form>
END;
    break;
    default:
        $mySQLTable = new mySQLTable('demo');
        $output = print_r($mySQLTable->getRows(true),true);
        
        $page_text .= <<<END
        Code:
        <pre>
    \$mySQLTable = new mySQLTable('demo');
    print_r(\$mySQLTable->getRows(true));
</pre>
        Output:
        <pre>
$output
        </pre>
END;
    break;
    
}
?>