<?php
    // DB settings
    $HOST="localhost";
    $DB_NAME="test";
    $USER="root";
    $PASS="azerty123";

    // Connect to DB
    $db = new PDO("mysql:host=" . $HOST . ";dbname=" . $DB_NAME, $USER, $PASS);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );


    $uri = '/input.html';

    if(isset($_GET["uri"])){
        $uri = $_GET["uri"];
        if($uri[0]!="/")$uri = "/".$uri;
    }else{
        $uri = "/index.html";
    }

    // Request a view of all nodes/attributes of the page
    $ps = $db->prepare("SELECT
    nodes.nodeID, nodes.parentNodeID, nodes.tagName, nodes.tagValue, attrs.attrName, attrs.attrValue
    FROM pages
    JOIN nodes ON nodes.pageID=pages.pageID
    LEFT JOIN attrs ON nodes.nodeID = attrs.nodeID
    WHERE uri=?
    ORDER BY nodes.nodeID");
    $ps->bindParam(1, $uri);
    $ps->execute();

    // If we didn't get any results, the page doesn't exist
    if($ps->rowCount()==0){
        echo "This page doesn't exist !";
        return;
    }


    // generate HTML page

    $VOID = array("area", "base", "br", "col", "embed", "hr", "img",
    "input", "link", "meta", "param", "source", "track", "wbr");

    class NodeInfo{
        public $id;
        public $tagName;
        public $tagValue;

        function __construct($id, $tagName, $tagValue){
            $this->id = $id;
            $this->tagName = $tagName;
            $this->tagValue = $tagValue;
        }
    };


    
    $arr = array();
    array_push($arr, new NodeInfo(null, null, null));

    echo "<!DOCTYPE html";

    while($row = $ps->fetch()){

        $lastVal = end($arr);

        if($row["nodeID"]!=$lastVal->id){

            echo ">".$lastVal->tagValue."\n";

            // new node : find the parent
            while(true){
                if(count($arr)==0){
                    die("Shouldn't happen : Len was 0");
                }
                
                $lastVal = end($arr);
                if($row["parentNodeID"]==$lastVal->id)break;

                if(!in_array($lastVal->tagName, $VOID)){
                    echo "</".$lastVal->tagName.">\n";
                }

                array_pop($arr);

            }
            array_push($arr, new NodeInfo($row["nodeID"], $row["tagName"], $row["tagValue"]));
            echo "<".$row["tagName"];
        }

        if($row["attrName"]!=null){
            echo " ".$row["attrName"]."='".$row["attrValue"]."'";
        }
    }

    // finish the array

    $lastVal = end($arr);
    echo ">".$lastVal->tagValue."\n";

    // close the remainings tags
    while(count($arr)>1){ // not 0 because the last value is null and must not be sent
        $lastVal = array_pop($arr);
        if(!in_array($lastVal->tagName, $VOID)){
            echo "</".$lastVal->tagName.">\n";
        }
    }

?>