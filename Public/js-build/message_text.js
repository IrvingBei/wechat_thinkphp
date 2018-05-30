$(function(window){
    "use strict";

    var PRODUCT_LIST = $("#PL");
    
    //初始化参数 
    // FIND SKU  
    var OBJ_FIND_SKU = initFindSku({
    	chooseBtn 		: $('#TEXT-CHOOSE-BTN'),
    	chooseWin 		: $('#TEXT-CHOOSE-WINDOW'),
    	btnText  		: 'PRODUCT',
    	requestUrl		: '/Ajax/get_wx_text',
    	htmlTemplate 	: function(i, id, content,btnText){
					        return '<tr>' +
				                    '<td class="text-center">'+ id +'</td>' +
				                    '<td>'+ content +'</td>' +
				                    '<td class="text-center">' +
				                        '<button type="button" class="btn btn-sm btn-danger" data-text-id="'+ id +'" data-content="'+ content +'">'+ btnText +'</button>' +
				                    '</td>' +
				                    '</tr>';
					    },
		successFunc		: function(btnText,jsonData){
							var STATUS = jsonData.status, DATA = jsonData.data;
			                if(STATUS == 1){
			                    $('#SKU-LIST').parents('table').show(); 
			                    for(var i = 0, j = DATA.length; i < j; i++){
			                        var pic_url = DATA[i].link;
			                        $('#SKU-LIST').append( this.htmlTemplate(i,DATA[i].id, DATA[i].content,btnText) );
			                    }		
			                }else{
			                    $('#EMPTY').show();
			                }
						}
    }),
        LIST_CHOOSE_SKU = OBJ_FIND_SKU.LIST,
        WINDOW_CHOOSE_SKU = OBJ_FIND_SKU.WINDOW;

    //choose close
    LIST_CHOOSE_SKU.on('click','button',function(){
        var id = $(this).attr('data-text-id'),
            content = $(this).attr('data-content');
        //alert(id + content);
        $("textarea#content").val(content);
        WINDOW_CHOOSE_SKU.modal('hide')
    });
}(window));