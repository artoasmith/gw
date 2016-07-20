
    <div class="main">
        <button onclick="send()">
            GO CLICK!
        </button>
        <script>
            var conn = new WebSocket('ws://g.loc:8080');
            conn.onopen = function (e) {
                console.log('connected');
            };
            
            conn.onmessage = function (e) {
                console.log('message ' + e.data);
            };

            function send() {
                console.log('send');
                conn.send('send random ' + Math.random());
            }
        </script>
    </div>
