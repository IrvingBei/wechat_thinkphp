
//规则类型 改变事件
$('select[name="addons"]').change(function(){
    choose_top();
});

choose_top();

/*
 * 在 关键字外面包围一个id="top_show"de div
 * */
function choose_top(){
    var addons = $('select[name="addons"]').val();

    if(addons=="Welcome"){
        $("#top_show").hide()
    }else{
        $("#top_show").show()
    }
}

/*
 * 
 * 获取素材 弹框页面公共方法
 * parameter{
 * 	chooseBtn : $('#IMAGE-CHOOSE-BTN'),  // 【选择文本素材】 按钮
 * 	chooseWin : $('#IMAGE-CHOOSE-WINDOW'), //弹出页面容器ID
 *  btnText ： PRODUCT  //按钮名称  PRODUCT ——>选择
 *  requestUrl ： '/Ajax/get_wx_news'，//请求地址
 * 	htmlTemplate  ：var SKU_LIST_TPL = function(i, id, title, intro, url, pic_url){  //页面表格模板
				        return '<tr>' +
			                    '<td class="text-center">'+ id +'</td>' +
			                    '<td class="text-center">'+ title +'</td>' +
			                    '<td class="text-center">'+ intro +'</td>' +
			                    '<td class="text-center">'+ '<a href="' + url + '" target="_blank">' + '图文地址</a></td>' +
			                    //'<td class="text-center">'+ cover_url +'</td>' +
			                    '<td class="text-center">'+'<a href="' + pic_url + '" target="_blank"><img width="100" height="70" src="' + pic_url + '"/></a></td>' +
			                    '<td class="text-center">' +
			                        '<button type="button" class="btn btn-xs btn-danger" data-text-id="'+ id +'">'+ BUTTON_TEXT +'</button>' +
			                    '</td>' +
			                    '</tr>';
				    	}
 *	callback ：function(skuList,empty,btnText,jsonData){
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
 * 
 * 
 * */

function initFindSku(parameter){

	var BTN_CHOOSE_SKU = parameter.chooseBtn,  
		WINDOW_CHOOSE_SKU = parameter.chooseWin,
		BTNTEXT =  parameter.btnText,

		SKU_CATEGORY = WINDOW_CHOOSE_SKU.find('input[name="token"]'),
		SKU_KEYWORD = WINDOW_CHOOSE_SKU.find('input[name="keyword"]'),
        BTN_FIND_SKU = $('#SKU-FIND-BTN'),
        LIST_CHOOSE_SKU = $('#SKU-LIST'),
        EMPTY = $('#EMPTY');
	
	//var BUTTON_TEXT
    var BUTTON_TEXT;

    var TIME = 0;
	
	switch(BTNTEXT){
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
            url:  parameter.requestUrl, //
            dataType: 'JSON',
            beforeSend: function(){
                LIST_CHOOSE_SKU.parents('table').hide();
                EMPTY.hide();
                LIST_CHOOSE_SKU.empty();
                BTN_FIND_SKU.prop('disabled', true);
            },
            success: function(JSON){  
            	//回调
            	parameter.successFunc(BUTTON_TEXT,JSON);
            },
            error: function(){
                toastr.error('服务异常，请联系管理人员');
            },
            complete: function(){
                BTN_FIND_SKU.prop('disabled', false);
            }
        });
    }
    var OBJ = {};
    OBJ.LIST = LIST_CHOOSE_SKU;
    OBJ.WINDOW = WINDOW_CHOOSE_SKU;
    return OBJ;
}