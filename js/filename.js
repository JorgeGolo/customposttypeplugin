jQuery( document ).ready(function() {
    console.log("Hello world!");
    
    jQuery.ajax({
        type : "GET",
        dataType : "JSON",
        url : my_ajax_object.ajax_url,
        data : {
             action: "get_shop_data",
        },
    
        error: function(response, error) {
            console.log("wrong");
        },
    
        success : function(response) {
            console.log(response);
            jQuery(document.body).append(response);
    
        }
    });
    
    });