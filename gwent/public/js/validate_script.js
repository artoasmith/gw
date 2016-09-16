function validate(form, options) {
    var setings = {
        errorFunction: null,
        submitFunction: null,
        highlightFunction: null,
        unhighlightFunction: null
    }
    $.extend(setings, options);

    var $form = $(form);

    if ($form.length && $form.attr('novalidate') === undefined) {
        $form.on('submit', function (e) {
            e.preventDefault();
        });

        $form.validate({
            errorClass: 'errorText',
            focusCleanup: true,
            focusInvalid: false,
            ignore: ".ignore",
            invalidHandler: function (event, validator) {
                if (typeof (setings.errorFunction) === 'function') {
                    setings.errorFunction(form);
                }
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form_input'));
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('error');
                $(element).closest('.form_row').addClass('error').removeClass('valid');
                if (typeof (setings.highlightFunction) === 'function') {
                    setings.highlightFunction(form);
                }
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('error');
                if ($(element).closest('.form_row').is('.error')) {
                    $(element).closest('.form_row').removeClass('error').addClass('valid');
                }
                if (typeof (setings.unhighlightFunction) === 'function') {
                    setings.unhighlightFunction(form);
                }
            },
            submitHandler: function (form) {
                if (typeof (setings.submitFunction) === 'function') {
                    setings.submitFunction(form);
                } else {
                    $form[0].submit();
                }
            }
        });

        $('[required]', $form).each(function () {

            if (language == 'ru') {
                $(this).rules("add", {
                    required: true,
                    messages: {
                        required: "Вы пропустили"
                    }
                });
            };

            if (language == 'ua') {
                $(this).rules("add", {
                    required: true,
                    messages: {
                        required: "Ви пропустили"
                    }
                });
            };
            if (language == 'en') {
                $(this).rules("add", {
                    required: true,
                    messages: {
                        required: "You missed"
                    }
                });
            };


        });

        if ($('[type="email"]', $form).length) {
            if (language == 'ru') {
                $('[type="email"]', $form).rules("add",
                    {
                        messages: {
                            email: "Невалидный email"
                        }
                    });
            };
            if (language == 'ua') {
                $('[type="email"]', $form).rules("add",
                    {
                        messages: {
                            email: "Неправильний email"
                        }
                    });
            };
            if (language == 'en') {
                $('[type="email"]', $form).rules("add",
                    {
                        messages: {
                            email: "Invalid email"
                        }
                    });
            };


        }

        if ($('.tel-mask[required]', $form).length) {
            $('.tel-mask[required]', $form).rules("add",
                {
                    messages: {
                        required: "Введите номер мобильного телефона."
                    }
                });
        };
        $('[type="password"]', $form).each(function () {
            if (language == 'ru') {
                if ($(this).is("#re_password") == true) {
                    $(this).rules("add", {
                        minlength: 1,
                        equalTo: "#password",
                        messages: {

                            equalTo: "Неверный пароль.",
                            minlength: "Недостаточно символов."
                        }
                    });
                }
            };
            if (language == 'ua') {
                if ($(this).is("#re_password") == true) {
                    $(this).rules("add", {
                        minlength: 1,
                        equalTo: "#password",
                        messages: {

                            equalTo: "Неправильний пароль.",
                            minlength: "Недостатньо символів."
                        }
                    });
                }
            };
            if (language == 'en') {
                if ($(this).is("#re_password") == true) {
                    $(this).rules("add", {
                        minlength: 1,
                        equalTo: "#password",
                        messages: {
                            equalTo: "Invalid password.",
                            minlength: "Insufficient number of characters in the password"
                        }
                    });
                }
            };
        });
    }
}

// вихід
function exitGvent() {
    var user_logout = {
        action : "exit"
    };
    $.ajax({
        url: ajaxurl,
        data: user_logout,
        method: 'POST',
        success: function (data) {
            if ( parseInt(data) == 1) {
                var url = 'index.html';
                document.location.replace(url);
            }
        }
    });
}

// вхід

function logIn(form) {
    var thisForm = $(form);
    var formSur = thisForm.serialize();
    var replacement;

    //ajaxurl = 'js/json/login_false.json';
   // ajaxurl = 'js/json/login_true.json';
    $.ajax({
        url: '/login',
        data: formSur,
        method: 'POST',
        success: function (data) {

            var res = JSON.parse(data);

            //var res = data;

            if ( parseInt(res.answer) === 0 ) {

                $('form').trigger("reset");
                $(form).find('.error-text').removeClass('hide-it');
                $(form).find('.error-text').addClass('show');
                //$('.error-text').text(res.message);

            }
            else if ( parseInt(res.answer) == 1 ) {

                location = '/';
                //document.location.replace(res.location);

            }
        }
    });
}


// форма востановления пароля

function reset_pass(form) {
    var thisForm = $(form);
    var formSur = thisForm.serialize();

   // ajaxurl = 'js/json/reset_pass_false.json';
   // ajaxurl = 'js/json/reset_pass_true.json';

    $.ajax({
        url: ajaxurl,
        data: formSur,
        method: 'POST',
        success: function (data) {
            var res = JSON.parse(data);

            //var res = data;

             //console.log(res);
            if ( parseInt(res.answer) === 0 ) {
                $('form').trigger("reset");
                $(form).find('.error-text').removeClass('hide-it');
                $(form).find('.error-text').addClass('show');
                $('.error-text').text(res.message);

            }
            else if ( parseInt(res.answer)  == 1) {
                var url = res.location;
                $('.call-title').text(res.message);
                $('.call-subtitle').text('');
                popNext();
                var timer1 = null;
                timer1 = setTimeout(function () {
                    document.location.replace(url);
                }, 2000);
            }
        }
    });
}


// реєстрація

function registerForm(form) {

    if(grecaptcha.getResponse().length === 0){
        $(form).find('.reCaptcha-wrap>div>div>div').addClass('error-recapture');
    } else if(!$('#linka-check').is( ":checked" )){
       // console.log('not check');
        // open menu width

    } /*else if( $(form).find('input[name=reg_password]').val().length < 4 && $(form).find('input[name=confirm_reg_password]').val().length < 4 ){
        $(form).find('input[type=password]').addClass('error-field');
    } */ else{

        var thisForm = $(form);
        var formSur = thisForm.serialize();

        //ajaxurl = 'js/json/register_true.json';
        //ajaxurl = 'js/json/register_false_email.json';
        //ajaxurl = 'js/json/register_false_login.json';

        $.ajax({
            url: '/registration',
            data: formSur,
            method: 'POST',
            success: function (data) {
                var res = JSON.parse(data);
               // var res = data;

                if ( parseInt(res.answer) == 0) {
                    if (res.error_email){
                        thisForm.find('input[type="email"]').closest('.form_row').addClass('error');
                        thisForm.find('input[type="email"]').closest('.form_row').removeClass('valid');
                        thisForm.find('input[type="email"]').addClass('false-field');
                    }
                    if (res.error_login){
                        thisForm.find('input[name="reg_login"]').closest('.form_row').addClass('error');
                        thisForm.find('input[name="reg_login"]').closest('.form_row').removeClass('valid');
                        thisForm.find('input[name="reg_login"]').addClass('false-field');
                    }
                // $('form').trigger("reset");
                    thisForm.find('.error-text').removeClass('hide-it');
                    thisForm.find('.error-text').addClass('show');
                    $('.error-text').text(res.message);

                }
                else if (res.answer == 1) {
                    /*var url = res.location;
                    $('.call-title').text(res.message);
                    $('.call-subtitle').text('');

                    popNext();

                    var timer1 = null;

                    timer1 = setTimeout(function () {
                        document.location.replace(url);
                    }, 2000);*/
                    location = '/';

                }
            }
        });


    }


}


// зміна данних користувача

function settingsForm(form) {
    var myForm = $(form);
    var formData = new FormData(form);
    var swicher;

    //ajaxurl = 'js/json/settings_true.json';
    //ajaxurl = 'js/json/settings_false.json';

    $.ajax({
        url: ajaxurl,
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        method: 'POST',
        success: function (data) {
            var res = JSON.parse(data);

            if ( parseInt(res.answer) === 0) {
                swicher = 0;

                $('form').trigger("reset");
                $(form).find('.error-text').removeClass('hide-it');
                $(form).find('.error-text').addClass('show');
                $('.error-text').text(res.message);
            }
            else if ( parseInt(res.answer) == 1) {
                swicher = 1;

                loadingStartDogFB();

                if (res.imageUrl != '') {
                    userAvatar = res.imageUrl;
                    $('#avatarImg').attr('src', res.imageUrl);
                    $('.user-image img').attr('src', res.imageUrl);
                };

                $(form).find('input[name=current_password]').val('');
                $(form).find('input[name=settings_pass]').val('');
                $(form).find('input[name=settings_pass_confirm]').val('');

                if (language == 'ru') {
                    $('.call-title').text('Ваши данные успешно изменены');
                    $('.call-subtitle').text('');
                };
                if (language == 'ua') {
                    $('.call-title').text('Ваші дані успішно змінені');
                    $('.call-subtitle').text('');
                };
                if (language == 'en') {
                    $('.call-title').text('Your data was successfully changed');
                    $('.call-subtitle').text('');
                };

            }
            var timer = null;
        }
    }).done(function () {
        if (swicher == 1) {
            timer = setTimeout(function () {
                $.fancybox.close("#call-popup");
                popNext();
            }, 200);
        }
    });


}

// техническая поддержка
function supportForm(form) {
     if(grecaptcha.getResponse().length != 0){

        var thisForm = $(form);
        var formSur = thisForm.serialize();
    // popNext();

    // ajaxurl = 'js/json/support_true.json';
    // ajaxurl = 'js/json/support_false.json';

        $.ajax({
            url: ajaxurl,
            data: formSur,
            processData: false,
            method: 'POST',
            success: function (data) {

              var res = JSON.parse(data);
             //var res = data;

                if ( parseInt(res.answer) === 0) {
                    var timer1 = null;

                    timer1 = setTimeout(function () {
                        $.fancybox.close("#call-popup");
                    }, 500);

                    //$('form').trigger("reset");
                // $.fancybox.close("#call-popup");
                    //$('form').trigger("reset");
                    $(form).find('.error-text').removeClass('hide-it');
                    $(form).find('.error-text').addClass('show');
                    $('.error-text').text(res.message);
                    //document.location.replace();
                }
                else if ( parseInt(res.answer) == 1) {

                    $('form').trigger("reset");

                    var url = res.location;
                    var textMassage = res.message;

                    $('.call-title').text(textMassage);

                    popResult();

                    var timer = null;

                    timer = setTimeout(function () {
                        document.location.replace(url);
                        $.fancybox.close("#call_success");
                    }, 2000);
                    $('form').trigger("reset");
                }
            }
        }).done(function () {
            //console.log("finish");
        });

     }
    function popNext() {

        $.fancybox.open("#call-popup", {
            padding: 0,
            fitToView: false,
            wrapCSS: "call-popup",
            autoSize: true,
            'helpers': {
                'overlay': { 'closeClick': false }
            },
            afterClose: function () {
               // $('form').trigger("reset");
            }
        });

    }

    function popResult() {
        $.fancybox.open("#call_success", {
            padding: 0,
            fitToView: false,
            wrapCSS: "call-popup",
            autoSize: true,
            'helpers': {
                'overlay': { 'closeClick': false }
            },
            afterClose: function () {
                clearTimeout(timer);
            }
        });

    };
}



function popNext() {

    $.fancybox.open("#call_success", {
        padding: 0,
        fitToView: false,
        'closeBtn': false,
        wrapCSS: "call-popup",
        'showCloseButton':false,
        autoSize: true,
        afterClose: function () {
            $('form').trigger("reset");
            clearTimeout(timer1);
        }
    });

    var timer1 = null;

    timer1 = setTimeout(function () {
        $.fancybox.close("#call_success");
    }, 2000);

}

function loadingStartDogFB() {
    $.fancybox.open("#call-popup", {
        padding: 0,
        fitToView: false,
        wrapCSS: "call-popup",
        autoSize: true,
        'helpers': {
            'overlay': { 'closeClick': false }
        },
        afterClose: function () {
            $('form').trigger("reset");
        }
    });
}


function showErrorField(form) {

    $(form).find('.error-text').addClass('show');

}


function showErrorFieldPassFormAgain(form) {
    var errorLength = $(form).find('.form_row.error').length;
    if (errorLength !== 0) {
        $(form).find('.error-text').removeClass('hide-it');
    }
    else {
        $(form).find('.error-text').addClass('hide-it');
        $('.error-text').text('Неправильно указанный Email');
    }
}

function showHideErrorFieldAgain(form) {
    var errorLength = $(form).find('.form_row.error').length;
    if (errorLength !== 0) {
        $(form).find('.error-text').removeClass('hide-it');
    } else {
        $(form).find('.error-text').addClass('hide-it');

        if (language == 'ru') {
            $('.error-text').text('Неправильно указан логин и/или пароль');
        };
        if (language == 'ua') {
            $('.error-text').text('Неправильно вказаний логін і/або пароль');
        };
        if (language == 'en') {
            $('.error-text').text('The username or password you entered is incorrect');
        };
    }

}

function onchangeTestDate() {

    var date = new Date();
    var str = $('#datepicker').val();
    var inputDate = str.split('/', 3);
    $('.date-select').addClass('error');
    $('.date-select').closest('.form_row').removeClass('valid').addClass('error');

    if (inputDate[2] <= date.getFullYear()) {
        if (inputDate[0] <= (date.getMonth() + 1)) {
            if (inputDate[1] <= date.getDate()) {
                $('.date-select').closest('.form_row').removeClass('error').addClass('valid');
                $('.date-select').removeClass('error');
            }
            else {
                $('#datepicker').val('');
            };
        }
        else {
            $('#datepicker').val('');
        };
    }
    else {
        //(date.getMonth()+1)+ '/' + date.getDate() +'/' + date.getFullYear()
        $('#datepicker').val('');
    };
}


function setDatePickerRussian() {
    $.datepicker.regional['ru'] = {
        closeText: 'Закрыть',
        prevText: '&#x3c;Пред',
        nextText: 'След&#x3e;',
        currentText: 'Сегодня',
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
        dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false
    };

    $.datepicker.setDefaults($.datepicker.regional['ru']);
}

function setDatePickerUkraine() {
    $.datepicker.regional['ua'] = {
        clearText: 'Очистити', clearStatus: '',
        closeText: 'Закрити', closeStatus: '',
        prevText: '&lt;&lt;', prevStatus: '',
        nextText: '&gt;&gt;', nextStatus: '',
        currentText: 'Сьогодні', currentStatus: '',
        monthNames: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень',
            'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
        monthNamesShort: ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер',
            'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'],
        monthStatus: '', yearStatus: '',
        weekHeader: 'Не', weekStatus: '',
        dayNames: ['неділя', 'понеділок', 'вівторок', 'середа', 'четвер', 'пятниця', 'суббота'],
        dayNamesShort: ['нед', 'пнд', 'вів', 'срд', 'чтв', 'птн', 'сбт'],
        dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayStatus: 'DD', dateStatus: 'D, M d',
        dateFormat: 'dd/mm/yy', firstDay: 1,
        initStatus: '', isRTL: false
    };
    $.datepicker.setDefaults($.datepicker.regional['ua']);
}

function setDatePickerEnglish() {
    $.datepicker.regional['en'] = {
        closeText: 'Done',
        prevText: 'Prev',
        nextText: 'Next',
        currentText: 'Today',
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'],
        monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        weekHeader: 'Wk',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['en']);
}

function reloadLighting() {
    setInterval(function () {
        var lighting = {
            action: "energyStatus"
        }
        $.ajax({
            url: ajaxurl,
            data: lighting,
            method: 'POST',
            success: function (data) {
                var res = JSON.parse(data);
                $(".lighting").text(res[0]);
            }
        });
    }, interval_update * 1000);
}



var interval_update = 5000;
// Перевірка розширення файлу
(function ($) {
    $.fn.checkFileType = function (options) {
        var defaults = {
            allowedExtensions: [],
            success: function () { },
            error: function () { }
        };
        options = $.extend(defaults, options);

        return this.each(function () {

            $(this).on('change', function () {
                var value = $(this).val(),
                    file = value.toLowerCase(),
                    extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, options.allowedExtensions) == -1) {
                    options.error();
                    $(this).focus();
                } else {
                    options.success();
                }
            });
        });
    };

})(jQuery);

// buy more scripts

    function buyMorePopup(){

        $('.convert-resurses .button-plus').fancybox({
            autoSize:true,
            fitToView:true,
            padding:0,
            wrapCSS:'fancybox-buy-more',
            beforeLoad:function(){
                if($('.buy-popup').is('#buy-silver')){

                    buyingSilver($('.buy-silver-form input'));

                }
            }
        });

        // callback on gold buying submit

            function validateGoldBuying(form){

                var thisForm = $(form);
                var formSur = thisForm.serialize();

                $.ajax({
                    url:ajaxurl, //'js/json/buy_gold_page_path.json'
                    data:{action:'buy_gold', formData:formSur},
                    method:'POST',
                    success:function(){

                        window.location.pathname = 'buy_gold.html';

                    }
                })

            }

        // /callback on gold buying submit

        // validate on gold buying form

            validate('.buy-gold-form', {submitFunction:validateGoldBuying});

        // /validate on gold buying form

        // callback on silver buying form

            function validateSilverBuying(form){

                var thisForm = $(form);
                var goldCountExchange = thisForm.find('input[name="buy_silver"]').val();

                $.ajax({
                    url: ajaxurl, //exchange_silver_error.json //js/json/exchange_silver_error.json
                    data:{action:'exchange_gold_to_silver', formData:goldCountExchange},
                    method:'POST',
                    success:function(data){

                        if(typeof data == 'object'){
                            var buyingSilverData = data;
                        }else{
                            var buyingSilverData = JSON.parse(data);
                        }

                        console.log(buyingSilverData);

                        if(buyingSilverData.change == 0){

                            popupCalling('exchange_error_gold', "exchange-error-wrap", thisForm);

                        }else if(buyingSilverData.change == 1){

                            $('.header-box .resurses .gold').text(buyingSilverData.gold_count);
                            $('.header-box .resurses .silver').text(buyingSilverData.silver_count);

                            $.fancybox.close();

                        }

                    }
                });

            }

        // /callback on silver buying form

        // validate on silver buying

            validate('.buy-silver-form', {submitFunction:validateSilverBuying});

        // /validate on silver buying

        // energy excahnge

            $(document).on('click', '.excahnge-energy .exchange-button-main', function(){

                var type = $(this).data('type');
                var energyCount = $(this).data('energy-count');

                $.ajax({
                    url:ajaxurl, //js/json/energy_exchange_to_'+type+'.json
                    data:{action:'energy_exchange', valueType:type, energyCount:energyCount},
                    method:'POST',
                    success:function(data){

                        console.log(data);

                        if(typeof data == 'object'){
                            var buyingEnergyData = data;
                        }else{
                            var buyingEnergyData = JSON.parse(data);
                        }

                        if(buyingEnergyData.change == 0){

                            popupCalling('exchange_error_'+type, "exchange-error-wrap");

                        }else if(buyingEnergyData.change == 1){

                            $('.header-box .lighting').text(buyingEnergyData.energy);
                            $('.header-box .'+type).text(buyingEnergyData[type]);

                            $.fancybox.close();

                        }
                    }
                });

            });

        // / energy excahnge

    }

    // call popup by ajax

        function popupCalling(popupId, popupWrap, thisForm){

            var timer = null;

            $.fancybox.open('#'+popupId, {
                padding:0,
                fitToView:false,
                'closeBtn': false,
                wrapCSS: popupWrap,
                autoSize: true,
                afterClose: function () {
                    if(thisForm != 'undefined'){
                        thisForm.trigger("reset");
                    }
                    clearTimeout(timer);
                }
            });

            timer = setTimeout(function(){
                $.fancybox.close();
            },2000);

        }

    // /call popup by ajax

    //buying silver scripts

        function buyingSilver(item){

            var inputVal = parseInt(item.val());
            var textBlockRadion = item.parents('.form-row').find('.exchange-value');
            var countBlockCoof = textBlockRadion.attr('data-exchange-koof');

            var silverCount = inputVal * countBlockCoof;

            textBlockRadion.text(silverCount.toFixed());

        }

    // /buying silver scripts

// /buy more scripts

// market scripts

    function marketScripts(){

        if($('.market-page').length){

            /* change fraction in market */

                function marketChangeFraction(){

                    /* ajax function by changing fraction */

                        function marketAjax(marketFractionValue){

                            $('.market-page').addClass('loading');

                            /* in what we market (cards or effects) */

                            var effects = false;

                            if($('.effect-market-wrap').length){
                                marketFractionValue = marketFractionValue + '_effects';
                                effects = true;
                            }

                            /* /in what we market (cards or effer) */

                            if(effects){

                                /* if we in effects market change rase */

                                    $.ajax({
                                        url:ajaxurl, //'js/json/market_'+marketFractionValue+'.json' //'js/json/market_'+marketFractionValue+'_effects.json'
                                        data:{action:'market_effects_by_fraction', fraction:marketFractionValue},
                                        success:function(data){

                                            if(typeof data == 'object'){
                                                var marketData = data;
                                            }else{
                                                var marketData = JSON.parse(data);
                                            }

                                            /* removing market_effect items */

                                                $('.main-table tr').remove();

                                            /* /removing market_effect items */

                                            var marketDataLength = 0;

                                            marketDataLength = marketData.effects.length;

                                            var marketDataLogo = marketData.logo;

                                            if(marketDataLogo != 'none'){
                                                $('.market-selection .select-rase-img').addClass('active').find('img').attr('src', marketDataLogo);
                                            }else{
                                               $('.market-selection .select-rase-img').removeClass('active');
                                            }

                                            var mainTable = $('.main-table');

                                            mainTable.append('<tr><th class="no-border"></th><th></th><th>Название</th><th>Описание</th><th>затраты энергии</th><th colspan="2"><table><tr><th colspan="2">Цена</th></tr><tr><th>Золото</th><th>Серебро</th></tr></table></th><th>Статус</th><th>Дата  окончания</th></tr>');

                                            marketData.effects.forEach(function(item, index){

                                                var unactive = '';
                                                var canBuy = 'canot-buy';

                                                if(!item.status == "false" || !item.status){
                                                    unactive = "disabled";
                                                    canBuy = '';
                                                }

                                                mainTable.append('<tr><td class="effect-buy no-border"><a href="#" class="button-plus '+canBuy+'" data-count='+item.buy_value+' data-effect-id='+item.effect_id+'></a></td><td class="effect-img"><img src='+item.img+' alt="" /></td><td class="effect-title">'+item.title+'</td><td class="effect-descript">'+item.descript+'</td><td class="energy-effect">'+item.energy_cost+'</td><td class="gold-tableCell">'+item.gold_cost+'</td><td class="silver-tableCell">'+item.silver_cost+'</td><td class="market-status-wrap"><div class="market-status '+unactive+'"><span></span></div></td><td class="effect-date">'+item.ending_date+'</td></tr>');

                                                if(index == (marketDataLength - 1)){

                                                    $('.loading').removeClass('loading');

                                                }

                                            });

                                        }
                                    });

                                /* /if we in effects market change rase */

                            }else{

                                /* if we in cards market change rase */

                                    $.ajax({
                                        url:ajaxurl, // 'js/json/market_'+marketFractionValue+'.json' //'js/json/market_'+marketFractionValue+'_effects.json'
                                        data:{action:'market_cards_by_fraction', fraction:marketFractionValue},
                                        success:function(data){

                                            if(typeof data == 'object'){
                                                var marketData = data;
                                            }else{
                                                var marketData = JSON.parse(data);
                                            }

                                            /* removing cards from market */

                                                $('.market-card-wrap').remove();

                                            /* /removing cards from market */

                                            var marketDataLength = 0;

                                            marketDataLength = marketData.cards.length;

                                            var marketDataLogo = marketData.logo;

                                            if(marketDataLogo != 'none'){
                                                $('.market-selection .select-rase-img').addClass('active').find('img').attr('src', marketDataLogo);
                                            }else{
                                               $('.market-selection .select-rase-img').removeClass('active');
                                            }

                                            marketData.cards.forEach(function(item, index){

                                                var special = '';
                                                if(typeof item.special != 'undefined'){
                                                    special = 'special';
                                                }

                                                var cardPriceText = '';
                                                if(typeof item.cardPrice.silver != 'undefined'){
                                                    cardPriceText = '<span class="card-silver-price"><img src="images/header_logo_silver.png" alt=""><span class="card-price-value">'+item.cardPrice.silver+'</span></span>';
                                                }
                                                if(typeof item.cardPrice.gold != 'undefined'){
                                                    cardPriceText = cardPriceText + '<span class="card-gold-price"><img src="images/header_logo_gold.png" alt=""><span class="card-price-value">'+item.cardPrice.gold+'</span></span>';
                                                }


                                                if(special == ''){

                                                    var cardRaceImg = '';
                                                    if(typeof item.cardRace != 'undefined' && item.cardRace != 'none'){
                                                        cardRaceImg = '<img src='+item.cardRace+' alt="" />';
                                                    }

                                                    var abilitiesRow = '';
                                                    item.cardAbilities.forEach(function(itemTwo, indexTwo){
                                                        abilitiesRow = abilitiesRow + '<span class="' + itemTwo + '"></span>';
                                                    });

                                                    var lider = '';
                                                    if(typeof item.lider != 'undefined'){
                                                        lider = item.lider;
                                                    }

                                                    $('.market-cards-items-wrap').prepend('<div class="market-card-wrap"><div class="market-card-main-wrap"><div class="content-card-item-main card-popup '+lider+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="label-power-card"><span class="label-power-card-wrap"><span>'+item.cardPower+'</span></span></div><div class="hovered-items"><div class="card-game-status"><div class="card-game-status-role"><span class="'+item.cardRole+'"></span></div><div class="card-game-status-wrap">'+abilitiesRow+'</div></div><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-image-describe">'+cardRaceImg+'</div><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div><div class="card-name">'+item.cardName+'</div><div class="market-card-item-price">'+cardPriceText+'</div><div class="market-card-item-buy"><a href="#" class="button-buy" data-card-id='+item.cardId+'>Купить</a></div></div>');
                                                }else{

                                                    $('.market-cards-items-wrap').prepend('<div class="market-card-wrap"><div class="market-card-main-wrap"><div class="content-card-item-main card-popup '+special+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="hovered-items"><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div><div class="card-name">'+item.cardName+'</div><div class="market-card-item-price">'+cardPriceText+'</div><div class="market-card-item-buy"><a href="#" class="button-buy" data-card-id='+item.cardId+'>Купить</a></div></div>');

                                                }
                                                /* card name one height */

                                                    if(index == (marketDataLength - 1)){

                                                        var cardNameHeight = 0;
                                                        var cardNameLength = $('.card-name').length;
                                                        var cardNameIndex = 0;

                                                        $('.card-name').each(function(){

                                                            if($(this).height() > cardNameHeight){
                                                                cardNameHeight = $(this).height();
                                                            }

                                                            if(cardNameIndex == (cardNameLength-1)){
                                                                $('.card-name').height(cardNameHeight);

                                                                /* remove class loading if all items added & their title height calced */

                                                                    $('.market-page').removeClass('loading');

                                                                /* /remove class loading if all items added & their title height calced */
                                                            }

                                                            cardNameIndex++;

                                                        });
                                                    }

                                                /* /card name one height */

                                            });

                                        }
                                    });

                                /* /if we in cards market change rase */

                            }

                        }

                    /* /ajax function by changing fraction */

                    var marketSelection = $('.market-selection');

                    marketSelection.find('.selection-rase select').change(function(){

                        marketAjax(marketSelection.find('.selection-rase select').val());

                    });

                    marketAjax(marketSelection.find('.selection-rase select').val());

                };

                marketChangeFraction();

            /* /change fraction in market */

            /* market buy card script */

                function marketBuyCard(){

                    var cardId = null;

                    /* buying card fancy opening */

                        $(document).on('click', '.market-card-item-buy .button-buy', function(e){

                            e.preventDefault();

                            cardId = $(this).data('card-id');

                            $.fancybox.open("#call_success",{
                                padding: 0,
                                fitToView: false,
                                wrapCSS: "call-popup",
                                autoSize: true,
                                'helpers': {
                                    'overlay': { 'closeClick': false }
                                },
                                afterClose:function(){
                                    $('#call_success').removeClass('done');
                                    $('#call_success .call-title-message').text('');
                                }
                            });

                        });

                    /* /buying card fancy opening */

                    /* ajax calling if you want buy card */

                        $(document).on('click', '.call-subtitle a', function(e){

                            e.preventDefault();

                            if($(this).is('.button-accept')){

                                $('#call_success').addClass('loading');

                                $.ajax({
                                    url:ajaxurl, //'js/json/market_card_buying_false.json' //'js/json/market_card_buying_true.json'
                                    data:{action:'market_cards_buying', cardId:cardId},
                                    success:function(data){

                                        if(typeof data == 'object'){
                                            var buyingData = data;
                                        }else{
                                            var buyingData = JSON.parse(data);
                                        }

                                        if(buyingData.buy || buyingData.buy == "true"){

                                            /* successfuly card buying and calling func to remath resurces */

                                            howMuchResursesAjax();

                                            $('#call_success').addClass('done').find('.call-title-message').text('Спасибо за покупку!');
                                            $('#call_success').removeClass('loading');

                                            /* /successfuly card buying and calling func to remath resurces */

                                        }else{

                                            /* error card buying */

                                            $('#call_success').addClass('done').find('.call-title-message').text(buyingData.error);
                                            $('#call_success').removeClass('loading');

                                            /* /error card buying */

                                        }

                                        setTimeout(function(){

                                            $.fancybox.close({
                                                afterClose:function(){
                                                    $('#call_success').removeClass('done');
                                                }
                                            });

                                        }, 1000);

                                    }
                                });
                            }else{

                                $.fancybox.close();

                            }

                        });

                    /* /ajax calling if you want buy card */

                }

                marketBuyCard();

            /* /market buy card script */

            /* market jscrollpane init */

                $('.market-cards-wrap').jScrollPane({
                    contentWidth: '0px',
                    autoReinitialise:true,
                    autoReinitialiseDelay:0,
                    verticalDragMaxHeight:65,
                    verticalDragMinHeight:65,
                    showArrows:true
                });

            /* /market jscrollpane init */

            /* special effects shop */

                var timer=null;

                function closeAnim(item){
                    clearTimeout(timer);
                    item.addClass('disable-anim');
                    timer = setTimeout(function(){
                        $('.market-status').removeClass('disable-anim');
                    },500);
                }

                $(document).on('click', '.market-status.disabled, .market-status.disabled-styles', function(event) {
                    closeAnim($(this));
                });

                function chekedStatus(){

                    /* send ajax when chouse or disable magical effect */

                        function checkedStatusAjax(effectId, chouseParam){

                            $.ajax({
                                url:ajaxurl, //any json for test
                                data:{magicEffectId:effectId, chouseEffect:chouseParam},
                                method:'POST',
                                success:function(){
                                    $('.market-status').removeClass('pause');
                                }
                            });

                        }

                    /* /send ajax when chouse or disable magical effect */

                    /* click on effect status */

                        $(document).on('click', '.market-status:not(.disabled, .disabled-styles, .pause)', function(event) {

                            var parent = $(this).parents('tr');
                            var effectId = parent.find('.button-plus').attr('data-effect-id');

                            if($('.market-status.active').length!==3 && !$(this).is('.active') ){
                                $(this).addClass('active');
                                if($('.market-status.active').length == 3){
                                    $('.market-status:not(.active)').addClass('disabled-styles');
                                }
                                $('.market-status').addClass('pause');

                                checkedStatusAjax(effectId, 'add');

                            }else if($(this).is('.active')){
                                $(this).removeClass('active');
                                if($('.market-status.disabled-styles').length){
                                    $('.market-status.disabled-styles').removeClass('disabled-styles');
                                }
                                $('.market-status').addClass('pause');

                                checkedStatusAjax(effectId, 'remove');

                            }else{
                                closeAnim($(this));
                            }

                        });

                    /* click on effect status */

                }

                chekedStatus();

            /* /special effects shop */

            /* buy effect */

                function marketBuyEffect(){

                    var effectId = null;

                    $(document).on('click', '.effect-buy .button-plus:not(.canot-buy)', function(e){

                        e.preventDefault();

                        var count = $(this).data('count');
                        effectId = $(this).data('effect-id');

                        $.fancybox.open('#buy_effect',{
                            fitToView:true,
                            autoSize:true,
                            padding:0,
                            wrapCSS:'buy-effect-popup-main',
                            'helpers': {
                                'overlay': { 'closeClick': false }
                            },
                            beforeLoad:function(){
                                $('#buy_effect .title-small span').text(count);
                            },
                            afterClose:function(){
                                $('.buy-effect').removeClass('done');
                                $('.buy-effect .title-big').text('Покупка');
                            }
                        });

                    });

                    /* refuse buying effect */

                        $(document).on('click', '.denie-buy a', function(e){

                            e.preventDefault();

                            $.fancybox.close();

                        });

                    /* refuse buying effect */

                    /* confirm buying effect */

                        $(document).on('click','.confirm-buy a',function(e){

                            e.preventDefault();

                            $('.buy_effect').addClass('loading');

                            $.ajax({
                                url:ajaxurl, //js/json/market_effect_buying_false.json //js/json/market_effect_buying_true.json
                                data:{action:'market_effect_buing', itemId:effectId},
                                method:'POST',
                                success:function(data){

                                    if(typeof data == 'object'){
                                        var buyingEffectData = data;
                                    }else{
                                        var buyingEffectData = JSON.parse(data);
                                    }

                                    if(!buyingEffectData.buying || buyingEffectData.buying == "false"){

                                        /* not enough resources  */

                                            $('.buy-effect').addClass('done').find('.title-big').text(buyingEffectData.buying_text);
                                            $('.buy-effect').removeClass('loading');

                                        /* /not enough resources */

                                    }else{

                                        /* enough resourses */

                                            howMuchResursesAjax();

                                            var parent = $('.button-plus[data-effect-id='+effectId+']').parents('tr');

                                            parent.find('.button-plus').addClass('canot-buy');
                                            parent.find('.market-status').removeClass('disabled');
                                            parent.find('.effect-date').text(buyingEffectData.effect_date_ending);

                                            $('.buy-effect').addClass('done').find('.title-big').text(buyingEffectData.buying_text);
                                            $('.buy-effect').removeClass('loading');

                                        /* /enough resourses */

                                    }

                                    setTimeout(function(){

                                        $.fancybox.close();

                                    }, 1000);

                                }
                            });

                        });

                    /* /confirm buying effect */

                }

                marketBuyEffect();

            /* /buy effect */
        }

    }

// /market scripts

// how much resurses script

    function howMuchResursesAjax(){

        if($('.header-box .resurses').length){

            $.ajax({
                url:ajaxurl, //'js/json/how_much_resurses_user_has.json'
                data:{action:'how_much_resurses_user_has'},
                method:'POST',
                success:function(data){

                    if(typeof data == 'object'){
                        var resursesData = data;
                    }else{
                        var resursesData = JSON.parse(data);
                    }

                    $('.header-box .resurses .gold').text(resursesData.gold);
                    $('.header-box .resurses .silver').text(resursesData.silver);
                    $('.header-box .resurses .lighting').text(resursesData.energy);

                }
            });

        }

    }

// / how much resourses scripts

/* user race */

    function userRace(){

        if($('.user').length){

            /*$.ajax({
                url:ajaxurl, //'js/json/user_race.json'
                data:{action:'what_user_race'},
                method:'POST',
                success:function(data){


                    if(typeof data == 'object'){
                        var userRace = data;
                    }else{
                        var userRace = JSON.parse(data);
                    }

                    $('body').addClass(userRace.race+'-race');

                }
            });*/
        }

    }

/* user race */

/* buy-gold-page */

    function buyGoldInput(item){

        var input = item;
        var coef = input.data('coef');
        var valueHolder = $('#gold-page-price');
        var value = input.val();
        var newVal = (value * coef).toFixed(2);

        valueHolder.text(newVal);

    }

    function buyGoldAjax(){

        $('.gold-page-pay').on('click', function(event) {

            event.preventDefault();

            var valueHolder = $('.buy-gold-page-item-counter input').val();

            $.ajax({
                url:ajaxurl,  // 'js/json/buyGoldAjax_succes.json'  // js/json/buyGoldAjax_error.json
                data:{action:'buyGoldAjax', goldValueBuying:valueHolder},
                method:'POST',
                success:function(data){

                    if(typeof data == 'object'){
                        var goldExcahangeData = data;
                    }else{
                        var goldExcahangeData = JSON.parse(data);
                    }

                    if(goldExcahangeData.answer == 1){

                        howMuchResursesAjax();

                    }

                    $.fancybox.open(goldExcahangeData.buy_gold_message,{
                        'closeBtn': false,
                        wrapCSS: "call-popup-gold",
                        'showCloseButton':false,
                        autoSize: true,
                        fitToView:true,
                        'helpers': {
                            'overlay':{'closeClick':false}
                        }
                    });

                    setTimeout(function(){
                        $.fancybox.close();
                    }, 2000);

                }
            });

        });

    }

/* /buy-gold-page */

/* sortable cards on deck */

    function sortableScripts(){

        /* sortable ui */

            $("#sortableOne").sortable({
                forcePlaceholderSize: true,
                receive: function (event, ui){

                    sortableAjax($(ui.item).attr("data-card-id"), 'add');

                },
                stop: function (event, ui){}
            });

            $("#sortableTwo").sortable({
                forcePlaceholderSize: true,
                receive: function (event, ui){

                    sortableAjax($(ui.item).attr("data-card-id"), 'remove');

                },
                stop: function (event, ui){}
            });

        /* /sortable ui */

        var deckOfCards = {}; //object with cards in main deck
        var leaguePoints = null;

        /* sortable ajax */

            function sortableAjax(cardId, operation){

                var countLeft = 1;
                var countRight = 1;

                var card = $('.content-card-item[data-card-id='+cardId+'] .content-card-item-main');
                var cardPower = 0;
                if(card.find('.label-power-card-wrap').length){
                    cardPower = parseInt(card.eq(0).find('.label-power-card-wrap span').text());
                }
                var deckPower = parseInt($('.deck-cards-power').text());

                $('.content-card-field-wrap').parents('.content-card-wrap-main').addClass('loading');

                /* change data of cards */

                    if(operation == "add"){

                        if(cardId in deckOfCards){
                            deckOfCards[cardId] = deckOfCards[cardId] + 1;
                        }else{
                            deckOfCards[cardId] = 1;
                        }

                        $('.deck-cards-power').text(deckPower+cardPower);

                    }else if (operation == "remove"){

                        if(deckOfCards[cardId] == 1){
                            delete deckOfCards[cardId];
                        }else{
                            deckOfCards[cardId] = deckOfCards[cardId] - 1;
                        }

                        $('.deck-cards-power').text(deckPower-cardPower);

                    }

                /* change data of cards */

                /* change count card */

                    if($('#sortableOne .content-card-item[data-card-id='+cardId+']').length){
                        countLeft = $('#sortableOne .content-card-item[data-card-id='+cardId+']').length;
                        $('#sortableOne .content-card-item[data-card-id='+cardId+'] .card-count').text(countLeft);
                    }
                    if($('#sortableTwo .content-card-item[data-card-id='+cardId+']').length){
                        countRight = $('#sortableTwo .content-card-item[data-card-id='+cardId+']').length;
                        $('#sortableTwo .content-card-item[data-card-id='+cardId+'] .card-count').text(countRight);
                    }

                /* /change count card */

                /* center column change value */

                    $('.deck-card-sum').text($('#sortableOne li').length);
                    $('.deck-warriors .current-value').text($('#sortableOne .content-card-item-main:not(.special)').length);
                    $('.deck-special .current-value').text($('#sortableOne .special').length);
                    $('.deck-liders .current-value').text($('#sortableOne .yellow').length);

                    leagueDeck();

                /* /center column change value */

                $.ajax({
                    url:ajaxurl, // js/json/login_false.json
                    data:{action:'deck_sortable', cardForGame:deckOfCards},
                    method:'POST',
                    success:function(){

                        $('.content-card-field-wrap').parents('.content-card-wrap-main').removeClass('loading');

                    }
                });

            }

        /* /sortable ajax */

        /* center column values */

            function deckCenterColumnSumValues(){

                var cardsLength = $('#sortableOne li').length;
                var warriorLength = $('#sortableOne .content-card-item-main:not(.special)').length;
                var lidersLength = $('#sortableOne .yellow').length;
                var specialLength = $('#sortableOne .special').length;

                var cardsPower = 0;

                $('#sortableOne .content-card-item').each(function(){
                    if($(this).find('.label-power-card-wrap').length){
                        cardsPower+=parseInt($(this).find('.label-power-card-wrap span').text());
                    }
                });

                $('.deck-card-sum').text(cardsLength);
                $('.deck-warriors .current-value').text(warriorLength);
                $('.deck-liders .current-value').text(lidersLength);
                $('.deck-special .current-value').text(specialLength);
                $('.deck-cards-power').text(cardsPower);

                leagueDeck();

            }

        /* /center column values */

        /* league deck */

            function leagueDeck(){

                var deckPower = parseInt($('.deck-cards-power').text());
                var playerLeague = leaguePoints.length+1;
                var currentLeague = 0;
                $('.deck-league').text(playerLeague);

                leaguePoints.forEach(function(leagueValue, leagueIndex){

                    if(deckPower > leagueValue){
                        currentLeague = playerLeague - (leagueIndex + 1);
                        $('.deck-league').text(currentLeague);
                        return false;
                    }

                });

            }

        /* /league deck */

        /* change deck race ajax */

            function deckChangeRace(raceValue){

                if($('.content-card-field').length){

                    $('.content-card-wrap-main').addClass('loading');

                    setTimeout(function(){

                        $.ajax({
                            url:ajaxurl,  // 'js/json/deck_card_'+raceValue+'.json'
                            data:{action:'load_deck_cards',deckRace:raceValue},
                            method:'POST',
                            success:function(data){

                                deckOfCards = {}; // reset deck of cards

                                if(typeof data == 'object'){
                                    var deckRaceChangeData = data;
                                }else{
                                    var deckRaceChangeData = JSON.parse(data);
                                }

                                $('.connected-sortable li').remove();

                                var leftDone = false, rightDone = false;

                                $('.content-card-center-img-wrap img').attr('src', deckRaceChangeData.deck_logo);

                                /* center column min values load */

                                    $('.deck-warriors .min-value').text(deckRaceChangeData.centerColumnVal.warrior);
                                    $('.deck-special .min-value').text(deckRaceChangeData.centerColumnVal.special);
                                    $('.deck-liders .min-value').text(deckRaceChangeData.centerColumnVal.lider);
                                    leaguePoints = deckRaceChangeData.centerColumnVal.leaguePoints;

                                /* /center column min values load */

                                /* deck cards load left */

                                    if(typeof deckRaceChangeData.deck_cards != 'undefined'){

                                        var cardsLengthLeft = deckRaceChangeData.deck_cards.length;

                                        deckRaceChangeData.deck_cards.forEach(function(item, index){

                                            /* adding items to deckOfCards object */

                                                if(item.cardId in deckOfCards){
                                                    deckOfCards[item.cardId] = deckOfCards[item.cardId] + 1;
                                                }else{
                                                    deckOfCards[item.cardId] = 1;
                                                }

                                            /* /adding items to deckOfCards object */

                                            var specialLeft = '';
                                            if(typeof item.special != 'undefined'){
                                                specialLeft = 'special';
                                            }

                                            if(specialLeft == ''){

                                                var liderLeft = '';
                                                if(typeof item.lider != 'undefined'){
                                                    liderLeft = item.lider;
                                                }

                                                var abilitiesRowLeft = '';
                                                item.cardAbilities.forEach(function(itemTwoLeft, indexTwoLeft){
                                                    abilitiesRowLeft = abilitiesRowLeft + '<span class="' + itemTwoLeft + '"></span>';
                                                });

                                                var cardRaceImgLeft = '';
                                                if(typeof item.cardRace != 'undefined' && item.cardRace != 'none'){
                                                    cardRaceImgLeft = '<img src='+item.cardRace+' alt="" />';
                                                }

                                                $('#sortableOne').prepend('<li class="content-card-item ui-sortable-handle" data-card-id='+item.cardId+'><div class="deck-card-popup-wrap"><div class="content-card-item-main card-popup '+liderLeft+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="label-power-card"><span class="label-power-card-wrap"><span>'+item.cardPower+'</span></span></div><div class="hovered-items"><div class="card-game-status"><div class="card-game-status-role"><span class="'+item.cardRole+'"></span></div><div class="card-game-status-wrap">'+abilitiesRowLeft+'</div></div><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-image-describe">'+cardRaceImgLeft+'</div><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div></div><div class="card-count">'+item.cards_count+'</div></li>');
                                            }else{
                                                $('#sortableOne').prepend('<li class="content-card-item ui-sortable-handle" data-card-id='+item.cardId+'><div class="deck-card-popup-wrap"><div class="content-card-item-main card-popup '+specialLeft+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="hovered-items"><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div></div><div class="card-count">'+item.cards_count+'</div></li>');
                                            }

                                            if(index == (cardsLengthLeft-1)){
                                                leftDone = true;
                                                if(leftDone && rightDone){

                                                    deckCenterColumnSumValues();

                                                    setTimeout(function(){
                                                        $('.content-card-wrap-main').removeClass('loading');
                                                    },300);
                                                }
                                            }

                                        });

                                    }else{
                                        leftDone = true;
                                        if(leftDone && rightDone){

                                            deckCenterColumnSumValues();

                                            setTimeout(function(){
                                                $('.content-card-wrap-main').removeClass('loading');
                                            },300);
                                        }
                                    }

                                /* /deck cards load left */

                                /* deck cards load right */

                                    if(typeof deckRaceChangeData.available_cards != 'undefined'){

                                        var cardsLengthRight = deckRaceChangeData.available_cards.length;

                                        deckRaceChangeData.available_cards.forEach(function(item, index){

                                            var specialRight = '';
                                            if(typeof item.special != 'undefined'){
                                                specialRight = 'special';
                                            }

                                            if(specialRight == ''){

                                                var liderRight = '';
                                                if(typeof item.lider != 'undefined'){
                                                    liderRight = item.lider;
                                                }

                                                var abilitiesRowRight = '';
                                                item.cardAbilities.forEach(function(itemTwoRight, indexTwoRight){
                                                    abilitiesRowRight = abilitiesRowRight + '<span class="' + itemTwoRight + '"></span>';
                                                });

                                                var cardRaceImgRight = '<img src='+$('.user img').attr('src')+' alt="" />';
                                                if(typeof item.cardRace != 'undefined' || item.cardRace == 'none'){
                                                    cardRaceImgRight = '<img src='+item.cardRace+' alt="" />';
                                                }

                                                $('#sortableTwo').prepend('<li class="content-card-item ui-sortable-handle" data-card-id='+item.cardId+'><div class="content-card-item-main card-popup '+liderRight+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="label-power-card"><span class="label-power-card-wrap"><span>'+item.cardPower+'</span></span></div><div class="hovered-items"><div class="card-game-status"><div class="card-game-status-role"><span class="'+item.cardRole+'"></span></div><div class="card-game-status-wrap">'+abilitiesRowRight+'</div></div><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-image-describe">'+cardRaceImgRight+'</div><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div><div class="card-count">'+item.cards_count+'</div></li>');
                                            }else{
                                                $('#sortableTwo').prepend('<li class="content-card-item ui-sortable-handle" data-card-id='+item.cardId+'><div class="content-card-item-main card-popup '+specialRight+' '+item.cardFraction+'" style="background-image:url('+item.imageSrc+')" data-race='+item.cardFraction+'><div class="hovered-items"><div class="card-name-property"><p>'+item.cardName+'</p></div><div class="block-describe"><div class="block-text-describe"><div class="block-text-describe-wrap"><div class="block-text-describe-main"><div class="block-text-describe-main-wrap"><p>'+item.cardDescription+'</p></div></div></div></div></div></div></div><div class="card-count">'+item.cards_count+'</div></li>');
                                            }

                                            if(index == (cardsLengthRight-1)){
                                                rightDone = true;
                                                if(leftDone && rightDone){

                                                    deckCenterColumnSumValues();

                                                    setTimeout(function(){
                                                        $('.content-card-wrap-main').removeClass('loading');
                                                    },300);

                                                }
                                            }

                                        });

                                    }else{
                                        rightDone = true;
                                        if(leftDone && rightDone){

                                            deckCenterColumnSumValues();

                                            setTimeout(function(){
                                                $('.content-card-wrap-main').removeClass('loading');
                                            },300);

                                        }
                                    }

                                /* /deck cards load right */

                                /* if user don't have cards of this race */

                                    if(typeof deckRaceChangeData.deck_cards == 'undefined' && typeof deckRaceChangeData.available_cards == 'undefined'){
                                        setTimeout(function(){
                                            $('.content-card-wrap-main').removeClass('loading');
                                        }, 300);
                                    }

                                /* /if user don't have cards of this race */

                            }
                        });

                    }, 300);

                }

            }

        /* /change deck race ajax */

        deckChangeRace($('.content-card-select-wrap select').val());

        $('.content-card-select-wrap select').change(function(){

            deckChangeRace($('.content-card-select-wrap select').val());

        });

        /* deck filter */

            function deckFilter(){

                $(document).on('change', '.content-card-center-checkbox input', function(){
                    if($('.content-card-center-checkbox input:checked').length == 0){
                        $('.content-card-item').removeClass('hide filter-show');
                    }else{
                        $('.content-card-item').addClass('hide');
                        var toogleType = $(this).attr('data-card-type');
                        if($(this).is(':checked')){
                            if(toogleType == 'special'){
                                $('.content-card-item-main.special').parents('.content-card-item').addClass('filter-show');
                            }else if(toogleType == 'neutral'){
                                $('.content-card-item-main.neutral').parents('.content-card-item').addClass('filter-show');
                            }else{
                                $('.content-card-item-main:not(.special, .neutral)').parents('.content-card-item').addClass('filter-show');
                            }
                        }else{
                            if(toogleType == 'special'){
                                $('.content-card-item-main.special').parents('.content-card-item').removeClass('filter-show');
                            }else if(toogleType == 'neutral'){
                                $('.content-card-item-main.neutral').parents('.content-card-item').removeClass('filter-show');
                            }else{
                                $('.content-card-item-main:not(.special, .neutral)').parents('.content-card-item').removeClass('filter-show');
                            }
                        }
                    }
                });

            }

            deckFilter();

        /* /deck filter */

    }

/* /sortable cards on deck */

// call scripts call scripts call scripts call scripts call scripts call scripts call scripts call scripts call scripts

$(window).load(function () {
    if ($(".lighting").length > 0) {
        reloadLighting();
    };
})

$(document).ready(function () {


    if ($("#datepicker").length > 0) {
        if (language == 'ru') {
            setDatePickerRussian();
        };
        if (language == 'ua') {
            setDatePickerUkraine();
        };
        if (language == 'en') {
            setDatePickerEnglish();
        };

        $("#datepicker").datepicker({
            maxDate: new Date,
            minDate: new Date(1920, 1, 1),
            startDate: new Date(1992, 1, 1),
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            yearRange: "-100:+0"
        });
    }

    //reload lighting

    if ($(".lighting").length > 0) {
        reloadLighting();
    };

    $('.log_out_menu').click(function (e) {
        /*e.preventDefault();
        exitGvent();*/
    });


    $('#file').checkFileType({
        allowedExtensions: ['jpg', 'jpeg', 'png'],
        success: function () {
            console.log("good");
        },
        error: function () {
            console.log("suck");
            $('#file').val('');
        }
    });

    $('.footer_placeholder').height($('.footer').outerHeight());

    validate('.pass-form', {
        submitFunction: reset_pass,
        errorFunction: showErrorField,
        highlightFunction: showErrorFieldPassFormAgain,
        unhighlightFunction: showHideErrorFieldAgain
    });

    validate('.forget-pass-form', {
        submitFunction: logIn,
        errorFunction: showErrorField,
        highlightFunction: showHideErrorFieldAgain,
        unhighlightFunction: showHideErrorFieldAgain
    });

    validate('.support-form', { submitFunction: supportForm,
        errorFunction: showErrorField,
        highlightFunction: showHideErrorFieldAgain,
        unhighlightFunction: showHideErrorFieldAgain
    });

    validate('.register-form', { submitFunction: registerForm,
        errorFunction: showErrorField,
        highlightFunction: showHideErrorFieldAgain,
        unhighlightFunction: showHideErrorFieldAgain
    });

    validate('.settings_form', {
        submitFunction: settingsForm,
        errorFunction: showErrorField,
        highlightFunction: showHideErrorFieldAgain,
        unhighlightFunction: showHideErrorFieldAgain
    });

    buyGoldAjax();

    buyingSilver($('.buy-silver-form input'));
    inputNumber('.num-only-silver',{blurFunc:buyingSilver});

    buyGoldInput($('.buy-gold-page-item-counter input'));
    inputNumber('.buy-gold-page-item-counter',{keyupFunc:buyGoldInput});

    marketScripts();

    userRace();

    sortableScripts();

});

$(window).load(function(){



});


/* from server */

