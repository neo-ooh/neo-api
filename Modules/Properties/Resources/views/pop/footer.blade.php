<footer class="pop-footer">
    <div class="pop-footer__attribution">
        <img class="pop-footer__attribution__logo"
             alt="BroadSign Logo"
             width="250"
             src="{{ resource_path('/images/broadsign.png') }}">
        <br/>
        {{ Lang::get("pop.pop-from-broadsign") }}
    </div>
    <div class="pop-footer__folio">
        <span class="pop-footer__folio__current">{PAGENO}</span>
        <span class="pop-footer__folio__group">/ {nbpg}</span>
    </div>
</footer>
