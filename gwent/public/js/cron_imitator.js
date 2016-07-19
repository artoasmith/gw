$(window).load(function(){
    setInterval(function(){
        $.ajax({
            url:    '/cron_imitator',
            type:   'GET',
            success:function(data){
                console.log(data);
            }
        })
    }, 180000);
});