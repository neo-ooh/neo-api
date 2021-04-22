<footer>
    <table class="footer">
        <tr>
            <td class="footer-logo">
                <img class="footer-company-branding-logo"
                     src="{{ resource_path('/logos/shopping.dark@2x.png')  }}" alt="Neo-Shopping logo"/>
            </td>
            <td class="footer-logo">
                <img class="footer-company-branding-logo"
                     src="{{ resource_path('/logos/onthego.dark@2x.png')  }}" alt="Neo-On the Go logo"/>
            </td>
            <td class="footer-logo">
                <img class="footer-company-branding-logo"
                     src="{{ resource_path('/logos/fitness.dark@2x.png')  }}" alt="Neo-Fitness logo"/>
            </td>
            <td class="footer-folio" style="width: {{ $width - 80 }}mm">
                <span class="footer-folio-current">{PAGENO}</span>
                <span class="footer-folio-group">/{nbpg}</span>
            </td>
        </tr>
    </table>
</footer>
