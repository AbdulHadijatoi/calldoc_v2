@extends('layout.mainlayout_admin',['activePage' => 'pathologist'])

@section('title', __('Pathologist name'))

@section('content')

    <section class="section">
        @include('layout.breadcrumb',[
        'title' => __('Dashboard'),
        ])
        <div class="section_body">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-vials"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>{{__('Total Pathology') }}</h4>
                            </div>
                            <div class="card-body">
                                <h3>{{ $pathology }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-x-ray"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h6 class="text-muted">{{__('Total Radiology') }}</h6>
                            </div>
                            <div class="card-body">
                                <h3>{{ $radiology }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card">
                    <div class="card-header w-100 text-right d-flex justify-content-between">
                        <h6>{{__('Todays Report') }}</h6>
                        @include('superAdmin.auth.exportButtons')
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover text-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if (auth()->user()->hasRole('super admin'))
                                            <th>{{__('Laboratory Name')}}</th>
                                            <th>{{__('Report')}}</th>
                                        @endif
                                        <th>{{__('Prescirption')}}</th>
                                        <th>{{__('Date time')}}</th>
                                        <th>{{__('Payment Type')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('View') }}</th>
                                        @if (auth()->user()->hasRole('laboratory'))
                                            <th>{{__('Upload Report') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($test_reports as $test_report)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            @if (auth()->user()->hasRole('super admin'))
                                                <td>{{ $test_report->lab['name'] }}</td>
                                                <td>
                                                    @if ($test_report->upload_report == null)
                                                        {{__('Report Not Availabel.') }}
                                                    @else
                                                        <a href="{{ 'report_prescription/report/'.$test_report->upload_report }}" data-fancybox="gallery2">
                                                            {{__('Report') }}
                                                        </a>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="d-flex">
                                                @if ($test_report->prescription != null)
                                                    <a href="{{ 'report_prescription/upload/'.$test_report->prescription }}" data-fancybox="gallery2">
                                                        <img src="{{ 'report_prescription/upload/'.$test_report->prescription}}" alt="Feature Image" width="50px" height="50px">
                                                    </a>
                                                @else
                                                    {{__('Prescirption Not available')}}
                                                @endif
                                            </td>
                                            <td>{{ $test_report->date }}<span class="d-block text-info">{{ $test_report->time }}</span></td>
                                            <td>{{ $test_report->payment_type }}</td>
                                            <td>{{ $currency }}{{ $test_report->amount }}</td>
                                            <td>
                                                <a onclick="single_report({{ $test_report->id }})" class="text-info ml-2" href="#edit_specialities_details" data-toggle="modal">
                                                    {{__('View')}}
                                                </a>
                                            </td>
                                            @if (auth()->user()->hasRole('laboratory'))
                                                <td>
                                                    @if ($test_report->upload_report == null)
                                                        <a onclick="upload_report({{ $test_report->id }})" class="text-info ml-2" href="#upload_report" data-toggle="modal">
                                                            {{__('Upload Report')}}
                                                        </a>
                                                    @else
                                                        <a  class="text-success" href="{{ 'report_prescription/report/'.$test_report->upload_report }}" data-fancybox="gallery2">
                                                            {{__('Report') }}
                                                        </a>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card_fotter">
                        <input type="button" value="delete selected" onclick="deleteAll('doctor_all_delete')" class="btn btn-primary">
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
