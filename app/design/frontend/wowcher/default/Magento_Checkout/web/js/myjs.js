require(['jquery', 'jquery/ui'], function($){ 
    $( document ).ready(function() {

      //wait until the last element (.payment-method) being rendered
      var existCondition = setInterval(function() {
       if ($('.payment-method').length) { 
        clearInterval(existCondition);
        runMyFunction();
       }
      }, 1000);

      function runMyFunction(){
       //console.log("Last");
       $("#braintree").prop("checked", true).trigger("click");
      }

    }); 
 }); 
