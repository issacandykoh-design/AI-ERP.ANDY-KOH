@component('mail::message')
# @lang('email.hello')@if(!empty($notifiableName)){{ ' '.$notifiableName }}@endif!

@if (!empty($content))
@php($safe = strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $content)))
<p style="white-space: pre-line;">{{ $safe }}</p>
@endif

@if (!empty($url))
[{{ $actionText }}]({{ $url }})
@endif

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
