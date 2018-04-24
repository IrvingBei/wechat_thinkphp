// JavaScript Document
jQuery(function($){ 
    // backup fn 
    var _ajax = $.ajax;  
    // reset
    $.ajax = function(opt){  
        var _success = opt && opt.success || function(a, b){};  
        var _opt = $.extend(opt, {  
            success:function(data, textStatus){
                if(data.status === 40001) {  
                    window.location.reload(); 
                    return;  
                }  
                _success(data, textStatus);    
            }    
        });  
        _ajax(_opt);  
    };  
});  

Date.prototype.Format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

function getCurrentMonthFirst(){
    var date = new Date();
    date.setDate(1);
    return date;
}

function getCurrentMonthLast(){
    var date = new Date();
    var currentMonth = date.getMonth();
    var nextMonth = ++ currentMonth;
    var nextMonthFirstDay = new Date(date.getFullYear(), nextMonth, 1);
    var oneDay = 1000*60*60*24;
    return new Date(nextMonthFirstDay - oneDay);
} 

function animationHover(o, e) {
    o = $(o),
    o.hover(function () {
        o.addClass('animated ' + e)
    }, function () {
        window.setTimeout(function () {
            o.removeClass('animated ' + e)
        }, 2000)
    })
}

function toDecimal(number) { 
    var format = parseFloat(number); 
    if(isNaN(format)){ 
        return; 
    } 
    format = Math.round(number * 100) / 100; 
    return format; 
}

Array.prototype.indexOf = function(val) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == val) return i;
    }
    return -1;
};

Array.prototype.remove = function(val) {
    var index = this.indexOf(val);
    if (index > -1) {
        this.splice(index, 1);
    }
};

!function(window, undefined){

    var Tree = function(){
        var $e = $(".treeview-collapse");
        $e.delegate(".L1 > .T1", "click", function(){
            $(".L2, .L3").hide();
            $(this).parents("tr").nextUntil(".L1",".L2").show();
        });
        $e.delegate(".L2 > .T2", "click", function(){
            $(".L3").hide();
            $(this).parents("tr").nextUntil(".L2",".L3").show();
        });
    },
    Calendar = function(){
        /*
        .calendar 初始化条件
        .yearpicker 带选择年月
        .datepicker 选日期
        .timepicker 带选择时间
        .gourppicker 从日期到日期，限制可选 
        .gourppicker rangeyear 往后加1年
        */
        if( $("[class*=calendar]").size() > 0 ){
            $('<link/>').attr({'rel':'stylesheet','href':'/Public/css/plugins/jquery-ui/jquery.ui.dt.css'}).insertBefore('head > link:last');
            $.ajax({
                url: '/Public/js/plugins/jquery-ui/jquery.ui.dt.js',
                dataType: 'script',
                cache: true,
                success: function(){
                    $("[class*=calendar]").each(function(){
                        if($(this).val() == '1970-01-01'){
                            $(this).val('');
                        }
                    });
                    if($("[class*=calendar]").val()=='1970-01-01'){
                        $("[class*=calendar]").val('');
                    }
                    if( $(".timepicker[class*=calendar]").size() > 0 ){
                        $('.timepicker').prop('readonly', true).datetimepicker({ controlType: "select", timeFormat: "HH:mm", dateFormat: "yy-mm-dd", oneLine: true, showButtonPanel:false, hourMin: 6, hourMax: 22 });
                    }
                    if( $(".datepicker[class*=calendar]").size() > 0 ){
                        $('.datepicker').prop('readonly', true).datepicker({ numberOfMonths: 1, dateFormat:"yy-mm-dd", defaultDate: 0 });
                    }
                    if( $(".yearpicker[class*=calendar]").size() > 0 ){
                        $('.yearpicker').prop('readonly', true).datepicker({ numberOfMonths: 1, dateFormat:"yy-mm-dd", defaultDate: 0, changeMonth: true, changeYear: true });
                    }
                    if( $(".grouppicker[class*=calendar]").size() > 0 ){
                        var $group = $(".grouppicker").parent();
                        $group.each(function(){
                            var $from = $(this).find(".grouppicker").eq(0),
                                $to = $(this).find(".grouppicker").eq(1);
                            $from.datepicker({
                                numberOfMonths: 2,
                                onSelect: function( selectedDate ) {
                                    if($from.is('.rangeyear') || $to.is('.rangeyear')){
                                        $to.val(RangeYear(selectedDate, 1));
                                    }else{
                                        $to.datepicker( "option", "minDate", selectedDate );
                                    }
                                }
                            });
                            $to.datepicker({
                                numberOfMonths: 2,
                                onSelect: function( selectedDate ) {
                                    if($from.is('.rangeyear') || $to.is('.rangeyear')){
                                        //$from.val(RangeYear(selectedDate, 0));
                                    }else{
                                        $from.datepicker( "option", "maxDate", selectedDate );
                                    }
                                    
                                }
                            });
                        });
                        function RangeYear(d, i){
                            var d2=new Date(d);
                            if(i > 0){
                                d2.setFullYear(d2.getFullYear()+1);
                            }else{
                                d2.setFullYear(d2.getFullYear()-1);
                            }                    
                            var Y = d2.getFullYear(),
                                M = d2.getMonth() + 1,
                                D = d2.getDate();
                            if( parseInt(M) < 10 ){
                                M = "0" + M;
                            }
                            if( parseInt(D) < 10 ){
                                var D = "0" + D;
                            }
                            return Y +"-"+ M +"-"+ D;
                        }
                    }
                }
            });
        }
    },
    Tooltip = function(){
        if( $(".tooltip-fn").size() > 0 ){
            $('.tooltip-fn').tooltip({
                selector: '[data-toggle=tooltip]',
                container: 'body'
            });
            $('.tooltip-fn').popover({
                selector: '[data-toggle=popover]',
                container: 'body'
            });
        }
    },
    Validator = function(){
        if( $("form.validate").size() > 0 ){
            $.ajax({
                url: '/Public/js/plugins/validate/jquery.validate.min.js',
                dataType: 'script',
                cache: true,
                success: function(){
                    $.validator.setDefaults({
                        highlight:function(a){$(a).closest(".form-group").addClass("has-error")},
                        success:function(a){a.closest(".form-group").removeClass("has-error")},
                        errorElement:"span",
                        errorPlacement:function(a,b){
                            if(b.is(":radio")||b.is(":checkbox")||b.parent().is(".input-group")){
                                a.appendTo(b.parent().parent())
                            }else{
                                a.appendTo(b.parent())
                            }
                        },
                        errorClass:"help-block m-b-none",
                        validClass:""
                    });
                    $(".validate").validate();
                }
            });
        }
    },
    SweetAlert = function(){
        $('<link/>').attr({'rel':'stylesheet','href':'/Public/css/plugins/sweetalert/sweetalert.css'}).insertBefore('head > link:last');
        $.ajax({
            url: '/Public/js/plugins/sweetalert/sweetalert.min.js',
            dataType: 'script',
            cache: true
        });
    },
    Chosen = function(){
        if( $("select.chosen").size() > 0 ){
            $('<link/>').attr({'rel':'stylesheet','href':'/Public/css/plugins/chosen/chosen.css'}).insertBefore('head > link:last');
            $.ajax({
                url: '/Public/js/plugins/chosen/chosen.jquery.min.js',
                dataType: 'script',
                cache: true,
                success: function(){
                    $(".chosen").chosen({
                        width: "100%", 
                        search_contains: true,
                        no_results_text: "没有查询到相关数据"
                    });
                }

            });
        }
    },
    PageControl = function(){
        $("#page-refresh").click(function(){
            window.location.reload();
        });
    },
    CheckBox = function(){
        if( $(".checkbox").size() > 0 || $(".radio").size() > 0 ){
            $('<link/>').attr({'rel':'stylesheet','href':'/Public/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css'}).insertBefore('head > link:last');
        }
    },
    CheckAll = function(){
        $('.check-all').on('click', function(){
            $(this).parents('thead').siblings('tbody').find(':checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        });
        $('.check-all-form').on("submit", function(){
            if( $(".check-child:checked").size() < 1 ){
                _alert("请勾选至少一条数据", "error");
                return false;
            }
        });
    },
    Toastr = function(){
        $('<link/>').attr({'rel':'stylesheet','href':'/Public/css/plugins/toastr/toastr.min.css'}).insertBefore('head > link:last');
        $.ajax({
            url: '/Public/js/plugins/toastr/toastr.min.js',
            dataType: 'script',
            cache: true,
            success: function(){
                toastr.options = {
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "2000",
                    "extendedTimeOut": "500",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
            }

        });
    },
    PrettyFile = function(){
        if( $('input[type="file"]').size() > 0 ){
            $.ajax({
                url: '/Public/js/plugins/bootstrap-prettyfile/bootstrap-prettyfile.js',
                dataType: 'script',
                cache: true,
                success: function(){
                    $('input[type="file"]').prettyFile({
                        text: "选择文件"
                    });
                }

            });                   
        }
    },
    ModalWindow = function(){
        $(".modal").appendTo("body");
    },
    Confirm = function(){
        $(".fn-confirm, .fn-delete, .fn-do").on("click", function(e){
            var $this = $(this), $act = $this.text();
            swal({
                title: $act + "提示",
                text: "确认"+ $act +"后不可更改或撤销，请谨慎操作",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确认" + $act,
                cancelButtonText: "暂不" + $act,
                closeOnConfirm: false
            },
            function(){
                window.location.href = $this.attr("data-link");
            });
        });
    },
    InputNumber = function(){
        $('input[type="number"]').on('wheel', function(){
            return false;
        });
    };

    Tree(), Calendar(), Tooltip(), Validator(), SweetAlert(), Chosen(), PageControl(), CheckBox(), CheckAll(), Toastr(), PrettyFile(), ModalWindow(), Confirm(), InputNumber();

}(window);

function _alert(str, type){
    swal({
        title: "",
        text: str,
        timer: 2000,
        type: type,
        showConfirmButton: false
    });
}