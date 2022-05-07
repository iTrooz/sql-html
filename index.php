<?php
    $HOST="localhost";
    $DB_NAME="test";
    $USER="root";
    $PASS="azerty123";

    // Connect to DB
    $db = new PDO("mysql:host=" . $HOST . ";dbname=" . $DB_NAME, $USER, $PASS);
    // Display errors when occurs
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

    $uri = '/input.html';

    $ps = $db->prepare("SELECT
    nodes.nodeID, nodes.parentNodeID, nodes.tagName
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

        function __construct($id, $tagName){
            $this->id = $id;
            $this->tagName = $tagName;
            echo "added ".$this->tagName." id ".$this->id."\n";
        }
    };


    
    $arr = array();
    array_push($arr, new NodeInfo(null, null));

    while($row = $ps->fetch()){
        echo "New iteration for ".$row["tagName"]."\n";

        $lastVal = end($arr);

        if($row["nodeID"]!=$lastVal->id){
            // the last node is finished, we have data on the child
            // of another parent in the stack

            echo "Find parent of ".$row["tagName"]."  parent ".$row["parentNodeID"]."\n";
            echo "Array is at start size of ".count($arr)."\n";
            print_r($arr);

            while(true){
                if(count($arr)==0){
                    die("Shouldn't happen : Len was 0");
                }
                
                $lastVal = end($arr);
                if($row["parentNodeID"]==$lastVal->id)break;
                array_pop($arr);

                echo "checked with ".$lastVal->tagName."  ".$lastVal->id." and false\n";
                // array should not underflow
            }
            echo "found : ".$lastVal->tagName."\n";
            array_push($arr, new NodeInfo($row["nodeID"], $row["tagName"]));
        }else{
            echo "Add attribute to the same\n";
            // else this is the same
        }

        // add attribute
    }

?>