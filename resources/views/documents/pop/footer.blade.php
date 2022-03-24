<footer>
    <table class="footer__table">
        <tr>
            <td class="footer__caption">
                <img class="footer__broadsign-logo"
                     alt="BroadSign Logo"
                     width="250"
                     src="{{ resource_path('/images/broadsign.png') }}">
                <br/>
                {{ Lang::get("pop.pop-from-broadsign") }}
            </td>
            <td class="footer__folio">
                <span class="footer__folio__current">{PAGENO}</span>
                <span class="footer__folio__group">/{nbpg}</span>
            </td>
        </tr>
    </table>
</footer>
