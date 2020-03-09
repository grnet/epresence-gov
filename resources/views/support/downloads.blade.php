@extends('app')

@section('header-javascript')
    <link rel="stylesheet" href="/css/font-awesome.css">
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->

    <script src="/js/jquery-2.1.4.js"></script>
    <script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <!-- <script src="js/main.js"></script> -->


    <script src="/js/carousel.js"></script>
    <link rel="stylesheet" href="/css/carousel.css">

    <script type="text/javascript" src="/flowplayer-5.4.6/flowplayer.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/flowplayer-5.4.6/skin/minimalist.css">

    <script type="text/javascript">
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();

            @if ($errors->any() )

            @if($errors->has('edit_title_en') || $errors->has('edit_title_el') || $errors->has('edit_description_el') || $errors->has('edit_description_en') || $errors->has('edit_file') || $errors->has('edit_file_upload'))
            $("#editDownload").modal("show");
            @else
            $("#addNewDownload").modal("show");
            @endif

            @endif

            @if(Auth::check() && Auth::user()->hasRole('SuperAdmin'))

            $(".delete_downloads_buttons").on("click", function () {

                var confirm_response = confirm("Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το αρχείο;");

                if (confirm_response) {

                    var id = $(this).attr('id').split('_')[1];

                    $.post("/support/downloads/delete", {
                        _token: '{{csrf_token()}}',
                        download_id: id,
                    })
                        .done(function () {
                            location.reload();
                        });

                } else {
                    console.log('canceled');
                }
            });


            $("#editDownload").on("hidden.bs.modal", function () {

                $("#download_id_hidden_field").val('');
                $("#edit_title_el").val('');
                $("#edit_title_en").val('');
                $("#edit_description_el").val('');
                $("#edit_description_en").val('');
            });


            $(".edit_downloads_buttons").on("click", function () {

                var id = $(this).attr('id').split('_')[1];


                $.post("/support/downloads/get_download_details_ajax", {
                    _token: '{{csrf_token()}}',
                    download_id: id,
                })
                    .done(function (response) {
                        if (response.status === "success") {
                            console.log(response.data);


                            $("#download_id_hidden_field").val(response.data.id);
                            $("#edit_title_el").val(response.data.title_el);
                            $("#edit_title_en").val(response.data.title_en);
                            $("#edit_description_el").val(response.data.description_el);
                            $("#edit_description_en").val(response.data.description_en);
                            $("#edit_file").html(response.data.file_path);


                            $("#editDownload").modal("show");
                        }
                    });

            });
            @endif
        });
    </script>
@endsection
@section('extra-css')
    <style>
        #main-slider {
            background-image: url(/images/slider-ypostiriksi.jpg);
        }

        .img-center {
            display: block;
            margin: 0 auto;
        }

        .font-counter {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
        }

        .counter-small {
            font-size: 18px;
            color: #fff;
            padding-top: 5px;
        }

        .guide {
            min-height: 250px;
        }
    </style>
@endsection
@section('support-active')
    class="active"
@endsection
@section('content')

    <!--/#main-slider-->
    <section id="main-slider" class="carousel">
        <div class="carousel-inner">
            <div class="item active">
                <div class="container">
                    <div class="carousel-content">
                        <h1>&nbsp;</h1>

                        <p class="lead carousel-shadow">&nbsp;</p>
                    </div>
                </div>
            </div>
            <!--/.item-->
        </div>
        <!--/.carousel-inner-->
        <!--      <a class="prev" href="#main-slider" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
              <a class="next" href="#main-slider" data-slide="next"><i class="fa fa-chevron-right"></i></a> -->
    </section><!--/#main-slider-->

    <section>
        <div class="container">
            <div class="box" style="padding: 30px 50px">
                <ul class="nav nav-tabs">
                    <li><a href="/support/faq">Faq</a></li>
                    @if($total_documents > 0)
                        <li><a href="/support/documents">{!! trans('site.support_manuals') !!}</a></li>
                    @endif
                    @if($total_videos>0)
                        <li><a href="/support/videos">{!! trans('site.support_videos') !!}</a></li>
                    @endif
                    <li><a href="/support/teamviewer">Teamviewer</a></li>
                    <li class="active"><a href="#">Downloads</a></li>
                </ul>
                <div class="medium-gap"></div>
                <div class="medium-gap"></div>
                <div class="tab-content">
                    <div class="row tab-pane active" id="downloads">
                        @if(Auth::check() && Auth::user()->hasRole('SuperAdmin'))
                            <div class="col-12" style="margin-bottom:5px; overflow: auto;">
                                <button type="button" class="btn btn-primary" style="float:right;" data-toggle="modal"
                                        data-target="#addNewDownload">
                                    <i class="fa fa-plus"></i> Προσθήκη
                                </button>
                            </div>
                        @endif
                        @if (session('message'))
                            <p class="alert alert-info" style="margin: 0px 15px 10px 15px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {!! session('message') !!}
                            </p>
                        @endif
                        <div class="row" style="padding-left:30px; padding-right:30px;">
                            <div class="col-12">
                                <p class="alert alert-info"> {!! trans('support.downloads_intro') !!}</p>
                            </div>
                            @foreach($downloads as $download)
                                <div class="col-12">
                                    @if(App::getLocale() == 'en')
                                        <h3> {{$download->title_en}}</h3>
                                        <p> {{$download->description_en}}</p>
                                    @else
                                        <h3> {{$download->title_el}}</h3>
                                        <p> {{$download->description_el}}</p>
                                    @endif
                                    <div class="btn-group">
                                        <a href="{{$download->file_path}}" download target="_blank">
                                            <button class="btn btn-primary"><i
                                                        class="fa fa-download"></i> {{trans('support.download')}}
                                            </button>
                                        </a>
                                        @if(Auth::check() && Auth::user()->hasRole('SuperAdmin'))
                                            <button class="btn btn-danger delete_downloads_buttons"
                                                    id="delete-download_{{$download->id}}"><i
                                                        class="fa fa-trash"></i> {{trans('conferences.delete')}}
                                            </button>
                                            <button class="btn btn-default edit_downloads_buttons"
                                                    id="edit-download_{{$download->id}}"><i
                                                        class="fa fa-edit"></i> {{trans('conferences.edit')}}</button>
                                        @endif
                                    </div>
                                    <hr/>
                                </div>
                            @endforeach
                        </div>
                        <!--/.col-md-4-->
                    </div>
                </div>
                <!--/.box-->
            </div>
            <!--/.container-->
        </div>
        <!-- Modal -->
        <div class="modal fade" id="addNewDownload" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add new download</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {!! Form::open(array('url' => ['/support/downloads'], 'method' => 'POST', 'class' => 'form-horizontal','role' => 'form', 'files' => true)) !!}
                    <div class="modal-body">
                        @if($errors->any() && (!$errors->has('edit_title_en') && !$errors->has('edit_title_el') && !$errors->has('edit_description_el') && !$errors->has('edit_description_en') && !$errors->has('edit_file') && !$errors->has('edit_file_upload')))
                            <div class="col-12 alert-danger" style="margin-left:10px; margin-right:10px;">
                                <ul class="alert alert-danger">
                                    <strong>{{trans('users.changesNotSaved')}}</strong>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            {!! Form::label('TitleEl','Τίτλος Ελληνικά:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('title_el', old('title_el'), ['class' => 'form-control','id' => 'TitleEl', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('TitleEn','Title English:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('title_en', old('title_en'), ['class' => 'form-control','id' => 'TitleEn', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('DescriptionEl','Περιγραφή Ελληνικά:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('description_el', old('description_el'), ['class' => 'form-control','id' => 'DescriptionEl', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('DescriptionEn','Description English:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('description_en', old('description_en'), ['class' => 'form-control','id' => 'DescriptionEn', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('File','Επιλογή Αρχείου:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::file('file', null, ['class' => 'form-control','id' => 'File', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <div class="modal fade" id="editDownload" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Επεξεργασία</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {!! Form::open(array('url' => ['/support/downloads/update'], 'method' => 'PATCH', 'class' => 'form-horizontal','role' => 'form', 'files' => true)) !!}
                    <div class="modal-body">
                        @if ($errors->any() && ($errors->has('edit_title_en') || $errors->has('edit_title_el') || $errors->has('edit_description_el') || $errors->has('edit_description_en') || $errors->has('edit_file') || $errors->has('edit_file_upload')))
                            <div class="col-12 alert-danger" style="margin-left:10px; margin-right:10px;">
                                <ul class="alert alert-danger">
                                    <strong>{{trans('users.changesNotSaved')}}</strong>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {!! Form::hidden('download_id', null, ['id' => 'download_id_hidden_field']) !!}
                        <div class="form-group">
                            {!! Form::label('TitleEl','Τίτλος Ελληνικά:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('edit_title_el', old('title_el'), ['class' => 'form-control','id' => 'edit_title_el', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('TitleEn','Title English:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('edit_title_en', old('title_en'), ['class' => 'form-control','id' => 'edit_title_en', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('DescriptionEl','Περιγραφή Ελληνικά:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('edit_description_el', old('description_el'), ['class' => 'form-control','id' => 'edit_description_el', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('DescriptionEn','Description English:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('edit_description_en', old('description_en'), ['class' => 'form-control','id' => 'edit_description_en', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('File','Αρχείο:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                <span id="edit_file"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('File','Επιλογή νέου αρχείου:', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::file('edit_file', null, ['class' => 'form-control','id' => 'File', 'placeholder' => trans('deptinst.required')]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Ακύρωση</button>
                        <button type="submit" class="btn btn-primary">Αποθήκευση</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
