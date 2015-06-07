<?php
require_once "bootstrap.php";
$server = new \Saki\App\Server();
$server->process();
$data = $server->getData();
$tileList = $data;
?>

<html>
<head></head>
<body>
<form action='index.php'>
    <?php foreach ($tileList as $tile) { ?>
        <input type="submit" name="tile" value="<?php echo $tile->__toString() ?>"/>
    <?php } ?>
</form>
</body>
</html>

