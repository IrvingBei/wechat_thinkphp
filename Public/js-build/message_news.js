$(function(window){
    "use strict";

    var PRODUCT_LIST = $("#PL");
    
    // FIND SKU  
    var OBJ_FIND_SKU = initFindSku({
    	chooseBtn 		: $('#NEWS-CHOOSE-BTN'),
    	chooseWin 		: $('#NEWS-CHOOSE-WINDOW'),
    	btnText  		: 'PRODUCT',
    	requestUrl		: '/Ajax/get_wx_news',
    	htmlTemplate 	: function(i, id, title, intro, url, pic_url,btnText){ //模板
					        return '<tr>' +
					                    '<td class="text-center">'+ id +'</td>' +
					                    '<td class="text-center">'+ title +'</td>' +
					                    '<td>'+ intro + ' <a href="' + url + '" target="_blank"><i class="fa fa-share"></i></a></td>' +
					                    //'<td class="text-center">'+ cover_url +'</td>' +
					                    '<td class="text-center">'+'<a href="' + pic_url + '" target="_blank"><img width="100" height="70" src="' + pic_url + '"/></a></td>' +
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
			                        var pic_url = DATA[i].link;
			                        $('#SKU-LIST').append( this.htmlTemplate(i,DATA[i].id, DATA[i].title, DATA[i].intro, DATA[i].url,pic_url,btnText) );
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
        //alert(id + content);
        $("#image_material").val(id);
        WINDOW_CHOOSE_SKU.modal('hide')
    });
}(window));