@extends('layout.mainlayout_admin',['activePage' => 'setting'])

@section('title',__('Create Meeting'))
@section('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
@endsection
@section('content')
<section class="section">
    @include('layout.breadcrumb',[
    'title' => __('Create Meeting'),
    ])

    @if (session('status'))
    @include('superAdmin.auth.status',[
    'status' => session('status')])
    @endif
    <div class="card">
        <form action="{{ url('store',$id) }}" method="post" enctype="multipart/form-data" class="myform">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="col-form-label">{{__('Topic')}}</label>
                            <input type="text" name="topic" class="form-control @error('topic') is-invalid @enderror"
                                value="{{ old('topic') }}">
                            @error('topic')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        {{-- <div class="form-group">
                            <label class="col-form-label">{{__('Start Time')}}</label>
                            <input type="text" name="start_time"
                                class="form-control @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time') }}" placeholder="2020-11-23T13:26:02.000Z">
                            @error('start_time')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div> --}}
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="col-form-label">{{__('Date')}}</label>
                                    <input type="text" class="datepicker form-control @error('date') is-invalid @enderror" id="date"
                                        min="{{ Carbon\Carbon::now(env('timezone'))->format('Y-m-d') }}" name="date">
                                    <span class="invalid-div text-danger"><span class="date"></span>
                                    @error('date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="col-form-label">{{__('Time')}}</label>
                                    <input type="text" name="time"
                                        class="timepicker form-control @error('time') is-invalid @enderror"
                                        value="{{ old('time') }}">
                                    @error('time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="col-form-label">{{__('Agenda')}}</label>
                            <input type="text" name="agenda" class="form-control @error('agenda') is-invalid @enderror"
                                value="{{ old('agenda') }}">
                            @error('agenda')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer">
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('js')
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>

<script>
    var dateToday = new Date();
    $( function() {
        $( ".datepicker" ).datepicker({
            minDate: dateToday,
            numberOfMonths: 1,
            dateFormat: 'yy-mm-dd'
        });
    });
$(document).ready(function(){
    $('.timepicker').timepicker({
        timeFormat: 'h:mm:ss'
    });
});
</script>
@endsection
