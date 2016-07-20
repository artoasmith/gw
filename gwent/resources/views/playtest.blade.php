
    <div class="main">
        <button onclick="send()">
            GO CLICK!
        </button>
        <script>
            var conn = new WebSocket('ws://gwent.lar:8080');
            conn.onopen = function (e) {
                console.log('connected');
            };
            
            conn.onmessage = function (e) {
                console.log(JSON.parse(e.data));
            };

            function send() {
                console.log('send');
                conn.send('{"message": "'+Math.random()+'"}');
            }
        </script>
    </div>
