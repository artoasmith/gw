@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>

@if($user)

    @include('layouts.top')


    <div class="main">
        <div class="mbox">
            <div class="content-top-wrap disable-select">
                <div class="dragon-image cfix">

                        <div class="dragon-middle">
                            <img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
                            <img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
                        </div>

                </div>
                <div class="tabulate-image"></div>
            </div>

            @include('layouts.sidebar')

            <div class="content-wrap">
                <div class="content-card-wrap-main">
                    <div class="content-card-top cfix">
                        <div class="content-card-left">
                            <div class="content-card-description">
                                <div class="content-card-description-wrap">
                                    Карты в колоде
                                </div>
                            </div>
                        </div>
                        <div class="content-card-right">
                            <div class="content-card-description">
                                <div class="content-card-description-wrap">
                                    Доступные карты
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-card-field-wrap cfix">
                        <div class="content-card-field-top cfix">
                            <div class="content-card-field-top-left"></div>
                            <div class="content-card-field-top-center"></div>
                            <div class="content-card-field-top-right"></div>
                        </div>
                        <div class="content-card-field cfix">
                            <div class="content-card-left">
                                <div class="content-card-cards scroll-pane">
                                    <div class="content-card-cards-wrap cfix">
                                        <ul id="sortableOne" class="connected-sortable">

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="content-card-field-center">
                                {{ Form::open(['class' => 'content-card-form', 'method' => 'POST']) }}
                                    <div class="content-card-field-center-wrap">
                                        <div class="content-card-center-title">
                                            <div class="content-card-select">
                                                <div class="content-card-select-wrap">
                                                    <select>
                                                    <?php
                                                    foreach($races as $race){
                                                        if($user['last_user_deck'] == $race['slug']){
                                                            $selected = 'selected="selected"';
                                                        }else{
                                                            $selected = '';
                                                        }
                                                    ?>
                                                        <option value="{{ $race['slug'] }}" {{ $selected }}>{{ $race['title'] }}</option>
                                                    <?php
                                                    }
                                                    ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content-card-center-block">
                                            <div class="content-card-center-img-wrap">
                                                @if($user['img_url'] != '')
                                                <img src="{{ URL::asset('img/user_images/'.$user['img_url']) }}" alt="" />
                                                @endif
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Всего карт в колоде</div>
                                                <div class="content-card-center-description-value deck-card-sum"></div>
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Карты воинов</div>
                                                <div class="content-card-center-description-value deck-warriors">
                                                    <span class="current-value">0</span> / <span class="min-value">min (<?= $deck['minWarriorQuantity'] ?>)</span>
                                                </div>
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Специальные</div>
                                                <div class="content-card-center-description-value deck-special">
                                                    <span class="current-value">0</span> / <span class="min-value"><?= $deck['specialQuantity'] ?></span>
                                                </div>
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Сила колоды</div>
                                                <div class="content-card-center-description-value deck-cards-power">0</div>
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Лига</div>
                                                <div class="content-card-center-description-value deck-league">0</div>
                                            </div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-description-key">Карты лидеров</div>
                                                <div class="content-card-center-description-value deck-liders">
                                                    <span class="current-value">0</span> / <span class="min-value"><?= $deck['leaderQuantity'] ?></span>
                                                </div>
                                            </div>
                                            <div class="content-card-center-description-key">Фильтр</div>
                                            <div class="content-card-center-description-block">
                                                <div class="content-card-center-checkbox">
                                                    <label>
                                                        <input type="checkbox" name="content-card-center-checkbox" data-card-type="special">
                                                        <span class="card-center-checkbox"></span>
                                                        <span>специальные</span>
                                                    </label>
                                                    <label>
                                                        <input type="checkbox" name="content-card-center-checkbox" data-card-type="neutral">
                                                        <span class="card-center-checkbox"></span>
                                                        <span>нейтральные</span>
                                                    </label>
                                                    <label>
                                                        <input type="checkbox" name="content-card-center-checkbox" data-card-type="fraction">
                                                        <span class="card-center-checkbox"></span>
                                                        <span>фракционные</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                 {{ Form::close() }}
                            </div>
                            <div class="content-card-right">
                                <div class="content-card-cards scroll-pane">
                                    <div class="content-card-cards-wrap cfix">
                                        <ul id="sortableTwo" class="connected-sortable">

                                        </ul>
                                        <!-- data-position=8 data-num=8 - each li has before -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-card-field-bottom cfix">
                            <div class="content-card-field-bottom-left"></div>
                            <div class="content-card-field-bottom-center"></div>
                            <div class="content-card-field-bottom-right"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endif

@stop