
function leftMenuWidth(){
    var width = $('.left-menu-naviagation-wrap').outerWidth()+18;
    $('.nav-item-two,.nav-item-three').outerWidth(width);
}

function mainPageHeight(){
    if($('.main_page').length > 0){
    var header = parseInt($('.header').outerHeight()+$('.content-top-wrap').outerHeight());
    var container = parseInt($(window).outerHeight() - header);
   $('.content-wrap,.left-menu-wrap').outerHeight(container);
    }
}


function settingsInputFile(){
    $('.form-description-settings-inp-wrap input').styler({
        fileBrowse:" ",
        filePlaceholder:"Сменить аватар"
    });
}
function contentCardSelect(){
    if($('.content-card-select-wrap select').length > 0){
        $('.content-card-select-wrap select').styler({
            selectSmartPositioning:'-1'
        });
    }
}
function marketSelection(){
    if($('.selection-rase select').length > 0){
        $('.selection-rase select').styler({
            selectSmartPositioning:'-1'/*,
            onSelectClosed:function (){
                var selected = $(this).find('.selected').index();
                $('.select-rase-img').removeClass('active');
                $('.select-rase-img').eq(selected).addClass('active');
            }*/
        });
        $('.selection-rase-img').click(function() {
            $('.selection-rase .jq-selectbox__dropdown').show();
            setTimeout(function(){
                $('.selection-rase .jq-selectbox').addClass('opened');
            },200);
        });
    }
}

/*rating page*/
function ratingPage(){
    if( $('.rating-page').length ) {
        var scrollElem = $('.rating-table-main .item.active .item-wrap');
        var api = null;

        function initScroll(){ // init scroll in table
            scrollElem.jScrollPane({
            contentWidth: '0px',
            showArrows: true,
            //autoReinitialise: true
            });
            api = $('.rating-table-main .item.active .item-wrap').data('jsp');

            if ( $('.rating-table-main-wrap .row.current').length > 0){ // add position user to => center
                var top = $('.rating-table-main-wrap .row.current').position().top;
                var containerHeight = $('.jspContainer').outerHeight();

                api.scrollBy(0,top-containerHeight/2);
                return false;
            }

        }
        initScroll();

        function destroyScroll(){ // destroy scroll in table
            if( $('.rating-table-main .item.active .item-wrap').is('.jspScrollable') ) {
                api.destroy();
            }
        }


        $(document).on('click', '.rating-table-title-tab .item', function(event) {
            event.preventDefault();
            var liga = parseInt($(this).data('item'));
            var that = $(this);
            $('.rating-table-title-tab .item').removeClass('active');
            $('.rating-table-main').addClass('hide');
            $('.rating-page .preloader').addClass('active');


            var ratingData;
            $.ajax({
                url:'js/json/rating_lig.json',
                data:{ action:'rating_lig', name_lig:liga},
                method: 'POST'
            }).done(function(data) {
                if(typeof data == 'object'){
                    ratingData = data;
                }else{
                    ratingData = JSON.parse(data);
                }

                destroyScroll();//destoy scroll
                $('.rating-table-main-wrap .item-wrap .row').remove();//clear .row

                $('.rating-table-wrap .rating-table-main-wrap .item').attr('data-item', liga);//change the dat-attr
                var rows = '';
                var current = '';
                $.each(ratingData.liga_holder, function(index, item) {
                     /* iterate through object */
                     if( typeof item.user != 'undefined'){
                        current = 'current';
                     }else{
                        current = '';
                     }
                     rows += "<div class='row "+current+"'><div class='number'>"+item.number+"</div><div class='players'>"+item.players+"</div><div class='battle_number'>"+item.battle_number+"</div><div class='percent_of_wins'>"+item.percent_of_wins+"</div><div class='overall_rating'>"+item.overall_rating+"</div></div>";
                });

                $('.rating-table-main-wrap .item-wrap').append(rows);//  apend new row in our table-div


                $('.rating-table-main .item.active .item-wrap').jScrollPane().data().jsp; // reinint jscrollpane
                api = $('.rating-table-main .item.active .item-wrap').data('jsp');

                that.addClass('active');
                $('.rating-table-main').removeClass('hide');
                $('.rating-page .preloader').removeClass('active');
            })
            .fail(function() {
                alert('Ошибка загрузки данных');
                $('.rating-table-main').removeClass('hide');
                $('.rating-page .preloader').removeClass('active');
            });

        });

    }

}
/*/rating page*/







$(document).ready(function(){
    ratingPage();
    settingsInputFile();
    marketSelection();
    contentCardSelect();
    leftMenuWidth();
    mainPageHeight();

});
$(window).load(function() {
    //marketSelection();
});