<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

    <p>You are not authorized</p>
<br>
    <button class="btn" onclick="javascript:history.back()">Back</button>


</div>
