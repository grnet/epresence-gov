<footer id="footer">
    <div class="container">
        <div class="box">
            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="https://grnet.gr/" target="_blank"><img src="/images/gov-logo.png"
                                                                     style="margin-left:10px; margin-right:10px; margin-bottom:10px; width:200px;"
                                                                     alt="grnet" title="grnet"></a>
                </div>
                <div class="col-md-12 text-center" style="overflow: auto;">
                    @auth
                        <a class="bottomLinks" href="/access" title="">{{trans('site.access')}}</a>&nbsp;&nbsp;|&nbsp;
                        &nbsp;
                        <a class="bottomLinks" href="/support" title="">{{trans('site.support')}}</a>&nbsp;&nbsp;|&nbsp;
                        &nbsp;
                        <a class="bottomLinks" href="/contact" title="">{{trans('site.contact')}}</a>&nbsp;&nbsp;|&nbsp;
                        &nbsp;
                        <a class="bottomLinks" href="/calendar" title="">{{trans('site.calendar')}}</a>&nbsp;&nbsp;|
                        &nbsp;&nbsp;
                    @endauth
                    <a href="/terms"><span class="bottomLinks">{{trans('site.termsSite')}}</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="/privacy_policy"><span class="bottomLinks">{{trans('site.privacy_policy')}}</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="/cookies"><span class="bottomLinks">{{trans('cookies.cookies_policy')}}</span></a>
                </div>
            </div>
        </div>
    </div>
</footer><!--/#footer-->