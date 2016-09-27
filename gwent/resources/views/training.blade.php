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
                <div class="content-top-wrap">
                    <div class="dragon-image cfix">

                            <div class="dragon-middle">
                                <img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
                                <img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
                            </div>

                    </div>
                    <div class="tabulate-image"></div>
                </div>

                @include('layouts.sidebar')

                <div class="content-wrap training-page">

                    <div class="training-title">
                        <h3>Обучение</h3>
                    </div>
                    <div class="ctext">
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Suscipit, placeat, debitis. Nobis, deleniti, ratione. Eaque fugiat excepturi provident expedita consequatur natus, voluptate ab, mollitia quae placeat ex repellat corporis pariatur inventore ipsa laborum quasi hic accusantium omnis vitae amet iure dolore itaque quidem at. Inventore tenetur voluptates amet consectetur, culpa.</p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil id illo cum animi nobis porro harum, quod voluptates libero provident unde, quaerat voluptate architecto alias sed ullam amet! Minus optio maiores aliquam delectus architecto atque doloribus facilis, placeat rem ducimus voluptate dolore molestiae eos consequuntur perspiciatis ab temporibus voluptates totam! Odio esse deserunt doloribus sit odit pariatur molestias quis, voluptas dolorem totam ipsam ratione cumque itaque sapiente quaerat debitis illum adipisci repudiandae, minus quo atque maxime maiores! Doloremque et cupiditate qui aliquam repudiandae nostrum cum non perferendis esse! Ullam earum distinctio voluptatem alias id rerum temporibus voluptate ea, molestias aspernatur, deleniti dolorum eius quos! Unde consequuntur dolor quibusdam laboriosam, explicabo delectus doloribus molestias, corporis dolorem quam iusto maxime vero hic nobis sit eligendi similique voluptatum nam eaque reprehenderit asperiores cupiditate quos fugiat. Maxime est itaque debitis, cupiditate asperiores maiores voluptate quos porro, tempora commodi quod ullam omnis at. Blanditiis ipsam tempore non, vel? Recusandae eveniet debitis architecto veniam, totam, itaque cum rerum non qui deserunt officia illo voluptate magni minus ipsum dolorem nesciunt iusto saepe vitae animi. Magnam deserunt sit eos porro exercitationem dolor laudantium, aliquam maiores, saepe nesciunt ipsam veniam molestias assumenda! Harum sequi debitis culpa enim ab fugit a, consequuntur! Optio temporibus, pariatur ad culpa quisquam vero recusandae id dignissimos, sit, voluptate, ab. Nam explicabo necessitatibus, enim a ab magnam, tempore accusantium iure fuga obcaecati, corporis facere labore. Voluptatibus, quisquam. Iusto aliquam id nihil fuga debitis, dolor sit voluptatibus, repellendus reiciendis aut quisquam iste impedit mollitia eos necessitatibus. Dignissimos tenetur non architecto impedit at ullam, fuga veniam, nisi similique mollitia illum ipsam quo exercitationem sit, quite suscipit assumenda minus quidem ducimus soluta dolores nesciunt aspernatur eum, facilis corporis beatae voluptatem ex nihil hic! Praesentium aliquid accusantium at, laborum reiciendis facilis, porro architecto rem sit aspernatur quas omnis dicta doloribus eligendi. Earum eveniet, deleniti aut et! Possimus minima, molestiae fugiat adipisci totam ut unde reiciendis, laborum optio.</p>
                        <ol>
                            <li><b>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quia eaque corporis harum aspernatur temporibus veniam amet neque, magnam! Accusantium, nisi.</b></li>
                            <li>Laboriosam sed recusandae, ab quis impedit vel perspiciatis at deleniti eius mollitia assumenda nihil corporis earum asperiores cum ratione quo.</li>
                            <li>Suscipit iure ullam at porro exercitationem magni debitis nam officiis explicabo maxime. Quaerat, accusantium. Reiciendis unde cum soluta similique aliquid.</li>
                            <li>Placeat doloremque sapiente maxime consequuntur velit nobis dolores asperiores repudiandae, libero perspiciatis nemo! Labore mollitia architecto, autem cumque inventore, nostrum!</li>
                            <li>Ipsum expedita provident numquam dolor mollitia, amet, enim id accusantium possimus ipsa nulla cupiditate omnis quasi corrupti ducimus maiores quod!</li>
                            <li>Eius praesentium neque perspiciatis voluptates. Nisi, nam illum, officiis, magni repudiandae placeat quidem alias dignissimos aliquam sunt unde necessitatibus mollitia!</li>
                        </ol>
                        <i>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Provident nobis consectetur illum doloribus enim reiciendis voluptatibus laborum dignissimos veniam nostrum quaerat asperiores cupiditate beatae eum iste veritatis, distinctio, ratione deserunt voluptate recusandae laudantium soluta. Obcaecati in, hic eos odio consectetur accusamus facere odit, incidunt aperiam dicta reprehenderit excepturi earum nobis maiores similique cumque magnam et exercitationem aut quasi vel! Quas voluptas eius ab architecto, molestiae facere soluta asperiores. Eum repudiandae itaque libero sunt, recusandae nihil. Quaerat qui nobis voluptate, molestiae tempore vel? Vero dicta quos non officia sapiente aut corrupti illo porro, dolore necessitatibus officiis neque, autem, doloribus accusantium eos!</i>
                    </div>
                </div>
            </div>
        </div>

    @endif

@stop