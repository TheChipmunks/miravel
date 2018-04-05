@if($gaid = config('google-analytics.uid'))
<script>
    window.ga=function(){ga.q.push(arguments)};
    ga.q=[];ga.l=+new Date;
    ga('create','{{ $gaid }}','auto');
    ga('send','pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async defer></script>
@endif
