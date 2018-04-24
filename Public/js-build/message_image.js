$(function(window){
    "use strict";

    var PRODUCT_LIST = $("#PL");
    
    // FIND SKU  
    var OBJ_FIND_SKU = initFindSku('PRODUCT'),
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

function initFindSku(MOD){

	var BTN_CHOOSE_SKU = $('#IMAGE-CHOOSE-BTN'),
		WINDOW_CHOOSE_SKU = $('#IMAGE-CHOOSE-WINDOW'),

		SKU_CATEGORY = WINDOW_CHOOSE_SKU.find('input[name="token"]'),
		SKU_KEYWORD = WINDOW_CHOOSE_SKU.find('input[name="keyword"]'),
        BTN_FIND_SKU = $('#SKU-FIND-BTN'),
        LIST_CHOOSE_SKU = $('#SKU-LIST'),
        EMPTY = $('#EMPTY');
	
	//var BUTTON_TEXT
    var BUTTON_TEXT;

    var TIME = 0;
	
	switch(MOD){
		case 'PRODUCT':
			BUTTON_TEXT = '选择';
			break;
		default:
			$('.wrapper').html('<h1>模块 '+ MOD +' 初始化失败，请刷新重试</h1>');
			break;
	};

    BTN_CHOOSE_SKU.on('click', function(){
        WINDOW_CHOOSE_SKU.modal('show');

        //加载弹窗时触发搜索
        if(TIME === 0){
            AJAX_FIND_SKU( $.trim(SKU_CATEGORY.val()), $.trim(SKU_KEYWORD.val()) );
            TIME++;
        }
    });



    //点击按钮触发搜索
    BTN_FIND_SKU.on('click', function(){
        if( !$.trim(SKU_CATEGORY.val()).length && !$.trim(SKU_KEYWORD.val()).length){
            SKU_KEYWORD.focus();
            return false;
        }
        AJAX_FIND_SKU( $.trim(SKU_CATEGORY.val()), $.trim(SKU_KEYWORD.val()) );
    });

    //按下回车触发搜索
    SKU_KEYWORD.on('keypress', function(e){
        if(e.keyCode == 13 && $.trim(SKU_KEYWORD.val()).length){
            AJAX_FIND_SKU( $.trim(SKU_CATEGORY.val()), $.trim(SKU_KEYWORD.val()) );
        }
    });


    //获取列表
    function AJAX_FIND_SKU(v,k){
        $.ajax({
            type: 'POST',
            data: 'keyword=' + k + '&token=' + v,
            url: '/Ajax/get_wx_image',
            dataType: 'JSON',
            beforeSend: function(){
                LIST_CHOOSE_SKU.parents('table').hide();
                EMPTY.hide();
                LIST_CHOOSE_SKU.empty();
                BTN_FIND_SKU.prop('disabled', true);
            },
            success: function(JSON){
                var STATUS = JSON.status, DATA = JSON.data;
                if(STATUS == 1){
                    LIST_CHOOSE_SKU.parents('table').show();
                    for(var i = 0, j = DATA.length; i < j; i++){
                        var cover_url = '/Public/upload/images/wechat/' + DATA[i].cover_url;
                        LIST_CHOOSE_SKU.append( SKU_LIST_TPL(i,DATA[i].id, DATA[i].image_name, DATA[i].media_id, cover_url) );
                    }
                }else{
                    EMPTY.show();
                }
            },
            error: function(){
                toastr.error('服务异常，请联系管理人员');
            },
            complete: function(){
                BTN_FIND_SKU.prop('disabled', false);
            }
        });
    }

    var SKU_LIST_TPL = function(i, id, image_name, media_id, cover_url){
        return '<tr>' +
                    '<td class="text-center">'+ id +'</td>' +
                    '<td class="text-center">'+ image_name +'</td>' +
                    '<td class="text-center">'+ media_id +'</td>' +
                    //'<td class="text-center">'+ cover_url +'</td>' +
                    '<td class="text-center">'+'<a href="' + cover_url + '" target="_blank"><img width="200" height="100" src="' + cover_url + '"/></a></td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-xs btn-danger" data-text-id="'+ id +'">'+ BUTTON_TEXT +'</button>' +
                    '</td>' +
                    '</tr>';
    }

    var OBJ = {};
    OBJ.LIST = LIST_CHOOSE_SKU;
    OBJ.WINDOW = WINDOW_CHOOSE_SKU;
    return OBJ;
}