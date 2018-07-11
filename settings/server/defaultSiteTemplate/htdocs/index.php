<?php
$sectionIdentifier=(!empty($_GET['section'])?preg_replace('~[^a-zA-Z0-9_-]~','',$_GET['section']):'index');
$pageIdentifier=(!empty($_GET['page'])?preg_replace('~[^a-zA-Z0-9_-]~','',$_GET['page']):'index');
include __DIR__.'/sitePage.php';
?>
