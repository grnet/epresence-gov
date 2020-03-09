<div class="modal fade" id="HistModal" tabindex="-1" role="dialog" aria-labelledby="HistLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{trans('site.confHistory')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">

                <!-- DATATABLES START -->
                <div class="row"> <!-- Row with search field and add button - START -->
                    <div class="col-xs-12">
                        <span class="pull-left" style="width:110px; margin-right:10px">
                                               <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                                class="glyphicon glyphicon-align-justify"></i></span>
                                                    <select class="form-control" id="datatablesChangeDisplayLength">
                                                        <option value="10">10</option>
                                                        <option value="20">20</option>
                                                        <option value="30">30</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                        <option value="-1">All</option>
                                                    </select>
                                                </div>
                                        </span>
                        <span class="pull-left">
                                            <div class="input-group" style="width:200px">
                                                <span class="input-group-addon"><i
                                                            class="glyphicon glyphicon-search"></i></span>
                                                <input type="text" class="form-control" style="width:200px"
                                                       id="datatablesSearchTextField">
                                            </div>
                                        </span>
                        <span class="pull-left">
                                      <div class="input-group">
                                                    <select class="form-control" id="datatablesChangeDisplayConnected">
                                                        <option value="all">{{trans('conferences.showAll')}}</option>
                                                        <option value="{{trans('users.yes')}}">{{trans('conferences.connected_to_conference')}}: {{trans('users.yes')}}</option>
                                                           <option value="{{trans('users.no')}}">{{trans('conferences.connected_to_conference')}}: {{trans('users.no')}}</option>
                                                    </select>
                                                </div>
                        </span>
                    </div>
                </div>


                <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                       class="table table-hover table-striped table-bordered" id="example">
                    <thead>
                    <tr>
                        <th>{{trans('site.description')}}</th>
                        <th>{{trans('site.dateShort')}}</th>
                        <th>{{trans('site.start')}}</th>
                        <th>{{trans('site.end')}}</th>
                        <th>{{trans('conferences.connected_to_conference')}}</th>
                    </tr>

                    </thead>
                    <tbody>
                    @if(!empty($user->pastConferences()))
                        @foreach($user->pastConferences() as $conference)
                            <tr>
                                <td>{{ $conference->title }}</td>
                                <td>{{ $conference->getDate($conference->start) }}</td>
                                <td>{{ $conference->getTime($conference->start) }}</td>
                                <td>{{ $conference->getTime($conference->end) }}</td>
                                <td>{{ $conference->pivot->joined_once ? trans('users.yes') : trans('users.no') }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <!-- DATATABLES END -->
            </div>

            <div class="modal-footer" style="margin-top:0px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('site.close')}}</button>
            </div> <!-- .modal-footer -->
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div>