<!-- Body measurements sectie toegevoegd aan het einde van de results view -->
@if(isset($body_measurements))
    <div class="mt-4" style="clear: both; padding-top: 20px;">
        <hr>
        {!! $body_measurements !!}
    </div>
@endif