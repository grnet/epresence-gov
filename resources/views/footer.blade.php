<footer id="footer">
    <div class="container">
        <div class="box">
            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="https://grnet.gr/" target="_blank"><img src="/images/grnet-logo.jpg"
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
                <div class="col-md-12 text-center" style="overflow: auto; margin-top:15px;">
                    <a href="https://www.linkedin.com/company/55267/" target="_blank"><span class="bottomLinks"><img src="/images/social_icons/linkedin.png"></span></a>
                    <a href="https://www.facebook.com/grnet.gr/" target="_blank"><span class="bottomLinks"><img src="/images/social_icons/facebook.png"></span></a>
                    <a href="https://twitter.com/grnet_gr" target="_blank"><span class="bottomLinks"><img src="/images/social_icons/twitter.png"></span></a>
                    <a href="https://www.instagram.com/grnet.gr/" target="_blank"><span class="bottomLinks"><img src="/images/social_icons/instagram.png"></span></a>
                    <a href="https://www.youtube.com/user/EDETvideos" target="_blank"><span class="bottomLinks"><img src="/images/social_icons/youtube.png"></span></a>

                </div>
            </div>
        </div>
    </div>
</footer><!--/#footer-->