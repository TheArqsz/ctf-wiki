<?php
echo "
<html>
    <head>
        <title>Are you funny?</title>
    </head>
    <body>
        Give me your name and I will tell you whether you are funny<br/>
        <form action=\"index.php\" method=\"GET\">
            <input type=\"text\" name=\"name\"/><br/>
            <input type=\"submit\"/>
        </form>";
if(array_key_exists('name', $_GET)) {
    $name = $_GET["name"];
    $db = new SQLite3('example.db');
    $query = "SELECT is_funny FROM funny as f, users as u JOIN users ON f.user_name=u.user_name WHERE u.user_name='$name';";
    $result = @$db->query($query);
    if($status = $result->fetchArray()[0]){
        echo $status;
    } else {
        echo("No such user");
    }
    
}
echo "
    </body>
</html>";
?>