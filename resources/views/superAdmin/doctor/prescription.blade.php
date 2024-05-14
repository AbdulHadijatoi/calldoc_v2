@extends('layout.mainlayout_admin',['activePage' => 'appointment'])

@section('title',__('Prescription'))
@section('css')
<style>
    .img_preview {
        width: 100%;
        height: 100px;
        background: no-repeat;
        background-size: cover;
        border-radius: 8px;
        background-color: rgba(0,0,0,0.1)
    }

    .bx-image-add:before {
        content: "\ed7f";
    }
    .upload-label {
        right: -15px;
        top: -12px;
        font-size: 22px;
        height: 40px;
        width: 40px;
        background-color: #fff;
    }
    .icon
    {
        right: 13px;
    }
</style>
@endsection
@section('content')

<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="card-title mb-0">{{__('Add Prescription')}}</h4>
            <div class="col-md-4 col-sm-6">
                <button type="button" class="btn btn-primary btn-block py-2" style="border-radius: 5px; border-style: none; text-transform: uppercase; font-weight: bold" id="submitBtn"><i class="fa fa-save"></i> {{__('Save')}}</button>
            </div>
        </div>
        <div class="card-body">
            <label class="pb-4">
                {{__("Please record a voice note of the prescription if you wish to send it to your patient via message.")}}
            </label>
            <div id="originalFields" class="mb-4">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <p for="medicine">{{__('Medicine Name')}}</p>
                        <div class="input-group" id="medicineSelect">
                            <select id="medicine" class="form-control select2">
                                @foreach ($medicines as $medicine)
                                <option value="{{ $medicine->name ? $medicine->name.', ' : '' }} {{ $medicine->dosage1 ? $medicine->dosage1.' ' : '' }} {{ $medicine->unit_dosage1 ? $medicine->unit_dosage1.', ' : '' }} {{ $medicine->shape ? $medicine->shape.', ' : '' }} {{ $medicine->presentation }}">{{ $medicine->name?$medicine->name.', ':'' }} {{ $medicine->dosage1?$medicine->dosage1.' ':'' }} {{ $medicine->unit_dosage1?$medicine->unit_dosage1.', ' :'' }} {{ $medicine->shape?$medicine->shape.', ':'' }} {{ $medicine->presentation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group" id="medicineText" style="display: none;">    
                            <input type="text" id="medicine2" class="form-control" placeholder="Write your medicine here">
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-success" type="button" id="toggleMedicineBtn">{{ __('Want to write it yourself?') }}</button>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <p for="day">{{__('Days')}}</p>
                        <input id="day" class="form-control" min="1" value="1" type="number">
                    </div>
                    <div class="form-group col-md-3">
                        <p for="quantity">{{__('Quantity/Morning')}}</p>
                        <input id="quantity1" class="form-control" min="0" value="0" type="number">
                    </div>
                    <div class="form-group col-md-3">
                        <p for="quantity">{{__('Quantity/Afternoon')}}</p>
                        <input id="quantity2" class="form-control" min="0" value="0" type="number">
                    </div>
                    <div class="form-group col-md-3">
                        <p for="quantity">{{__('Quantity/Night')}}</p>
                        <input id="quantity3" class="form-control" min="0" value="0" type="number">
                    </div>
                    
                    <div class="form-group col-md-8">
                        <label for="remarks">{{__('Remarks')}}</label>
                        <input id="remarks" class="form-control" placeholder="Add remarks if needed" type="text">
                    </div>
                    
                    <div class="col-md-4 mt-sm-0 mt-md-4 pt-1">
                        <button type="button" class="btn btn-outline-info btn-block py-2" id="addBtn"><i class="fa fa-plus"></i> {{__('Add medicine to the list below')}}</button>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <p>{{__("Record voice note for patient (OPTIONAL)")}}</p>

                    <audio id="recorder" muted hidden></audio>

                    <input type="hidden" id="appointment_id" value="{{ $appointment->id }}">
                    <button class="btn btn-primary py-3 col-md-3" id="start">
                        <i class="fa fa-microphone"></i>
                        {{__("Start Recording")}}
                    </button>
                    <button disabled class="btn btn-info py-3 col-md-3 mt-md-0 mt-sm-1" id="stop">
                        <i class="fa fa-stop"></i>
                        {{__("Stop Recording")}}
                    </button>
                    <button disabled class="btn btn-success py-3 col-md-3 mt-md-0 mt-sm-1" id="saveRecording">
                        <i class="fa fa-save"></i>
                        {{__("Save And Send To Patient")}}
                    </button>
                    <audio id="player" controls class="d-none"></audio>
                </div>
            </div> 
            <form action="{{ url('addPrescription') }}" method="post" class="myform" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <p>{{ __('Upload Prescription Image') }} {{ __("(OPTIONAL)") }}</p>
                        <div class="col-md-12 d-flex justify-content-start p-0 m-0">
                            <label class="img_preview avta-prview-1 mt-1" style="border: 1px solid rgba(0,0,0,0.15)">
                                <div class="position-relative">
                                    <input type="file" id="prescription_image" name="prescription_image" class="d-none" accept=".png, .jpg, .jpeg">
                                    <div class="position-absolute upload-label shadow-sm rounded-circle">
                                        <label for="prescription_image" class=" position-absolute mb-0 icon"><i class="far fa-solid fa-image"></i></label>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                <input type="hidden" name="user_id" value="{{ $appointment->user_id }}">
                <input type="hidden" name="pres_id" id="pres_id">
                
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-center">
                        <thead>
                            <tr>
                                <th>{{__('Medicine Name')}}</th>
                                <th>{{__('Days')}}</th>
                                <th>{{__('Quantity/Morning')}}</th>
                                <th>{{__('Quantity/Afternoon')}}</th>
                                <th>{{__('Quantity/Night')}}</th>
                                <th>{{__('Remarks')}}</th>
                                <th>{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody class="tBody">
                            <!-- Table body will be filled dynamically -->
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

</section>


<script>

function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#img_preview1").css(
                    "background-image",
                    "url(" + e.target.result + ")"
                );
                $("#img_preview1").hide();
                $("#img_preview1").fadeIn(650);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function () {
        readURL(this);
    });

    function readURL1(input)
    {
        if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.avta-prview-1').css('background-image', 'url(' + e.target.result + ')');
                $('.avta-prview-1').hide();
                $('.avta-prview-1').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#prescription_image").change(function () {
        readURL1(this);
    });
    
    // RECORDING SCRIPT:BEGINS
    class VoiceRecorder {
        constructor() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                console.log("getUserMedia supported")
            } else {
                console.log("getUserMedia is not supported on your browser!")
            }

            this.mediaRecorder
            this.stream
            this.chunks = []
            this.isRecording = false

            this.recorderRef = document.querySelector("#recorder")
            this.playerRef = document.querySelector("#player")
            this.startRef = document.querySelector("#start")
            this.stopRef = document.querySelector("#stop")
            this.saveRef = document.querySelector("#saveRecording")
            this.appointmentIdRef = document.querySelector("#appointment_id")
            this.presIdRef = document.querySelector("#pres_id")
            
            this.startRef.onclick = this.startRecording.bind(this)
            this.stopRef.onclick = this.stopRecording.bind(this)
            this.saveRef.onclick = this.saveRecording.bind(this)

            this.constraints = {
                audio: true,
                video: false
            }
            
        }

        handleSuccess(stream) {
            this.stream = stream
            this.stream.oninactive = () => {
                console.log("Stream ended!")
            };
            this.recorderRef.srcObject = this.stream
            this.mediaRecorder = new MediaRecorder(this.stream)
            console.log(this.mediaRecorder)
            this.mediaRecorder.ondataavailable = this.onMediaRecorderDataAvailable.bind(this)
            // this.mediaRecorder.onstop = this.onMediaRecorderStop.bind(this)
            this.recorderRef.play()
            this.mediaRecorder.start()
        }

        handleError(error) {
            console.log("navigator.getUserMedia error: ", error)
        }
        
        onMediaRecorderDataAvailable(e) { this.chunks.push(e.data) }
        
        saveRecording(){
            this.saveRef.disabled = true;
            this.stopRef.disabled = true;
            this.saveRef.innerHTML = 'Loading...';
            const blob = new Blob(this.chunks, { 'type': 'audio/ogg; codecs=opus' });
            this.chunks = [];
            this.stream.getAudioTracks().forEach(track => track.stop());
            this.stream = null;

            // Create a FormData object and append the audio Blob
            const formData = new FormData();
            formData.append('recording', blob, 'recording.ogg');
            formData.append('appointment_id', this.appointmentIdRef.value);
            formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token

            // Send the audio data to the backend using fetch
            fetch('{{ url("save-recording") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    this.saveRef.innerHTML = "{{ __('Successfully sent to patient') }}";
                    return response.json();
                }
                throw new Error('Network response was not ok.');
            })
            .then(data => {
                // Handle the response from the backend if needed
                if(data.pres_id){
                    this.presIdRef.value = data.pres_id;
                }
                console.log(data);
            })
            .catch(error => {
                console.error('There was a problem with your fetch operation:', error);
            });
        }

        startRecording() {
            if (this.isRecording) return
            this.isRecording = true
            this.startRef.innerHTML = '<i style="font-size: 22px;" class="fa fa-microphone"></i>'
            this.stopRef.disabled = false;
            this.saveRef.innerHTML = '<i class="fa fa-save"></i> Save'
            this.playerRef.src = ''
            navigator.mediaDevices
                .getUserMedia(this.constraints)
                .then(this.handleSuccess.bind(this))
                .catch(this.handleError.bind(this))
        }
        
        stopRecording() {
            if (!this.isRecording) return
            this.isRecording = false
            this.startRef.innerHTML = "<i class='fa fa-microphone'></i> {{ __('Start Recording')}}"
            this.stopRef.innerHTML = "{{ __('Stop Recording') }}"
            this.saveRef.disabled = false;
            this.recorderRef.pause()
            this.mediaRecorder.stop()
        }
        
    }

    window.voiceRecorder = new VoiceRecorder()

    // RECORDING SCRIPT:ENDS


    $(document).ready(function() {
        var btnToggle = $('#toggleMedicineBtn');
        var selectMedicineLabel = "{{ __('Select Medicine') }}";
        var medicineFieldRequiredLabel = "{{ __('Medicine field is required') }}";
        var write_yourself_label = "{{ __('Want to write it yourself?') }}";

        $("#addBtn").on('click', function () {
            var medicine = $('#medicine').val();
            var medicine2 = $('#medicine2').val();
            var day = $('#day').val();
            var quantity1 = $('#quantity1').val();
            var quantity2 = $('#quantity2').val();
            var quantity3 = $('#quantity3').val();
            var remarks = $('#remarks').val();
            var medicineText = medicine;
            if(btnToggle.html() == selectMedicineLabel){
                medicineText = medicine2
            }
            if(medicineText == '' || medicineText == null){
                alert(medicineFieldRequiredLabel);
                return;
            }
            // Add read-only row to table
            $('.tBody').append(
                '<tr>' +
                '<td><input type="text" class="form-control-plaintext" name="medicine[]" value="' + medicineText + '" readonly></td>' +
                '<td><input type="text" class="form-control-plaintext" name="day[]" value="' + day + '" readonly></td>' +
                '<td><input type="text" class="form-control-plaintext" name="qty_morning[]" value="' + quantity1 + '" readonly></td>' +
                '<td><input type="text" class="form-control-plaintext" name="qty_afternoon[]" value="' + quantity2 + '" readonly></td>' +
                '<td><input type="text" class="form-control-plaintext" name="qty_night[]" value="' + quantity3 + '" readonly></td>' +
                '<td><input type="text" class="form-control-plaintext" name="remarks[]" value="' + remarks + '" readonly></td>' +
                '<td><button type="button" class="btn btn-danger deleteBtn">{{__('Remove')}}</button></td>' +
                '</tr>'
            );

            // Clear original fields
            $('#medicine2').val('');
            $('#day').val('1');
            $('#quantity1').val('0');
            $('#quantity2').val('0');
            $('#quantity3').val('0');
            $('#remarks').val('');
        });

        $(document).on('click', '.deleteBtn', function() {
            $(this).closest('tr').remove();
        });

        function isTableBodyEmpty() {
            return $('.tBody').find('tr').length === 0;
        }
        
        $("#submitBtn").on('click', function (e) {
            const fileInput = document.getElementById('prescription_image');

            function isFileSelected() {
                return fileInput.files.length > 0;
            }

            if (!isFileSelected()) {
                if (isTableBodyEmpty()) {
                    alert("{{ __('Please select a file or add medicine before submitting.') }}");
                    e.preventDefault();
                    return;
                }
            }

            $(".myform").submit();
        });



        $('#toggleMedicineBtn').on('click', function() {
            var selectOption = $('#medicineSelect').is(':visible');
            $('#medicineSelect').hide();
            if (selectOption) {
                btnToggle.html(selectMedicineLabel);
                $('#medicineSelect').hide();
                $('#medicineText').show();
            } else {
                btnToggle.html(write_yourself_label);
                $('#medicineSelect').show();
                $('#medicineText').hide();
            }
        });

        
    });
</script>

@endsection
