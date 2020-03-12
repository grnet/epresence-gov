<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li><a href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
<li><a href="{{ backpack_url('language_files') }}"><i class="fa fa-files-o"></i> <span>Manage Language files</span></a></li>
<li class="treeview">
    <a href="#"><i class="fa fa-file"></i> <span>Υποστήριξη</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li><a href="{{ backpack_url('downloads') }}"><i class="fa fa-download"></i> <span>Downloads</span></a></li>
        <li><a href="{{ backpack_url('videos') }}"><i class="fa fa-video-camera"></i> <span>Videos</span></a></li>
        <li><a href="{{ backpack_url('documents') }}"><i class="fa fa-file"></i> <span>Documents</span></a></li>
        <li><a href="{{ backpack_url('faq') }}"><i class="fa fa-question"></i> <span>Faq</span></a></li>
    </ul>
</li>
<li><a href="{{ backpack_url('emails') }}"><i class="fa fa-envelope"></i> <span>Manage Emails</span></a></li>
<li><a href="{{ backpack_url('notifications') }}"><i class="fa fa-bell"></i> <span>Manage Notifications</span></a></li>