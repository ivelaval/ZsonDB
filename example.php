<?php

include_once 'libs/Zson.class.php';

$ins = new Zson();


$ins->dbsion()->createTable(array(
    "name"=>"config",
    "fields"=>array(
      "id"=>array(
        "index"=>"primary",
        "autoincrement"=>1,
        "type"=>"integer"
    ),"name"=>array(
        "type"=>"string",
    ),"email"=>array(
        "type"=>"string",
    ),"date"=>array(
        "type"=>"string",
    )),
))->createTable(array(
    "name"=>"access",
    "fields"=>array(
        "id"=>array(
            "index"=>"primary",
            "autoincrement"=>1,
            "type"=>"integer"
        ),"ip"=>array(
             "type"=>"string",
        ),"user"=>array(
             "type"=>"string",
        ),"date"=>array(
             "type"=>"string",
        )),
))->createTable(array(
    "name"=>"user",
    "fields"=>array(
        "id"=>array(
            "index"=>"primary",
            "autoincrement"=>1,
            "type"=>"integer"
        ),"name"=>array(
            "type"=>"string",
        ),"email"=>array(
            "type"=>"string",
            "index"=>"unique"
        ),"age"=>array(
            "type"=>"string",
        )),
));


$ins->dbsion()->tbconfig()->insert(array(
    "name"=>"Ivan Elias Avila",
    "email"=>"ivan.avila@leveluplatam.com",
    "date"=>date("Y-m-d H:i:s"),
));

$records = $ins->dbsion()->tbconfig()->update(array(
        "select"=>array("id"=>5),
        "update"=>array("email"=>"artisan@laravel.com"),
    ));

//Esto es una introducción al desarrollo de un sistema de manejo de JSON al estilo de base de datos

//$records = $ins->dbsion()->tbconfig()->delete(array("name"=>"julian mendez"));

//$records = $ins->dbsion()->tbconfig()->select();

//$records = $ins->dbsion()->viewTables("*","name");

//$records = $ins->dbsion()->dropTable("access");

//$records = $ins->dbsion()->viewTables("*","name");

echo "<pre>";
//print_r($records);
echo "</pre>";

echo "<pre>";
print_r($ins->getDebug());
echo "</pre>";

?>
<!--
<html>
    <head>
        <title>Discusiones</title>
    </head>
    
    <body>
    <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'cerato'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    
    </body>
    
</html>
-->
