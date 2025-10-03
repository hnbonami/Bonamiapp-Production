
@extends('layouts.mail')

@section('content')
	<div style="font-size:1.5em;font-weight:bold;margin-bottom:0.7em;">Welkom bij Bonami Sportcoaching</div>

	Beste {{ $name }},<br><br>
	Je account is aangemaakt op het Bonami Sportcoaching platform.<br><br>
	Je kunt inloggen via de volgende link:<br>
	<a href="{{ $loginUrl }}" style="display:inline-block;background:#2563eb;color:#fff;padding:0.5em 1.5em;border-radius:5px;text-decoration:none;font-size:1em;margin:1em 0;">Inloggen</a><br><br>
	<strong>Login:</strong> {{ $email }} <strong>Wachtwoord:</strong> {{ $password }}<br><br>
	Gelieve je wachtwoord te wijzigen na de eerste login.<br><br>
	Met sportieve groet,<br>
	Bonami Sportcoaching
@endsection
