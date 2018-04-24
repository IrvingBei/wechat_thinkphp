$(function(){
    "use strict";

    // 时效性
    var time_range_input = $('input[name="is_long"]'),
        time_range = $('#time-range');
    
    time_range_input.on('click', function(){
        $(this).val() == 1 ? time_range.hide() : time_range.show();
    });

    // 券
    var open_coupon_window = $('#open-coupon-window'), // 选取优惠券按钮
        coupon_selected = $('#coupon-selected'), // 已选列表
        coupon_window = $('#coupon-window'), // 搜索窗口
        search_coupon = $('#search-coupon'), // 关键字
        find_coupon = $('#find-coupon'), // 搜索按钮
        coupon_list = $('#coupon-list'), // 搜索列表
        coupon_empty = $('#coupon-empty'); // 无结果

    open_coupon_window.on('click', function(){
        coupon_window.modal('show');
    });

    var first_time = 0;

    coupon_window.on('show.bs.modal', function(){
        if(first_time === 0){
            AJAX_FIND_COUPON('');
            first_time++;
        }
    });

    find_coupon.on('click', function(){
        var v = $.trim(search_coupon.val())
        if( !v.length){
            search_coupon.focus();
            return false;
        }
        AJAX_FIND_COUPON(v);
    });

    function AJAX_FIND_COUPON(v){
        $.ajax({
            type: 'POST',
            data: 'keyword=' + v,
            url: '/Ajax/get_coupons',
            dataType: 'JSON',
            beforeSend: function(){
                coupon_list.empty().parents('table').hide();
                coupon_empty.hide();
                find_coupon.prop('disabled', true);
            },
            success: function(JSON){
                var STATUS = JSON.status, DATA = JSON.data;
                if(STATUS == 40001){
                    window.location.reload();
                }else if(STATUS == 0){ // 没找到
                    coupon_empty.show();
                }else if(STATUS == 1){ // 太多
                    toastr.error("查询结果过多，请完善查询内容");
                    search_coupon.focus();
                }else{
                    coupon_list.parents('table').show();
                    for(var i = 0, j = DATA.length; i < j; i++){
                        coupon_list.append( COUPON_TPL(i, DATA[i].coupon_id, DATA[i].type, DATA[i].discount, DATA[i].total, DATA[i].reduce) );
                    }
                }
            },
            error: function(){
                toastr.error('服务异常，请联系管理人员');
            },
            complete: function(){
                find_coupon.prop('disabled', false);
            }
        });
    }

    var COUPON_TYPE = function(type){
        switch(parseInt(type)){
            case 1:
                return "折扣券";
            case 2:
                return "满减券";
            default:
                return "无门槛券";
        }
    };

    var COUPON_DESC = function(type, discount, total, reduce){
        switch(parseInt(type)){
            case 1:
                return discount*100 + " 折";
            case 2:
                return "满" + total + "元减" + reduce + "元";
            default:
                return reduce + "元无门槛券";
        }
    };

    var COUPON_TPL = function(i, coupon_id, type, discount, total, reduce){
        return '<tr>' +
                    '<td class="text-center">'+ (i+=1) +'</td>' +
                    '<td>'+ COUPON_TYPE(type) +'</td>' +
                    '<td>'+ COUPON_DESC(type, discount, total, reduce) +'</td>' +
                    '<td class="text-center"><button data-id="'+ coupon_id +'" data-type="'+ type +'" data-discount="'+ discount +'" data-total="'+ total +'" data-reduce="'+ reduce +'" class="btn btn-xs btn-danger">选择</button></td>' +
                '</tr>';
    };

    var COUPON_SELECTED_TPL = function(id, type, discount, total, reduce){
        return '<tr class="coupon-'+ id +'">' + 
                '<td class="text-center">' + COUPON_TYPE(type) + '</td>' + 
                '<td>' + COUPON_DESC(type, discount, total, reduce) + '</td>' + 
                '<td class="text-center">' + 
                    '<input type="text" name="max[]" class="form-control text-center" value="0" required />' + 
               ' </td>' + 
               ' <td class="text-center">' + 
                    '<input type="text" name="limit[]" class="form-control text-center" value="0" required />' + 
                '</td>' + 
                '<th class="text-center col-sm-1">' + 
                    '<input type="hidden" name="coupon_id[]" value="'+ id +'">' +
                    '<a href="javascript:;" class="coupon-delete">删除</a>' + 
                '</th>' + 
            '</tr>';
    };

    coupon_list.on('click', 'button', function(){
        
        var ID = $(this).data("id"), 
            TYPE = $(this).data("type"), 
            DISCOUNT = $(this).data("discount"), 
            TOTAL = $(this).data("total"),
            REDUCE = $(this).data("reduce");

        if( $('.coupon-' + ID).size() > 0 ){
            toastr.warning('活动中已包含此优惠券');
        }else{
            // coupon_window.modal('hide');
            coupon_selected.append(COUPON_SELECTED_TPL(ID, TYPE, DISCOUNT, TOTAL, REDUCE));
        }
        
    });

    coupon_selected.on('click', '.coupon-delete', function(){
        $(this).parents('tr').remove();
    });

    // 适用范围
    var used_range_input = $('input[name="range"]'),
        used_range = $('#used-range'),
        used_range_selector = $('#used-range-selector');

    used_range_input.on('click', function(){
        $(this).val() == 1 ? used_range.hide() : used_range.show();
    });
    
    used_range_selector.multi({
        'enable_search': true,
        'search_placeholder': '可输入 商品名称、SKU属性 检索',
    });

});