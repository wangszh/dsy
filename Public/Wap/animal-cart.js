/*
 * Jquery—shopping   0.1
 * Copyright (c) 2013  Nicky Yan   个人网：站http://www.chinacoder.cn  QQ：525690001
 * Date: 2013-04-02
 * 使用Jquery—shopping可以很方便的实现加入购物车效果
 */

;(function($){
    $.extend($.fn,{
        shoping:function(options){
            var self=this,
                $shop=$('.myButton')
            var S={
                init:function(){
                    $(self).data('click',true).live('click',this.addShoping);
                },
                addShoping:function(e){
                    e.stopPropagation();
                    var $target=$(e.target),
                    id=$target.attr('id'),
                    dis=$target.data('click'),
                    x = $target.offset().left,
                    y = $target.offset().top,
                    X = $shop.offset().left+$shop.width()/2-$target.width()/2+10,
                    Y = $shop.offset().top;

                    var $obj=document.getElementById('floatOrder');
                    if($obj != null) {$obj.remove();}
                    $('body').append('<div id="floatOrder"><img src="./APP/Wap/View/image/shop/btn_plus.png" width="30" height="30" /></div>');
                    $obj=$("#floatOrder");
                    $obj.css({'left': x,'top': y}).animate({'left': X,'top': Y-80},500,function() {
                        $obj.stop(false, false).animate({'top': Y-20,'opacity':0},500,function(){
                            $obj.fadeOut(300,function(){
                                $obj.remove();
                            });
                        });
                    });
                }
            };
            S.init();
        }
    });
})(jQuery);