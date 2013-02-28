<?php
/*
 * <TODO> Use a constant in constants.php for the folder name instead of hard coding
 */
session_start();
if (isset($_POST['request']) && function_exists($_POST['request'])) {
    $_POST['request']();
}

//==============================================================================
// CopyItem
//
// Append the given item details to the end of the specified list
//------------------------------------------------------------------------------
function CopyItem() {
    $msg="";
    if ( !isset($_POST['listname']) || !isset($_POST['itemtext']) || !isset($_POST['itemcheck']) ) { 
    echo '{"request":"NAK","msg":"Not Understood"}';
     return(0);
    }
    $inotes = isset($_POST['itemnotes']) ? $_POST['itemnotes'] : "";
    $idate =  isset($_POST['itemdate'])  ? $_POST['itemdate']  : "";
    $ListFile = 'lists/' . trim($_POST['listname']);
    $ListData = json_decode(file_get_contents($ListFile), false); //Read in list data
    
    $numc = 0;
    foreach($ListData as $LDIndex) {
        $numc++;
    }
    
    $ListData->$numc->itemtext = $_POST['itemtext'];
    $ListData->$numc->itemnotes = $inotes;
    $ListData->$numc->itemdate = $idate;
    $ListData->$numc->itemcheck = $_POST['itemcheck'];
    $result = file_put_contents($ListFile,json_encode($ListData,  JSON_FORCE_OBJECT));
    if (!$result) {
        echo '{"request":"NAK","msg":"Could not update file"}';
    } else {
        echo '{"request":"ACK"}';
    }
} //--------------------------- end of CopyItem --------------------------------

//==============================================================================
// DeleteList
//
// Delete (move to ToDelete folder)
//------------------------------------------------------------------------------
function DeleteList() {
    if (isset($_POST['listname'])) {
        $result = rename("lists/". $_POST['listname'], "trash/" . $_POST['listname']); //Move file to trash folder
        if (!$result) {
            echo '{"request":"NAK","msg":"Could Not Move List To Trash!"}';
        } else {
            echo '{"request":"ACK","msg":"List ' . $_POST['listname'] . ' has been moved to trash"}';
        }
    } else {
        echo '{"request":"NAK","msg":"Not Understood"}';
    }
} //--------------------------- end of DeleteList ------------------------------

//==============================================================================
// CloneList
// KeepCompleted = true if you want the list to include the completed items
// from the source list or false if you only want the not completed items
// This Clones from the "active" $_SESSION['listinuse'] variable
// ClName - name of the new list that is cloned from the source list
//------------------------------------------------------------------------------
function CloneList() {
    $msg = "";
    if (isset($_POST['KeepCompleted']) && isset($_POST['ClName'])) {
        $KeepCs = $_POST['KeepCompleted'];
        $SrcF = "lists/" . $_SESSION['listinuse'] . ".lst";  //Calc Source file name
        $DestF = "lists/" . $_POST['ClName'] . ".lst";        //Calc Des file name
        if ($KeepCs=="true") {  //Just copy the file wholesale
            if (!$result = copy($SrcF, $DestF)) {                     //Copy the file
                $msg = "Could not copy list file";
            }
        } else { //Run through the file line by line
            $ListData = json_decode(file_get_contents($SrcF), false); //Read file and convert JSON to Object
            $DestFP = fopen($DestF, "w");
            if (!$DestFP) {
                $msg = "Could not create new list file";
            } else {
                foreach ($ListData as $LDIndex => $LItem) {
                    if ($LItem->itemcheck) { //This item has been completed then
                        unset($ListData->$LDIndex);  //Remove Item from Object list
                    }
                } // end of foreach
                $result = fwrite($DestFP,json_encode($ListData));
                fclose($DestFP); //Close the new file
                $_SESSION['listinuse'] = $_POST['ClName'];
            } // end of else creating new list file
        } //End of else running through each line
        
    } else {
        $msg = "Not Understood";
    }
    if ($msg != "") {
        echo '{"request":"NAK","msg":"' . $msg . '"}';
    }
    else {
        echo '{"request":"ACK","msg":"' . $_POST['ClName'] . ' created"}';
    }
} //-------------------------- end of CloneList --------------------------------

//==============================================================================
// "CreateList",
//        "listname" : ListName
//------------------------------------------------------------------------------
function CreateList() {
    if (isset($_POST["listname"])) {
     $lname = $_POST["listname"];
     if (substr($lname,strlen($lname)-4) == ".lst") {
         $lname=substr($lname,0,strlen($lname)-4);
     }
     $listf = touch("lists/" . $lname. ".lst"); //Open list of writing
     $_SESSION['listinuse'] = $lname;
     echo '{"request":"ACK"}';
    } else {
        echo '{"request":"NAK","msg","Did not understand list to create"}';
    }
} //---------------- end of CreateList -----------------------------------------

//====================================
//ListLists
// Send back the list of lists
//------------------------------------------------------------------------------
function ListLists() {
    if ($handle = opendir('lists')) {
        if (!$handle) {
            echo '{"request":"NAK","msg","Could not open list folder"}';
        }
        /* This is the correct way to loop over the directory. */
        echo '{"request":"ACK","lists":[';
        $index = 0;
        while (false !== ($file = readdir($handle))) {
            if (!is_dir($file) && (substr($file, strlen($file) - 4) == ".lst")) {
                if ($index >= 1) {
                    echo ",";
                }
                echo '"' . $file . '"';
                $index++;
            }
        }
        echo ']}';
    }
} //-------------------- end of ListLists --------------------------------------

//==============================================================================
// SaveList
//
// Save the json data in the file
// If saved okay then return ACK else NAK
//------------------------------------------------------------------------------
function SaveList() {
    $msg = '';
    if (isset($_POST['listdata'])) {
        $listf = fopen("lists/" . $_SESSION["listinuse"] . ".lst", "w");
        if (!$listf) {
            $msg = "Could not open list file for writing";
        } else {
            $result = fwrite($listf,$_POST['listdata']);
            if (!$result) {
                $msg = "Could not write the data to the list file";
            }
        }
    } else {
        $msg = "You did not send any data";
    }
    if ($msg != '') {
        echo '{"request":"NAK","msg":"' + $msg + '"}';
    } else {
        echo '{"request":"ACK"}';
    }
}//------------------------------ end of SaveList ------------------------------

//==============================================================================
// GetList
//
//Read list from file and send to user
//------------------------------------------------------------------------------
function GetList() {
    echo '{"request":"ACK","listname":"'.$_SESSION["listinuse"] . '","listdata":';
    $result = readfile("lists/" . $_SESSION["listinuse"] . ".lst");
    if ($result == 0) {
        echo '""';
    }
    echo '}';   
} //--------------------- end of GetList ---------------------------------------

//==============================================================================
// SaveJournal
//
// Save the contents of Journal to todays date
//------------------------------------------------------------------------------
function SaveJournal() {
    $today = "lists/" . date("m-d-Y") . "-Journal.txt";
    $result = file_put_contents($today,$_POST["journal"]);
    if (!$result) {
        echo '{"request":"NAK","msg":"Could not save file:' .$today.'"}';
    } else {
        echo '{"request":"ACK"}';
    }
} //-------------------- end of SaveJournal ------------------------------------

//==============================================================================
// GetJournal
//
// Read journal from disk and send to user
//------------------------------------------------------------------------------
function GetJournal() {
    $msg = "";
    if (isset($_POST["jdate"])) {
        $mm = substr($_POST['jdate'], 0, 2);
        $dd = substr($_POST['jdate'], 3, 2);
        $yy = substr($_POST['jdate'], 6, 4);
        echo '{"request":"ACK","journal":"';
        readfile("lists/" . $mm . "-" . $dd . "-" . $yy . "-Journal.txt");
        echo '"}';
    } else {
        $msg = "Invalid date provided";
    }
    if ($msg != "") {
        echo '{"request":"NAK","msg":"' . $msg . '"}';
    }
} //------------------------- end of GetJournal --------------------------------
?>
