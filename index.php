<?php
    $HOST="localhost";
    $DB_NAME="test";
    $USER="root";
    $PASS="azerty123";

    $VOID = array("area", "base", "br", "col", "embed", "hr", "img",
    "input", "link", "meta", "param", "source", "track", "wbr");

    // Connect to DB
    $db = new PDO("mysql:host=" . $HOST . ";dbname=" . $DB_NAME, $USER, $PASS);
    // Display errors when occurs
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

    $uri = '/input.html';

    $ps = $db->prepare("SELECT
    nodes.nodeID, nodes.parentNodeID, nodes.tagName, nodes.tagValue, attrs.attrName, attrs.attrValue
    FROM pages
    JOIN nodes ON nodes.pageID=pages.pageID
    LEFT JOIN attrs ON nodes.nodeID = attrs.nodeID
    WHERE uri=?
    ORDER BY nodes.nodeID");
    $ps->bindParam(1, $uri);
    $ps->execute();

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

    echo ">\n";

    // close the remainings tags
    while(count($arr)>1){ // not 0 because the last value is null and must not be sent
        $lastVal = array_pop($arr);
        if(!in_array($lastVal->tagName, $VOID)){
            echo "</".$lastVal->tagName.">\n";
        }
    }

?>