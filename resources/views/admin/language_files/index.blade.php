@extends('backpack::layout')

@section('header')
    <script src="/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '.input_text_area',  // change this value according to your HTML
            auto_focus: 'inputTextArea',
            plugins: "code",
            entity_encoding: "raw",
            height: "240",
            forced_root_block : ""
        });
    </script>
    <style>
        .highlighted {
            color: red;
        }
        .translation_heading_row {
            border-bottom: 1px solid #a0bcd0;
            padding: 5px;
        }

        .translation_inner_heading_row {
            border-bottom: 1px solid #d3e0e9;
            overflow: auto;
            padding: 15px;
        }

    </style>
    <section class="content-header">
        <h1>
            Language File translation interface
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ backpack_url() }}">{{ config('backpack.base.project_name') }}</a></li>
            <li class="active">{{ trans('backpack::base.dashboard') }}</li>
        </ol>
    </section>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Export database rows to language files</div>
                @if(session()->has('status') && session()->get('status') == "success" && session()->get("form") == "export_form")
                    <div class="alert-success">
                        <p>{{session()->get('message')}}</p>
                    </div>
                @endif
                @if(!$exported)
                    <div class="alert-danger">
                        <p>There are updated language lines that haven't been exported yet!</p>
                    </div>
                @endif
                @if($locked)
                    <div class="alert-danger">
                        <p>Language Files are locked!</p>
                    </div>
                @endif
                <div class="panel-body">
                    <form method="POST" action="/admin/export_language_files">
                        {{csrf_field()}}
                        <button class="btn btn-primary" @if($locked) disabled @endif type="submit">Export</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Search in files</div>
                <div class="panel-body">
                    <form method="GET" action="">
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <div class="col-md-3">
                                <label>Select language</label>
                                <select class="form-control" name="language_id">
                                        <option value="all">All</option>
                                    @foreach($languages as $language)
                                        <option value="{{$language->id}}"
                                                @if(isset($_GET['language_id']) && $_GET['language_id'] == $language->id) selected @endif >{{$language->name}} @if($language->primary)
                                                (primary) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Select group</label>
                                <select class="form-control" name="group">
                                    <option value="all">All</option>
                                    @foreach($groups as $group)
                                        <option value="{{$group->group}}"
                                                @if(isset($_GET['group']) && $_GET['group'] == $group->group) selected @endif >{{$group->group}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Dirty translation</label>
                                <select class="form-control" name="dirty">
                                    <option value="all"
                                            @if(isset($_GET['dirty']) && $_GET['dirty'] === "all") selected @endif>
                                        All
                                    </option>
                                    <option value="1"
                                            @if(isset($_GET['dirty']) && $_GET['dirty'] === "1") selected @endif>yes
                                    </option>
                                    <option value="0"
                                            @if(isset($_GET['dirty']) && $_GET['dirty'] === "0") selected @endif>no
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="term" class="form-control" placeholder="Αναζήτηση..."
                                       value="{{isset($_GET['term']) ? $_GET['term'] : null }}">
                            </div>
                            <div class="col-md-3" style="margin-top:10px;">
                                <label><input type="checkbox" name="search_in_original"
                                              @if(isset($_GET['search_in_original']) && $_GET['search_in_original'] == "on") checked @endif>
                                    Search in original</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary" type="submit" style="margin:20px;">Search</button>
                            <a href="/admin/language_files" style="float:right; margin:20px;">clear filters</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Lines</div>
                <div class="panel-body">
                    @if(session()->has("status") && session()->get("form") == "manage_lines" && session()->get("status") == "success" )
                        <div class="alert-success">
                            <p>{{session()->get('message')}}</p>
                        </div>
                    @endif
                    @if(session()->has("status") && session()->get("form") == "manage_lines" && session()->get("status") == "error" )
                        <div class="alert-danger">
                            <p>{{session()->get('message')}}</p>
                        </div>
                    @endif

                    @foreach($language_lines as $line)
                        <div class="row translation_inner_heading_row"
                             style="@if(isset($currently_editing) && $currently_editing == $line->id ) background-color:#d3e0e9; border:1px solid #3097d1; @endif">
                            <div class="translation_inner_heading_row">
                                <div class="@if(!isset($line->original_line))  col-md-8 @else col-md-6 @endif"
                                     style="border-right:1px solid #d3e0e9;">
                                        <span><strong>File:</strong> {{$line->group}}
                                            - <strong>Language:</strong> {{$line->language->name}} @if($line->language->primary)
                                                (primary) @endif</span>
                                    @if(!empty($currently_editing) && $currently_editing == $line->id)
                                        <span style="float:right;">Key: {{$line->key}}</span>
                                    @else
                                        <a style="float:right;"
                                           href="{{'language_files?'.http_build_query(['term' => isset($_GET['term']) ? $_GET['term'] : null,'language_id' => isset($_GET['language_id']) ? $_GET['language_id'] : null,'dirty' => isset($_GET['dirty']) ? $_GET['dirty'] : null,'group' => isset($_GET['group']) ? $_GET['group'] : null,'page' => isset($_GET['page']) ? $_GET['page'] : null,'id'=>$line->id])}}">Key: {{$line->key}}</a>
                                    @endif
                                </div>
                                <div class="@if(!isset($line->original_line)) col-md-4 @else col-md-6 @endif">
                                    <div class="col-md-12">
                                        @if(!isset($line->original_line) && (isset($currently_editing) && $currently_editing == $line->id))
                                            <span style="float:left;">Change note</span>
                                        @endif
                                        @if($line->dirty)
                                            <span class="highlighted" style="float:right;">Needs translation</span>
                                        @endif
                                        @if(!empty($currently_editing) && $currently_editing == $line->id)
                                            <span style="@if(!isset($line->original_line)) float:right; @else float:left; @endif margin-right:10px;">
                                            Available languages:
                                                @foreach($available_translations as $available_translation)
                                                    <a href="{{'language_files?'.http_build_query(['id' => $available_translation->id ,'same_page'=>false])}}"> @if($available_translation->dirty)
                                                            <strike>{{$available_translation->language->code}}</strike> @else {{$available_translation->language->code}} @endif</a>
                                                @endforeach
                                        </span>
                                        @endif
                                    </div>
                                    @if(isset($line->original_line) && (isset($currently_editing) && $currently_editing == $line->id))
                                        @if(isset($line->original_line->note))
                                            <div class="col-md-12" style="margin: 10px 0 10px 0 ; padding:10px;">
                                                <h4>Translation note</h4>
                                                <textarea disabled
                                                          cols="80">{{$line->original_line->note->text}}</textarea>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @if(isset($line->original_line))
                                <div class="col-md-6" style="border-right:1px solid #d3e0e9;">
                                    <div style="padding:20px; background-color: #d3e0e9; overflow: auto;">{!! $line->original_line->text !!}</div>
                                </div>
                                <div class="col-md-6">
                                    @if(!empty($currently_editing) && $currently_editing == $line->id )
                                        <form method="POST" action="/admin/update_string">
                                            {{csrf_field()}}
                                            <input type="hidden" name="id" value="{{$line->id}}">
                                            <textarea class="form-control input_text_area" rows="5"
                                                      id="inputTextArea"
                                                      name="value">{!! $line->text !!}</textarea>
                                            <button class="btn btn-primary" @if($locked) disabled @endif type="submit"
                                                    style="float:right; margin:10px;">Save
                                            </button>
                                        </form>
                                    @else
                                        <div>{!! $line->text !!}</div>
                                    @endif
                                </div>
                            @else
                                @if(!empty($currently_editing) && $currently_editing == $line->id )
                                    <form method="POST" action="/admin/update_string">
                                        <div class="col-md-8">
                                            {{csrf_field()}}
                                            <input type="hidden" name="id" value="{{$line->id}}">
                                            <textarea class="form-control input_text_area" rows="20"
                                                      id="inputTextArea"
                                                      name="value">{!! $line->text !!}</textarea>
                                        </div>
                                        <div class="col-md-2">
                                                <textarea class="form-control" rows="20" id="inputTextAreaNote"
                                                          name="note">{{ isset($line->note) ? $line->note->text : null}}</textarea>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary" @if($locked) disabled @endif type="submit"
                                                    style="float:right; margin:10px;">Save
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="col-md-12">
                                        <div>{!! $line->text !!}</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(!Request::has('same_page'))
                <div class="col-md-12">
                    {!! $language_lines->appends(Request::except(['page','id']))->links() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

