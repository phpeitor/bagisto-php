<!-- SEO Meta Content -->
@push('meta')
    <meta name="title" content="{{ $page->meta_title }}" />

    <meta name="description" content="{{ $page->meta_description }}" />

    <meta name="keywords" content="{{ $page->meta_keywords }}" />
@endPush

@push('styles')
    <style>
        /* Restores default list styling stripped by the Tailwind reset for
           page.html_content, editor-generated content (CMS pages). */
        .cms-content ul {
            list-style: disc;
            margin: 1em 0;
            padding-inline-start: 1.75em;
        }

        .cms-content ol {
            list-style: decimal;
            margin: 1em 0;
            padding-inline-start: 1.75em;
        }

        .cms-content li {
            margin: .35em 0;
        }

        .cms-content li > ul,
        .cms-content li > ol {
            margin: .35em 0;
        }

        .cms-content h1,
        .cms-content h2,
        .cms-content h3,
        .cms-content h4 {
            margin: 1.2em 0 .5em;
            font-weight: 600;
        }

        .cms-content p {
            margin: .75em 0;
        }

        .cms-content blockquote {
            margin: 1em 0;
            padding-inline-start: 1em;
            border-inline-start: 3px solid currentColor;
            opacity: .85;
        }

        .cms-content table {
            border-collapse: collapse;
        }

        .cms-content td,
        .cms-content th {
            border: 1px solid currentColor;
            padding: .5em .75em;
        }
    </style>
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $page->meta_title }}
    </x-slot>

    <!-- Page Content -->
    <div class="container mt-8 px-[60px] max-lg:px-8 cms-content">
        {!! $page->html_content !!}
    </div>
</x-shop::layouts>