@extends('layout.mainlayout',['activePage' => 'pharmacy'])

@section('css')
<style>
    .mainDiv .hoverDoc {
            display: none;
        }

        .mainDiv:hover .mainDiv1 {
            display: none;
        }

        .mainDiv:hover .hoverDoc,
        .mainDiv1 {
            display: block;
        }

/* body {
    background: linear-gradient(44deg, #e91e63, #fffde7);
} */

.player {
    position: relative;
    width: 350px;
    background: #f1f3f4;
    box-shadow: 0 50px 80px rgba(0, 0, 0, 0.25);
}

.player .imgbx {
    position: relative;
    width: 100%;
    height: 350px;
}

.player .imgbx img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.player audio {
    width: 100%;
    outline: none;
}

</style>
@endsection

@section('content')

<div class="pt-14 border-b border-gray-light mb-10 pb-10">
    <h1 class="font-fira-sans font-semibold text-5xl text-center leading-10">{{__('Voice Message')}}</h1>
    <div class="p-5">
        <p class="font-fira-sans font-normal text-lg text-center leading-5 text-gray">{{__('Your doctor has left a voice note for you. Please check it out below')}}</p>
    </div>
</div>

<div class="pb-10" style="display: flex; justify-content: center;">
    <div class="player">
        <div class="imgbx">
            <img id="audioImage" src="{{ asset('prescription/calldoclogo.jpg') }}">
        </div>
        <audio id="audioPlayer" controls>
            <source src="{{ asset($prescription->recording) }}" type="audio/mp3">
        </audio>
    </div>
</div>
@endsection

@section('js')
    <script>
        document.getElementById('audioImage').addEventListener('click', function() {
            // Get the audio player element
            var audioPlayer = document.getElementById('audioPlayer');
            
            // Play the audio
            audioPlayer.play();
        });
    </script>
@endsection
