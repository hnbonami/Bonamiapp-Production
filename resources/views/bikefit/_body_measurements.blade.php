<div class="flex flex-col items-start w-full mb-4">
    <div style="position:relative;max-width:475px;width:100%;margin-left:0px;transform:scale(0.95);transform-origin:top left;margin-top:10px;margin-bottom:20px;">
        <img src="{{ asset('images/body-blank.png') }}" alt="Lichaamsmaten" style="width:100%;height:auto;">
        <!-- Cijfertjes op aangepaste posities met Tahoma lettertype -->
        <span style="position:absolute;left:15%;top:46%;font-size:1.2em;color:#222;transform:translate(-50%,-50%);font-family:Tahoma,Arial,sans-serif;font-weight:600;">{{ $bikefit->lengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:67%;top:13%;font-size:1.0em;color:#222;transform:translate(-50%,-50%);font-family:Tahoma,Arial,sans-serif;font-weight:600;">{{ $bikefit->schouderbreedte_cm ?? '-' }}</span>
        <span style="position:absolute;left:85%;top:29%;font-size:1.2em;color:#222;transform:translate(-50%,-50%);font-family:Tahoma,Arial,sans-serif;font-weight:600;">{{ $bikefit->romplengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:86%;top:53%;font-size:0.9em;color:#222;transform:translate(-50%,-50%);font-family:Tahoma,Arial,sans-serif;font-weight:600;">{{ $bikefit->armlengte_cm ?? '-' }}</span>
        <span style="position:absolute;left:77%;top:65%;font-size:1.0em;color:#222;transform:translate(-50%,-50%);font-family:Tahoma,Arial,sans-serif;font-weight:600;">{{ $bikefit->binnenbeenlengte_cm ?? '-' }}</span>
    </div>
</div>

