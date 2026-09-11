<x-mail::message>
# New Comment!

**{{ $comment->user->name }}** commented on your post:

> {{ $comment->content }}

<x-mail::button :url="url('/posts/' . $comment->post_id)">
View Comment
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>