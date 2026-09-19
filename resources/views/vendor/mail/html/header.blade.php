@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/logo_clips_email.png') }}" class="logo logo-light" width="140" height="41" alt="{{ config('app.name') }}">
<!--[if !mso]><!-->
<img src="{{ asset('images/logo_clips_email_dark.png') }}" class="logo logo-dark" width="140" height="41" alt="{{ config('app.name') }}" style="display: none; max-height: 0; overflow: hidden;">
<!--<![endif]-->
</a>
</td>
</tr>
