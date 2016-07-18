// card in index.html

function initScrollpane() {
    $('.scroll-pane').jScrollPane({
        contentWidth: '0px',
        autoReinitialise: true
    });
}

function underDragCardFix() {
    if ($('.content-card-field')) {
        $('.content-card-field').mouseleave(function (event) {
            $(document).mouseup();
        });
    }
}

function draggableCards() {
    $("#sortableOne, #sortableTwo").sortable({
        connectWith: ".connected-sortable",
    }).disableSelection();
}


$(window).load(function () {

    initScrollpane();
    draggableCards();
    underDragCardFix();

});

// / card in index.html ---------------------------------------------------------------------------------- >

function resetFields() {
    $('.user .field-meele').removeClass('unselectable-row');
    $('.user .field-range').removeClass('unselectable-row');
    $('.user .field-super-renge').removeClass('unselectable-row');

    $('.user .field-for-cards').removeClass('grayscl');
    //$('.user .field-range').removeClass('grayscl');
    $('.user .field-for-cards').removeClass('swapable');
}

function resetards() {
    $('#sortable-user-cards>li').removeClass('grayscl');
    //console.log($('#sortable-user-cards').width());

    var conteiner = $('#sortable-user-cards').width();
    var childs = $('#sortable-user-cards>li').length;

    var widthOneElement = (100 * (conteiner - 130) / conteiner) / (childs + 1);
    $('.user-stats .greencard-num').html($('#sortable-user-cards>li').length);
}


function randomTime() {
    var startS = Math.round(Math.random() * (60));
    var startM = Math.round(Math.random() * (6));
    if (startM < 10) {
        startM = '0' + startM;
    }
    if (startS < 10) {
        $('.timer-go .timer-s-search').html('0' + startS);
        startS = '0' + startS;
    }

    $('.timer-fake .timer-go').html('<span>' + startM + '</span> <span>:</span> <span>' + startS + '</span>');
}

function treshSupportForFix() {
    // ------------------------------------------------------------------------------------------

    $('.card-my-init li:not(:last-child)').hover(
        function () {
            $(this).css('margin-bottom', '60px');
        },
        function () {
            $(this).css('margin-bottom', '0px');
        }
    );


    $('.card-otboy-counter> .counter').text($('#otboy-cards-list>li').length);
    $('.card-take-more-counter> .counter').text($('#cards-take-more-list>li').length);

    // ----------------------------------------------------------------------------------------------
    var startTime1 = [{ minute: 0, seconds: 3 }];
    var changedTime = [{ minute: 0, seconds: 3 }];


    setInterval(function () {
        var startMinutes = changedTime[0].minute;
        var startSeconds = changedTime[0].seconds - 1;

        if (startSeconds == -1) {
            startMinutes = startMinutes - 1;
            startSeconds = 59;
        }

        changedTime[0].minute = startMinutes;
        changedTime[0].seconds = startSeconds;

        if ((changedTime[0].minute == 0) && (changedTime[0].seconds == 0)) {
            changedTime = [{ minute: 2, seconds: 0 }];
        }


        if (startMinutes < 10) {
            startMinutes = '0' + startMinutes;
        }
        if (startSeconds < 10) {
            startSeconds = '0' + startSeconds;
        }

        $('.tic-tac-wrap> .tic').text((startMinutes));
        $('.tic-tac-wrap> .tac').text((startSeconds));

    }, 1000)
    // ----------------------------------------------------------------------------------------------

    // special for dima

    $('.convert-otboy-cards').click(function () {
        var stash = $(this).clone();
        $('#swap-conteiner').append(stash);


        $.fancybox([{
            href: '#swap-conteiner',
            openEffect: 'elastic',
            closeEffect: 'elastic',
            wrapCSS: 'fancybox-card-open',
            'closeBtn': false,
            fitToView: true,
            padding: '0',
            afterClose: function () {
                $('#swap-conteiner').html("");
            }

        }]);

    });

    //------------------------------------------------------------------------------------------------------
    var maxHealth = 410;
    var minHealth = 0;
    var healthPotoc = 63;

    var potoc = (maxHealth - minHealth) * (100 - healthPotoc) / 100;
    $('#bar-user').css('stroke-dashoffset', potoc + 'px');
    $('#bar-user').css('stroke', '#02FF2D');

    // -------------------------------------------------------------------------------------------------------

    $('.start-search-game').click(function () {
        $.fancybox([{
            href: '#choose-rase-block',
            openEffect: 'elastic',
            closeEffect: 'elastic',
            wrapCSS: 'fancybox-card-open',
            'closeBtn': false,
            fitToView: true,
            padding: '0'
        }]);
    });

    // start timer

    var startM = 0;
    var startS = 0;
    setInterval(function () {
        startS = startS + 1;
        if (startS > 59) {
            startS = 0;
            startM = startM + 1;
        }

        if (startM < 10) {
            $('.timer-go .timer-m-search').html('0' + startM);
        }
        else {
            $('.timer-go .timer-m-search').html(startM);
        }
        if (startS < 10) {
            $('.timer-go .timer-s-search').html('0' + startS);
        }
        else {
            $('.timer-go .timer-s-search').html(startS);
        }
    }, 1000);


    //});



}


$(window).resize(function () {
    resetards();
})

$(document).ready(function () {

    randomTime();
    resetards();
    treshSupportForFix();

})


/* script from server */

/*
function sendChangeSelected() {


    var selectedValue = $('#select-humanity').val();

    var cardSelector = {
        action: "change_rasa",
        ras: selectedValue
    }

    $.ajax({
        url: ajaxurl,
        data: cardSelector,
        method: 'POST',
        success: function (data) {
            //console.log(data);
            var res = JSON.parse(data);
            var dataHtmlLeft = res[0];
            var dataHtmlRight = res[1];

            var dataUrlBg = "url(" + res[2] + ")";

            $('.content-card-center-img-wrap').css("background-image", dataUrlBg);
            $("#sortableTwo").empty();
            $("#sortableOne").empty();
            $("#sortableOne").append(dataHtmlLeft);
            $("#sortableTwo").append(dataHtmlRight);
            reloadParameters();
            var timer6 = null;
            timer6 = setTimeout(function () {
                $.fancybox.close("#call-popup");
            }, 300);
        }
    });
}
*/

/*
function sendChangeSelectedMarket() {
    var selectedValue = $('#select-humanity').val();
    reloadParameters();
    var cardSelector = {
        action: "change_rasa",
        value: selectedValue
    }

    $.ajax({
        url: ajaxurl,
        data: cardSelector,
        method: 'POST',
        success: function (data) {
            //  var res= JSON.parse(data);
            // var dataHtml = res[0];
            var propka = 'http://cs611.vk.me/u76064894/a_fce8b7fa.jpg';

            var dataUrlBg = "url(" + propka + ")";

            $('.content-card-rasa').css("background-image", dataUrlBg);
            $(".market-cards-items-wrap").empty();
            //$(".market-cards-items-wrap").append(dataHtml);

            var timer5 = null;
            timer5 = setTimeout(function () {
                $.fancybox.close("#call-popup");
            }, 300);
        }
    });
}
*/
/*
function confirmByeCard(cardSelect, confirmStatus) {
    var timer1 = null;
    timer1 = setTimeout(function () {
        $.fancybox.close("#call_success");
    }, 300);


    if (confirmStatus) {
        fancyScriptforBye('#call-popup');
        var cardSelector = {
            action: "by_cart",
            cart_id: cardSelect
        }

        $.ajax({
            url: ajaxurl,
            data: cardSelector,
            method: 'POST',
            success: function (data) {
                var res = JSON.parse(data);
                if (res[0] == 1) {
                    var cardpriceG = parseInt($("li[data-cart-id=" + cardSelect + "]").closest('.market-cards-item-main').children('.market-card-item-price').children('.block-for-resourse ').children('.resurses').children('.gold').text());
                    // reload parametr ------------------------------------------------------------------------
                    var cardpriceS = parseInt($("li[data-cart-id=" + cardSelect + "]").closest('.market-cards-item-main').children('.market-card-item-price').children('.block-for-resourse ').children('.resurses').children('.silver').text());

                    var oldParametrG = parseInt($('.rating').find(".gold").text());
                    var oldParametrS = parseInt($('.rating').find(".silver").text());
                    $('.rating').find(".gold").text(oldParametrG - cardpriceG);
                    $('.rating').find(".silver").text(oldParametrS - cardpriceS);

                }
                var timer2 = null;
                // $(".reqest-for-tigl-tigl").text(res[0]);
                $(".reqest-for-tigl-tigl").text('res[1]');

                timer2 = setTimeout(function () {
                    $.fancybox.close("#call-popup");
                }, 1000);

                fancyScriptforBye('#reqest-false');
                var timer3 = null;
                timer3 = setTimeout(function () {
                    console.log('done2');
                    $.fancybox.close("#reqest-false");
                }, 1000);
            }
        });
    }

}
*/
/*
function reloadParameters() {
    listItemsTWO_START = $("#sortableTwo").children().length;
    listItemsONE_START = $("#sortableOne").children().length;

    $('.card-in-stash').text(listItemsONE_START);

    var variorsInStash = listItemsONE_START   //$("#sortableOne>.content-card-item[data-relative='varior']").length;
    $('.variors-in-stash').text(variorsInStash + ' / 24');



    var specialsInStash = $("#sortableOne>.content-card-item[data-relative='special']").length;
    $('.specials-in-stash').text(specialsInStash + ' / 10');

    var stashPover = 0;
    $("#sortableOne>.content-card-item").each(function (e) {
        stashPover = stashPover + parseInt($(this).attr('data-power'));
    });
    $('.stash-power').text(stashPover);

    var dataArrayLage = [0, 50, 51, 100, 101, 130, 131, 160];
    var counter = -1;

    var i = 0;
    while (dataArrayLage.length - 1 >= i) {
        if (stashPover > parseInt(dataArrayLage[i]) && stashPover < parseInt(dataArrayLage[i + 1])) {
            counter = (i) / 2 + 1;
            break;
        }
        i += 2;
    }
    if (counter == -1) counter = dataArrayLage.length / 2 + 1;

    $('.leage-of-bastard').text(counter);

    // error block
    if (variorsInStash > 24) {
        $('.variors-in-stash').addClass('kliping-words');
        $('.variors-in-stash').siblings().addClass('kliping-words');
    }
    if (variorsInStash <= 24) {
        $('.variors-in-stash').removeClass('kliping-words');
        $('.variors-in-stash').siblings().removeClass('kliping-words');
    }
    if (specialsInStash > 10) {
        $('.specials-in-stash').addClass('kliping-words');
        $('.specials-in-stash').siblings().addClass('kliping-words');
    }
    if (specialsInStash <= 10) {
        $('.specials-in-stash').removeClass('kliping-words');
        $('.specials-in-stash').siblings().removeClass('kliping-words');
    }
}
*/
/*
function marketSelectedRase() {
    var select_market = $('#select-value-market').val();
    var cardSelector = {
        action: "market_selected",
        market_select: select_market
    }

    $.ajax({
        url: ajaxurl,
        data: cardSelector,
        method: 'POST',
        success: function (data) {

            console.log('data send');
            var res = JSON.parse(data);

            // var dataHtmlLeft = res[0];
            // var dataHtmlRight = res[1];

            // var dataUrlBg = "url(" + res[2] +")";
            // $('.content-card-center-img-wrap').css("background-image", dataUrlBg);
            // $("#sortableOne").append(dataHtml);
            // $("#sortableTwo").append(dataHtml);

        }
    });
}
*/
/*
function fancyScriptforBye(selector) {
    $.fancybox.open(selector, {
        padding: 0,
        fitToView: false,
        wrapCSS: "call-popup",
        autoSize: true,
        'helpers': {
            'overlay': { 'closeClick': false }
        },
        afterClose: function () {
            $('form').trigger("reset");
            clearTimeout();
        }
    });

}

var listItemsTWO_START;
var listItemsONE_START;
var cardSelect;
*/
$(window).load(function () {
    //reloadParameters();
});

$(document).ready(function () {

    //.parent('.market-cards-item-main').children('.market-card-item-price').children('.block-for-resourse ').children('.resurses').children('.gold')
    //console.log($("li[data-cart-id="+666 +"]").closest('.market-cards-item-main').children('.market-card-item-price').children('.block-for-resourse ').children('.resurses').children('.gold').text()) ;


    /*$("#select-humanity").change(function () {
        fancyScriptforBye('#call-popup');
        sendChangeSelected();

    });*/
    /*
    $("#select-value-market").change(function () {
        fancyScriptforBye('#call-popup');
        sendChangeSelectedMarket();

    });
    */
    /*
    $("#sortableOne").sortable({
        forcePlaceholderSize: true,
        receive: function (event, ui) {
            reloadParameters();
            var cart_id = $(ui.item).attr("data-cart-id");
            var cardSelector = {
                action: "drag_cart_to_left",
                cart_id: cart_id
            };
            $.ajax({
                url: ajaxurl,
                data: cardSelector,
                method: 'POST',
                success: function () {
                    //   reloadParameters();
                }
            });
        },
        stop: function (event, ui) { }
    });

    $("#sortableTwo").sortable({
        forcePlaceholderSize: true,
        receive: function (event, ui) {
            reloadParameters();
            var cart_id = $(ui.item).attr("data-cart-id");//ui.item.context.id ;
            var cardSelector = {
                action: "drag_cart_to_right",
                cart_id: cart_id
            };
            $.ajax({
                url: ajaxurl,
                data: cardSelector,
                method: 'POST',
                success: function (data) {
                    //console.log(data);
                    //   reloadParameters();
                }
            });
        },
        stop: function (event, ui) { }
    });
    */

    /*$('.market-card-item-buy').click(function () {
        fancyScriptforBye('#call_success');
        cardSelect = $(this).parent('.market-cards-item-main').children('.market-cards-item-img').children('ul').children('li').attr('data-cart-id');
        // chooseCard(cardSelect);
    })*/
    /*
    $('.button-accept').click(function () {

        confirmByeCard(cardSelect, true);
    })

    $('.button-not-accept').click(function () {
        confirmByeCard(cardSelect, false);
    })
    */
})

/* script from server */