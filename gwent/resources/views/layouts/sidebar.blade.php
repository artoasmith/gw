<div class="left-menu-wrap disable-select">
    <div class="left-menu-wrapping">
        <div class="left-menu">
            <div class="left-menu-naviagation">
                <div class="left-menu-naviagation-wrap">

                    <div class="nav-item">
                        <a href="#" id="start-game" class="start-search-game">
                            <span class="nav-item-wrap">
                                <span>Играть</span>
                            </span>
                        </a>
                        <!--http://gw2.loc/img/card_images/577d0a1ab8741_ricari.png
                        <div class="nav-item-two">
                            <div class="nav-item-two_wrap">
                                <div class="nav-item">
                                    <a href="#">
                                        <span class="nav-item-wrap">
                                            <span>Дуэль</span>
                                        </span>
                                    </a>
                                    <div class="nav-item-three">
                                        <div class="nav-item-three_wrap">
                                            <div class="nav-item">
                                                <a href="./deck.html">
                                                    <span class="nav-item-wrap">
                                                        <span>Выбор калоды</span>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="nav-item">
                                    <a href="#">
                                        <span class="nav-item-wrap">
                                            <span>Играть с друзьями</span>
                                        </span>
                                    </a>
                                </div>
                                <div class="nav-item disabled">
                                    <a href="#">
                                        <span class="nav-item-wrap">
                                            <span>Командная игра</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
                    <div class="nav-item">
                        <a href="#">
                            <span class="nav-item-wrap">
                                <span>Рейтинг игроков</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('user-deck') }}">
                            <span class="nav-item-wrap">
                                <span>Мои карты</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="./market">
                            <span class="nav-item-wrap">
                                <span>Магазин</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('user-market-effects') }}">
                            <span class="nav-item-wrap">
                                <span>Волшебство</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('user-settings') }}">
                            <span class="nav-item-wrap">
                                <span>Настройки</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/for_tester">
                            <span class="nav-item-wrap">
                                <span>Информация Тестерам</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/training">
                            <span class="nav-item-wrap">
                                <span>Обучение</span>
                            </span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a data-href="{{ route('user-logout') }}" class="log_out_menu">
                            <span class="nav-item-wrap">
                                <span>Выход</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="left-menu-bottom">
                <div class="left-menu-bottom-wrap">
                    <div class="left-menu-img">
                        <img src="{{ URL::asset('images/left-menu-img.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>