<html>
<head>
    <link href="./index.css" rel="stylesheet" />
    <title>Μαζική Εισαγωγή Κατηγοριών και Προϊόντων</title>
</head>
<body>
<?php

require_once('./system.php');

$error = null;
if(isset($_POST['entity']) && isset($_FILES['excel']['tmp_name'])) {

    $entity = $_POST['entity'];
    $file = $_FILES['excel'];
    $controllerName = $entity."Controller";
    if(!is_file("controller/".$entity."Controller.php")) {
        echo 'Couldn\'t find import function for this entity! {'.$entity.'Controller.php}<br/>';
    } else {
        $controller = new $controllerName;
        if(!$controller->validateFile($_FILES['excel'])) {
            $error = $controller->getError();
        }

        $controller->parse();
    }
}

?>
    <section class="header"></section>
    <section class="main">
        <div class="titlebanner">Μαζική Εισαγωγή Κατηγοριών και Προϊόντων</div>
        <div class="importform">
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div class="error">
                <p>
                    <?php if($error != null) echo $error; ?>
                </p>

            </div>
            <p>
                <span>Επιλέξτε τι θέλετε να εισάγετε: </span>
                <select name="entity">
                    <option value="Products">Προϊόντα</option>
                    <option value="Categories">Κατηγορίες</option>
                </select>
            </p>
            <input type="file" name="excel" class="custom-file-input" />
            <p>
            <input type="submit" value="ΕΙΣΑΓΩΓΗ" class="importButton" />
            </p>
        </form>
        </div>
    </section>
    <section class="footer">
      Implemented by <a href="http://sotirispoulias.com">Sotiris Poulias</a>
    </section>
</body>
</html>