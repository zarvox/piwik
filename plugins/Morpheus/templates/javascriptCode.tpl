<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
{$options}  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    {$setTrackerUrl}
    {# SANDSTORM: use protocol-relative link for setTrackerUrl - $API_HOST is only the host and port #}
    {$optionsBeforeTrackerUrl}_paq.push(['setTrackerUrl', '//$API_HOST']);
    _paq.push(['setSiteId', {$idSite}]);
    _paq.push(['setApiToken', '$API_TOKEN']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src='{$publicHost}/embed.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="{$protocol}{$piwikUrl}/piwik.php?idsite={$idSite}" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
