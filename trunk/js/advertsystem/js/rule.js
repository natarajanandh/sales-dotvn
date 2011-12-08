
function changeType(){
  
    var type = document.getElementById('rule_type').value;

    if(type == '1'){
    
        document.getElementById('purchase_type').disabled=true;
        //document.getElementById('min_order_amount').disabled=true;
        
    }
    else{
         document.getElementById('purchase_type').disabled=false;
        //document.getElementById('min_order_amount').disabled=false;
    }


}

