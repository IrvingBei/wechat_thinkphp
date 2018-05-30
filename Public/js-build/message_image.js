$(function(window){
    "use strict";

    var PRODUCT_LIST = $("#PL");
    
    // FIND SKU  
    var OBJ_FIND_SKU = initFindSku({
    	chooseBtn 		: $('#IMAGE-CHOOSE-BTN'),
    	chooseWin 		: $('#IMAGE-CHOOSE-WINDOW'),
    	btnText  		: 'PRODUCT',
    	requestUrl		: '/Ajax/get_wx_image',
    	htmlTemplate 	: function(i, id, image_name, media_id, cover_url,btnText){
					        return '<tr>' +
					                    '<td class="text-center">'+ id +'</td>' +
					                    '<td class="text-center">'+ image_name +'</td>' +
					                    '<td class="text-center">'+ media_id +'</td>' +
					                    //'<td class="text-center">'+ cover_url +'</td>' +
					                    '<td class="text-center">'+'<a href="' + cover_url + '" target="_blank"><img width="100" height="70" src="' + cover_url + '"/></a></td>' +
					                    '<td class="text-center">' +
					                        '<button type="button" class="btn btn-sm btn-danger" data-text-id="'+ id +'">'+ btnText +'</button>' +
					                    '</td>' +
					                    '</tr>';
					    },
		successFunc		: function(btnText,jsonData){
							var STATUS = jsonData.status, DATA = jsonData.data;
			                if(STATUS == 1){
			                    $('#SKU-LIST').parents('table').show();
			                    for(var i = 0, j = DATA.length; i < j; i++){
			                        var cover_url = '/Public/upload/images/wechat/' + DATA[i].cover_url;
			                        $('#SKU-LIST').append( this.htmlTemplate(i,DATA[i].id, DATA[i].image_name, DATA[i].media_id, cover_url,btnText) );
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
        var id = $(this).attr('data-text-id');
        $("#image_material").val(id);
        WINDOW_CHOOSE_SKU.modal('hide')
    });
}(window));