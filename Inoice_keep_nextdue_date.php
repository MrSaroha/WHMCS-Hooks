<?php
/**
 * Restore Due Date Hook Function
 *
 * @package    NHGroup
 * @author     Network Hosting Group
 * @copyright  Copyright (c) Network Hosting Group 2023-2033
 * @license    https://networkhostinggroup.com/terms-of-service
 * @version    1.0.0
 * @link       https://networkhostinggroup.com/
 */
 
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

function nhgroup_add_log($log_msg){
    //  $nn = $data.$final_url;
    // $file = fopen("testLOG.txt","w");
    // echo fwrite($file,$nn);
    // fclose($file);
    $txt = "\n".$log_msg;
    $myfile = file_put_contents('testLOG.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}
function override_next_due_date($table,$id,$date){
     nhgroup_add_log("due date function called");
    $data = Capsule::table($table)->where('id',$id)->update(array('nextduedate'=>$date,'nextinvoicedate'=>$date));
    return true;
};
add_hook('InvoicePaid', 1, function($vars) {
     nhgroup_add_log("Hooks called");
    $command = 'GetInvoice';
    $postData = array(
        'invoiceid' => $vars['invoiceid'],
    );
    $results = localAPI($command, $postData);
    

    $invoice_due_date = $results['duedate'];
    $m = "invoice id =".$vars['invoiceid']." and duedate ".$invoice_due_date;
     nhgroup_add_log($m);
    foreach($results['items']['item'] as $item){
        if($item['type']=="Hosting"){
             nhgroup_add_log("In hosting option");
            override_next_due_date("tblhosting",$item['relid'],$invoice_due_date);
        }elseif(strpos($item['type'], "omain")>0){
            override_next_due_date("tbldomains",$item['relid'],$invoice_due_date);
        }
        $ms = "in loop type=".$item['type'];
         nhgroup_add_log($ms);

    }
});

?>
