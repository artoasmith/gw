@extends('layouts.default')
@section('content')

    <?php
    $user = Auth::user();

    $errors = $errors->all();
    $data = 'Покупка золота';
    ?>

    @if($user)

        @include('layouts.top')

        <div class="main">
            <div class="mbox">
                <div class="content-top-wrap">
                    <div class="dragon-image cfix">
                        <div class="dragon-middle-wrap">
                            <div class="dragon-middle">
                                <img src="images/dragon_glaz.png" alt=""  class="glaz" />
                                <img src="images/header_dragon_gold.png" alt="" />
                            </div>
                        </div>
                    </div>
                    <div class="tabulate-image"></div>
                </div>

                @include('layouts.sidebar')

                <form id="pay" name="pay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp" accept-charset="UTF-8">
                    <p>пример платежа через сервис Web Merchant Interface</p> <p>заплатить 1 WMZ...</p>
                    <p>
                        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="1.0">
                        <input type="hidden" name="LMI_PAYMENT_DESC" value="{{ $data }}">
                        <input type="hidden" name="LMI_PAYMENT_NO" value="1">
                        <input type="hidden" name="LMI_PAYEE_PURSE" value="Z145179295679">
                        <input type="hidden" name="LMI_SIM_MODE" value="0">
                        <input type="hidden" name="id" value="<?php $user['id'] ?>">
                    </p>

                    <?php
                        /*$key = $('WM_SHOP_PURSE_'.$pay['unit']).$pay['price'].$pay['id'].$_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].$_POST['LMI_SYS_TRNAS_DATE'].
                        $LMI_SECRET_KEY.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM'];*/
                    ?>
                    <p>
                        <input type="submit" value="submit">
                    </p>
                </form>

            </div>
        </div>
    @endif

@stop