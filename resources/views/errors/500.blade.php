<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

    <p>Back to Sign In page</p>
    <br>
    @if(auth()->user()->panelrole_id ?? 1)
    <a href="/admin" role="button" class="btn btn-neutral">Back</a>

    @elseif(auth()->user()->panelrole_id ?? 2)
    <a href="/pengajar" role="button" class="btn btn-neutral">Back</a>
    
    @elseif(auth()->user()->panelrole_id ?? 3)
    <a href="/" role="button" class="btn btn-neutral">Back</a>
    
    @endif

</div>