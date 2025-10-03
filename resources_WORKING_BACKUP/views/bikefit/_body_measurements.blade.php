<div class="flex flex-col items-center w-full mb-4">
    <div style="position:relative;max-width:440px;width:100%;margin-left:-180px;transform:scale(1.1);transform-origin:top center;">
        <img src="{{ asset('images/body-blank.png') }}" alt="Lichaamsmaten" style="width:100%;height:auto;">
        <!-- Cijfertjes op aangepaste posities -->
        <span style="position:absolute;left:15%;top:46%;font-size:1.4em;color:#222;transform:translate(-50%,-50%);">{{ $bikefit->lengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:67%;top:13%;font-size:1.1em;color:#222;transform:translate(-50%,-50%);">{{ $bikefit->schouderbreedte_cm ?? '-' }}</span>
        <span style="position:absolute;left:85%;top:29%;font-size:1.4em;color:#222;transform:translate(-50%,-50%);">{{ $bikefit->romplengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:86%;top:53%;font-size:1em;color:#222;transform:translate(-50%,-50%);">{{ $bikefit->armlengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:77%;top:65%;font-size:1.1em;color:#222;transform:translate(-50%,-50%);">{{ $bikefit->binnenbeenlengte_cm ?? '-' }}</span>
    </div>
</div>

